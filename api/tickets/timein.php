<?php
/** API: Ticket Time-In
 * Captures the start of a work session: creates (or resumes) a ticket,
 * stamps today's date and the current time as time_in, and optionally
 * carries equipment info pulled from device_models so the report is complete.
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

// GET by ticket_id: return a single session row (used by report refresh)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tid = (int)($_GET['ticket_id'] ?? 0);
    if (!$tid) { json_response(['error' => 'ticket_id required'], 400); exit; }
    $row = null;
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        $row = Database::fetch("SELECT * FROM troubleshooting_sessions WHERE id = ? AND user_id = ?", [$tid, Auth::userId()]);
        if (!$row) { json_response(['error' => 'Not found'], 404); exit; }
    }
    json_response(['session' => $row ?: []]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

// Fields the technician enters / selects
$title       = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$task        = trim($input['task'] ?? '');
$problem     = trim($input['problem'] ?? '');
$category    = trim($input['category'] ?? '');
$priority    = trim($input['priority'] ?? 'medium');
$department  = trim($input['department'] ?? '');
$location    = trim($input['location'] ?? '');
$companyName = trim($input['company_name'] ?? '');
$ticketNumInput = trim($input['ticket_number'] ?? ''); // manually typed on the form
$customerName= trim($input['customer_name'] ?? Auth::userName() ?? ' ');
$deviceLabel = trim($input['device'] ?? '');          // free-text override
$deviceType  = trim($input['device_type'] ?? '');
$equipmentId = (int)($input['equipment_id'] ?? 0);
$manufacturerId = (int)($input['manufacturer_id'] ?? 0);
$modelId     = (int)($input['model_id'] ?? 0);
$serial      = trim($input['serial_number'] ?? '');
$notesText   = trim($input['notes'] ?? '');
$issueId     = (int)($input['issue_id'] ?? 0);
$latitude    = trim($input['latitude'] ?? '');
$longitude   = trim($input['longitude'] ?? '');
$address     = trim($input['address'] ?? '');
$timeInInput = trim($input['time_in'] ?? '');

// Minimal validation
if (!$title) { json_response(['error' => 'Title is required'], 400); exit; }

try {
    // ---- carry over equipment details from device_models when selected ----
    $manufacturer = '';
    $model = '';
    $modelSpecs = null;
    $serviceManualUrl = '';
    $knownIssues = '';
    $requiredTools = '';

    if ($modelId > 0) {
        if (!defined('DEMO_MODE') || !DEMO_MODE) {
            $dm = Database::fetch(
                "SELECT dm.*, m.name AS mfr_name
                 FROM device_models dm
                 LEFT JOIN manufacturers m ON dm.manufacturer_id = m.id
                 WHERE dm.id = ?",
                [$modelId]
            );
            if ($dm) {
                $manufacturer = $dm['mfr_name'] ?: $dm['manufacturer_id'] ?: '';
                $model = $dm['name'];
                $modelSpecs   = $dm['specifications'] ?: null;
                $serviceManualUrl = $dm['service_manual_url'] ?: '';
                $knownIssues  = $dm['known_issues'] ?: '';
                $requiredTools = $dm['required_tools'] ?: '';
                // serial number: prefer the one typed by the tech over nothing
                if (!$serial) { $serial = trim($dm['generation'] ?: ''); }
            }
        }
    }

    // ---- carry over details from the real `equipment` table when a device was picked ----
    if ($equipmentId > 0) {
        if (!defined('DEMO_MODE') || !DEMO_MODE) {
            $eq = Database::fetch(
                "SELECT * FROM equipment WHERE id = ? AND deleted_at IS NULL",
                [$equipmentId]
            );
            if ($eq) {
                $manufacturer = trim($eq['manufacturer'] ?? '') ?: $manufacturer;
                $model        = trim($eq['model_name'] ?? '') ?: $model;
                $deviceType   = trim($eq['device_type'] ?? '') ?: $deviceType;
                if (!$serial)   { $serial   = trim($eq['serial_number'] ?? ''); }
                if (!$location) { $location  = trim($eq['location'] ?? ''); }
                $modelSpecs    = $eq['specs_json'] ?: null;
                $knownIssues   = $eq['known_issues'] ?: '';
                $requiredTools = $eq['tools_needed'] ?: '';
            } else {
                // Equipment row vanished — keep the id only if it still exists
                $equipmentId = 0;
            }
        }
    }

    // If the technician typed a device label and we have no model selected,
    // treat the label as the model for reporting purposes.
    if (!$model && $deviceLabel) { $model = $deviceLabel; }
    if (!$manufacturer && $deviceLabel) {
        // Known multi-word brands (keep 2 words). Everything else: first word only.
        $multiWordBrands = ['Cisco Meraki','Ubiquiti UniFi','Arista Networks','Dell Technologies','HP Inc','Hewlett Packard','Microsoft Surface','Samsung Electronics','LG Electronics'];
        $found = false;
        foreach ($multiWordBrands as $brand) {
            if (stripos($deviceLabel, $brand) === 0) { $manufacturer = $brand; $found = true; break; }
        }
        if (!$found && preg_match('/^([A-Za-z][A-Za-z]+)/', $deviceLabel, $m)) {
            $manufacturer = trim($m[1]);
        }
    }

    // ---- ticket number: use the manually typed value; fall back to auto TK-#### ----
    // Keeps uniqueness: if the typed number already exists, append -2, -3, ...
    $ticketNum = $ticketNumInput !== '' ? $ticketNumInput : '';
    if ($ticketNum === '' && (!defined('DEMO_MODE') || !DEMO_MODE)) {
        $nextNum = 1006;
        $maxRow = Database::fetch("SELECT MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED)) AS mx FROM troubleshooting_sessions WHERE ticket_number LIKE 'TK-%'");
        if ($maxRow && ($maxRow['mx'] ?? 0) > 0) {
            $nextNum = (int)$maxRow['mx'] + 1;
        }
        $ticketNum = 'TK-' . $nextNum;
    } elseif ($ticketNum === '') {
        $ticketNum = 'TK-1006';
    }
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        $baseNum = $ticketNum;
        $suffix = 2;
        while (Database::fetch("SELECT id FROM troubleshooting_sessions WHERE ticket_number = ? LIMIT 1", [$ticketNum])) {
            $ticketNum = $baseNum . '-' . $suffix;
            $suffix++;
        }
    }
    $now       = date('Y-m-d H:i:s');

    $resolvedAt = null;
    $endedAt    = null;          // no time-out yet
    $resolution = '';
    $resolutionType = '';
    $partsReplaced = '';
    $toolsUsed = '';
    $timeSpentMinutes = null;
    $stepsPerformed = '';
    // Creating a ticket is NOT a Time In. started_at stays NULL ("00" on screen)
    // until the technician clicks "Start Time In" in the ticket drawer
    // (which calls action.php with action=timein and stamps the server time).
    $status = 'new';

    $problemDesc = $problem !== '' ? $problem : $title;
    if ($task !== '' && $task !== $problemDesc) { $problemDesc = $task . "\n" . $problemDesc; }
    if ($description !== '' && stripos($problemDesc, $description) === false) {
        $problemDesc .= "\n" . $description;
    }

    // Time In stays NULL until the technician clicks "Start Time In".
    // The ticket creation timestamp lives in created_at (DB DEFAULT), which is a
    // different thing from the actual work start.
    $startedAt = null;

    $sessionId = Database::insert('troubleshooting_sessions', [
        'ticket_number'    => $ticketNum,
        'user_id'          => Auth::userId(),
        'issue_id'         => $issueId ?: null,
        'equipment_id'     => $equipmentId ?: null,
        'customer_name'    => $customerName,
        'company_name'     => $companyName,
        'department'       => $department,
        'location'         => $location,
        'device_type'      => $deviceType,
        'manufacturer'     => $manufacturer,
        'model'            => $model,
        'serial_number'    => $serial,
        'notes'            => $notesText,
        'problem_description' => $problemDesc,
        'task'             => $task,
        'priority'         => $priority,
        'status'           => $status,
        'started_at'       => $startedAt,
        'ended_at'         => $endedAt,
        'resolved_at'      => $resolvedAt,
        'resolution'       => $resolution,
        'resolution_type'  => $resolutionType,
        'parts_replaced'   => $partsReplaced,
        'tools_used'       => $toolsUsed,
        'steps_performed'  => '[]',  // JSON column; updated on time-out with actual steps
        'time_spent_minutes' => $timeSpentMinutes,
        'latitude'         => $latitude,
        'longitude'        => $longitude,
        'address'          => $address,
    ]);

    // The note lives on the session row (`ticket_notes` is FK'd to the separate
    // `tickets` table, so it cannot key off a troubleshooting session id).

    // ---- remember the company + address for future ticket forms ----
    // The company name is typed by hand; keep it in `organizations` so the next
    // ticket can suggest it, and save the address in `locations` linked to that
    // organization (a company may have several locations).
    try {
        if ($companyName !== '' && (!defined('DEMO_MODE') || !DEMO_MODE)) {
            $org = Database::fetch("SELECT id FROM organizations WHERE LOWER(name) = LOWER(?) LIMIT 1", [$companyName]);
            if ($org) {
                $orgId = (int)$org['id'];
            } else {
                $orgId = (int)Database::insert('organizations', ['name' => $companyName]);
            }
            if ($address !== '' && $orgId > 0) {
                $loc = Database::fetch(
                    "SELECT id FROM locations WHERE organization_id = ? AND LOWER(address) = LOWER(?) LIMIT 1",
                    [$orgId, $address]
                );
                if (!$loc) {
                    Database::insert('locations', [
                        'organization_id' => $orgId,
                        'name'   => mb_substr($address, 0, 100),
                        'address' => $address,
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        // Non-fatal: ticket creation must not fail because of the address book.
    }

    json_response([
        'success'        => true,
        'ticket_id'      => $sessionId,
        'ticket_number'  => $ticketNum,
        'time_in'        => $startedAt,
        'company_name'   => $companyName,
        'task'           => $task,
        'manufacturer'   => $manufacturer,
        'model'          => $model,
        'device_type'    => $deviceType,
        'serial_number'  => $serial,
        'service_manual_url' => $serviceManualUrl,
        'known_issues'   => $knownIssues,
        'required_tools' => $requiredTools,
        'model_specs'    => $modelSpecs,
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to record time-in: ' . $e->getMessage()], 500);
}
