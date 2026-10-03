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
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
    require_once APP_ROOT . '/includes/TicketSuggestions.php';
    require_once APP_ROOT . '/includes/TicketFieldMemory.php';
}
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Activity.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

// GET by ticket_id: return a single session row (used by report refresh)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tid = (int)($_GET['ticket_id'] ?? 0);
    if (!$tid) { json_response(['error' => 'ticket_id required'], 400); exit; }
    $row = null;
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        $row = Database::fetch("SELECT * FROM troubleshooting_sessions WHERE id = ?", [$tid]);
        if (!$row || !Auth::canViewTicketOwner((int)$row['user_id'])) {
            json_response(['error' => 'Not found'], 404); exit;
        }
        if (!empty($row['ticket_number']) && preg_match('/^(?:SD|TK-)?(\d+)$/i', (string)$row['ticket_number'], $numberMatch)) {
            $row['ticket_number'] = 'SD' . $numberMatch[1];
        }
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
$companyIsNew = !empty($input['company_is_new']);
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
$taskIsNew   = !empty($input['task_is_new']);

// Minimal validation
if (!$title) { json_response(['error' => 'Title is required'], 400); exit; }
if (!preg_match('/^(?:SD)?(\d+)$/i', $ticketNumInput, $ticketNumberMatch)) {
    json_response(['error' => 'Ticket number must contain numbers only. The SD prefix is added automatically.'], 400); exit;
}
$ticketDigits = $ticketNumberMatch[1];
$ticketNumInput = 'SD' . $ticketDigits;

try {
    if (!defined('DEMO_MODE') || !DEMO_MODE) { TicketFieldMemory::ensure(); }
    $companySuggestion = ['status' => 'ignored'];
    $taskSuggestion = ['status' => 'ignored'];
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        if ($companyName !== '') {
            $companySuggestion = TicketSuggestions::submit('company', $companyName, Auth::userId());
            if ($companySuggestion['status'] === 'already_approved' && !empty($companySuggestion['value'])) {
                $companyName = $companySuggestion['value'];
            }
        }
        if ($task !== '') {
            $taskSuggestion = TicketSuggestions::submit('task', $task, Auth::userId());
            if ($taskSuggestion['status'] === 'already_approved' && !empty($taskSuggestion['value'])) {
                $task = $taskSuggestion['value'];
            }
        }
    }

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

    // Users type digits only; SD is the canonical stored prefix.
    $ticketNum = $ticketNumInput;
    if (!defined('DEMO_MODE') || !DEMO_MODE) {
        $duplicate = Database::fetch(
            "SELECT id FROM troubleshooting_sessions
             WHERE UPPER(REPLACE(ticket_number, 'TK-', 'SD')) = ? OR ticket_number = ? LIMIT 1",
            [strtoupper($ticketNum), $ticketDigits]
        );
        if ($duplicate) {
            json_response(['error' => 'Ticket number ' . $ticketNum . ' already exists.'], 409); exit;
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
        'latitude'         => $latitude !== '' ? $latitude : null,
        'longitude'        => $longitude !== '' ? $longitude : null,
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
            if ($companyIsNew && $companySuggestion['status'] !== 'already_approved') {
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
                    'suggestion_notice' => 'New company/task suggestions were submitted for manager approval.',
                ]);
            }
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
                        'name'   => mb_substr($location !== '' ? $location : $address, 0, 100),
                        'address' => $address,
                        'latitude' => $latitude !== '' ? $latitude : null,
                        'longitude' => $longitude !== '' ? $longitude : null,
                    ]);
                } elseif ($latitude !== '' && $longitude !== '') {
                    Database::query(
                        "UPDATE locations SET name = CASE WHEN ? <> '' THEN ? ELSE name END, latitude = ?, longitude = ? WHERE id = ?",
                        [$location, mb_substr($location, 0, 100), $latitude, $longitude, $loc['id']]
                    );
                }
            }
        }
    } catch (Exception $e) {
        // Non-fatal: ticket creation must not fail because of the address book.
    }

    Activity::log('CREATE', 'ticket', (int)$sessionId, ['ticket_number' => $ticketNum, 'company' => $companyName, 'problem' => $task]);
    Activity::notifyUsers(Activity::managers(), 'new_ticket', 'New ticket: ' . $ticketNum, $companyName . ' - ' . $task, '/admin/ticket-approvals');
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
        'suggestion_notice' => ($taskIsNew && $taskSuggestion['status'] !== 'already_approved' ? 'New task suggestion submitted for manager approval.' : ''),
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to record time-in: ' . $e->getMessage()], 500);
}
