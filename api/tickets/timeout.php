<?php
/** API: Ticket Time-Out
 * Finalises a work session: stamps ended_at from the submitted time (or now),
 * computes time_spent_minutes from started_at -> ended_at, records the
 * resolution, parts replaced, tools used, notes, and the destination address
 * (for the routing map). Status becomes 'solved' by default unless the tech
 * marks it partial/escalated.
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
    require_once APP_ROOT . '/includes/TicketFieldMemory.php';
}
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

function ensureTicketReportColumns(): void {
    static $done = false;
    if ($done || (defined('DEMO_MODE') && DEMO_MODE)) { return; }
    $existing = [];
    foreach (Database::fetchAll("SHOW COLUMNS FROM troubleshooting_sessions") as $col) {
        $existing[$col['Field']] = true;
    }
    $adds = [];
    if (!isset($existing['result_of_checking'])) { $adds[] = "ADD COLUMN result_of_checking TEXT NULL AFTER resolution"; }
    if (!isset($existing['recommendation'])) { $adds[] = "ADD COLUMN recommendation TEXT NULL AFTER result_of_checking"; }
    if (!isset($existing['confirmed_by'])) { $adds[] = "ADD COLUMN confirmed_by VARCHAR(150) NULL AFTER recommendation"; }
    if ($adds) {
        Database::query("ALTER TABLE troubleshooting_sessions " . implode(', ', $adds));
    }
    $done = true;
}

ensureTicketReportColumns();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

$ticketId = (int)($input['ticket_id'] ?? 0);
if (!$ticketId) { json_response(['error' => 'ticket_id required'], 400); exit; }

// Verify ownership
$session = null;
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    $session = Database::fetch(
        "SELECT * FROM troubleshooting_sessions WHERE id = ? AND user_id = ?",
        [$ticketId, Auth::userId()]
    );
    if (!$session) { json_response(['error' => 'Ticket not found or not owned by you'], 404); exit; }
    // Workflow guard: a technician cannot Time Out before recording Time In.
    if (empty($session['started_at'])) { json_response(['error' => 'Record Time In before Time Out'], 400); exit; }
}

$endedAtRaw     = trim($input['ended_at'] ?? '');
$resolution     = trim($input['resolution'] ?? '');
$resolutionType = trim($input['resolution_type'] ?? '');
$partsReplaced  = trim($input['parts_replaced'] ?? '');
$toolsUsed      = trim($input['tools_used'] ?? '');
$notes          = trim($input['notes'] ?? '');
$resultOfChecking = trim($input['result_of_checking'] ?? '');
$recommendation   = trim($input['recommendation'] ?? '');
$confirmedBy      = trim($input['confirmed_by'] ?? '');

// steps_performed column requires valid JSON (CHECK constraint). Store notes as JSON array.
$notesJson = '[]';
if ($notes) {
    $parts = preg_split('/\r?\n|\r/', $notes);
    $parts = array_filter(array_map('trim', $parts), function($p) { return $p !== ''; });
    if (count($parts)) { $notesJson = json_encode($parts); }
}

$actionTaken    = trim($input['action_taken'] ?? $input['action_taken_manual'] ?? '');
$testResults    = '';
$status         = trim($input['status'] ?? 'solved');     // solved | partial | escalated
$latitude       = trim($input['latitude'] ?? '');
$longitude      = trim($input['longitude'] ?? '');
$address        = trim($input['address'] ?? '');

// Checklist steps sent from the Time Out button (the checklist IS the action log).
// These are stored in steps_performed as JSON and marked as pending approval.
$checklistSteps = $input['steps_performed'] ?? [];
if (!is_array($checklistSteps)) { $checklistSteps = []; }
$checklistJson = json_encode(array_values(array_filter(array_map('trim', $checklistSteps), function($s) { return $s !== ''; })));
$stepsApproved = 0;  // checklist saved but not yet approved by a supervisor
$stepsApprovedBy = trim($input['steps_approved_by'] ?? '') ?: null;

// ----- Company origin config (routing) -----
// The tech can override this from the per-user settings page; here we fall back to a
// sensible HQ address so the map always has a starting pin on the first job.
function companyOriginAddress() {
    try {
        $row = Database::fetch("SELECT value FROM settings WHERE `key` = 'company_address'");
        if ($row && trim($row['value'])) return trim($row['value']);
    } catch (Exception $e) {}
    return '1 Aviation Ground Handling Services Corporation, Pasay City, Metro Manila, Philippines';
}

function geocodeAddress($addr) {
    if (!$addr) { return ['lat' => null, 'lng' => null, 'addr' => null]; }
    require_once APP_ROOT . '/includes/Geocoder.php';
    $result = Geocoder::forward((string)$addr);
    return $result
        ? ['lat' => $result['lat'], 'lng' => $result['lng'], 'addr' => $result['address']]
        : ['lat' => null, 'lng' => null, 'addr' => $addr];
}

// ----- Resolve where this job ended (for the route origin) -----
$routeOrigin = ['lat' => null, 'lng' => null, 'addr' => null];
if ($latitude !== '' && $longitude !== '') {
    $routeOrigin = ['lat' => (double)$latitude, 'lng' => (double)$longitude, 'addr' => $address];
} elseif ($address) {
    $geo = geocodeAddress($address);
    $routeOrigin = $geo;
} else {
    // Last known destination the tech left (from a previous time-out) becomes the origin.
    try {
        $last = Database::fetch(
            "SELECT last_route_end_addr, last_route_end_lat, last_route_end_lng FROM troubleshooting_sessions WHERE user_id = ? AND last_route_end_addr IS NOT NULL LIMIT 1",
            [Auth::userId()]
        );
        if ($last && $last['last_route_end_addr']) {
            $routeOrigin['addr'] = $last['last_route_end_addr'];
            $routeOrigin['lat'] = $last['last_route_end_lat'];
            $routeOrigin['lng'] = $last['last_route_end_lng'];
        }
    } catch (Exception $e) {}
}

// ----- Find the next nearest open ticket destination -----
$routeNext = ['destination_addr' => null, 'destination_lat' => null, 'destination_lng' => null, 'next_ticket_id' => null, 'next_ticket_number' => null];
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    try {
        $next = Database::fetch(
            "SELECT id, ticket_number, address, location, customer_name, latitude, longitude
             FROM troubleshooting_sessions
             WHERE user_id = ?
               AND status IN ('in_progress','new')
               AND (latitude IS NOT NULL OR longitude IS NOT NULL OR address IS NOT NULL OR location IS NOT NULL)
               AND id <> ?
             ORDER BY
               CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL
                    THEN POW(latitude - " . ($routeOrigin['lat'] !== null ? (float)$routeOrigin['lat'] : 0) . ", 2)
                       + POW(longitude - " . ($routeOrigin['lng'] !== null ? (float)$routeOrigin['lng'] : 0) . ", 2)
                    ELSE 180
               END ASC,
               started_at ASC
             LIMIT 1",
            [Auth::userId(), $ticketId]
        );
        if ($next) {
            $routeNext = [
                'destination_addr' => trim($next['address'] ?: $next['location'] ?: $next['customer_name'] ?: ''),
                'destination_lat'  => $next['latitude'],
                'destination_lng'  => $next['longitude'],
                'next_ticket_id'   => $next['id'],
                'next_ticket_number' => $next['ticket_number'],
            ];
        }
    } catch (Exception $e) {}
}

// ----- ended_at + time spent -----
$endedAt = $endedAtRaw ?: date('Y-m-d H:i:s');
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $endedAt)) {
    $endedAt = date('Y-m-d H:i:s');
}

$timeSpentMinutes = null;
if (!defined('DEMO_MODE') || !DEMO_MODE && $session['started_at']) {
    try {
        $start = new DateTime($session['started_at']);
        $end   = new DateTime($endedAt);
        $diff  = $start->diff($end);
        $timeSpentMinutes = ($diff->h * 60) + $diff->i + ($diff->days * 1440);
        if ($timeSpentMinutes < 0) { $timeSpentMinutes = 0; }
    } catch (Exception $e) { $timeSpentMinutes = null; }
}

// Build resolution string from parts if technician left resolution blank
if (!$resolution) {
    $parts = [];
    if ($actionTaken) $parts[] = $actionTaken;
    if ($resolutionType) $parts[] = $resolutionType;
    if ($testResults) $parts[] = $testResults;
    if ($parts) { $resolution = implode(' ', $parts); }
}

try {
    $updates = [
        'ended_at'         => $endedAt,
        'resolution'       => $resolution,
        'result_of_checking' => $resultOfChecking,
        'recommendation'   => $recommendation,
        'confirmed_by'     => $confirmedBy,
        'resolution_type'  => $resolutionType,
        'parts_replaced'   => $partsReplaced,
        'tools_used'       => $toolsUsed,
        'steps_performed'  => strlen($checklistJson) > 2 ? $checklistJson : $notesJson,
        'steps_approved'   => $stepsApproved,
        'steps_approved_by'=> $stepsApprovedBy,
        'time_spent_minutes' => $timeSpentMinutes,
        'status'           => $status,
        // Persist where this job ended (next time-in starts from here).
        'last_route_end_addr' => $routeOrigin['addr'],
        'last_route_end_lat'  => $routeOrigin['lat'],
        'last_route_end_lng'  => $routeOrigin['lng'],
    ];

    if ($status === 'solved' || $status === 'partial') {
        $updates['resolved_at'] = $endedAt;
    }

    $sets = []; $vals = [];
    foreach ($updates as $k => $v) { $sets[] = "$k = ?"; $vals[] = $v; }
    $vals[] = $ticketId;

    Database::query(
        "UPDATE troubleshooting_sessions SET " . implode(', ', $sets) . " WHERE id = ?",
        $vals
    );

    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        $issueId = (int)($session['issue_id'] ?? 0);
        TicketFieldMemory::rememberIssue($issueId, 'result', $resultOfChecking, Auth::userId());
        TicketFieldMemory::rememberIssue($issueId, 'recommendation', $recommendation, Auth::userId());
        TicketFieldMemory::rememberConfirmedBy((string)($session['company_name'] ?? ''), $confirmedBy, Auth::userId());
    }

    // After time-out, always return the full session so the report card can rebuild
    $session = Database::fetch("SELECT * FROM troubleshooting_sessions WHERE id = ?", [$ticketId]);

    json_response([
        'success' => true,
        'ticket_id' => $ticketId,
        'ended_at' => $endedAt,
        'time_spent_minutes' => $timeSpentMinutes,
        'session' => $session ?: [],
        'route_next' => $routeNext,
        'company_address' => companyOriginAddress(),
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to record time-out: ' . $e->getMessage()], 500);
}
