<?php
/** API: Save the technician's checklist (POST)
 * Stores the performed steps (checked checklist items + custom typed steps)
 * on the ticket as steps_performed (JSON array of strings).
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

$ticketId = (int)($input['ticket_id'] ?? 0);
if (!$ticketId) { json_response(['error' => 'ticket_id required'], 400); exit; }

$rawSteps = $input['steps'] ?? [];
if (!is_array($rawSteps)) { $rawSteps = []; }

// Sanitize: trim, drop empties, keep order, remove duplicates (case-insensitive)
$steps = [];
$seen = [];
foreach ($rawSteps as $s) {
    $s = trim((string)$s);
    if ($s === '') { continue; }
    $key = mb_strtolower($s);
    if (isset($seen[$key])) { continue; }
    $seen[$key] = true;
    $steps[] = $s;
}

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    try {
        $owned = Database::fetch(
            "SELECT id FROM troubleshooting_sessions WHERE id = ? AND user_id = ?",
            [$ticketId, Auth::userId()]
        );
        if (!$owned) { json_response(['error' => 'Ticket not found or not owned by you'], 404); exit; }

        // steps_performed requires valid JSON (CHECK constraint)
        Database::query(
            "UPDATE troubleshooting_sessions SET steps_performed = ? WHERE id = ?",
            [json_encode($steps), $ticketId]
        );
    } catch (Exception $e) {
        json_response(['error' => 'Failed to save checklist: ' . $e->getMessage()], 500);
        exit;
    }
}

json_response(['success' => true, 'ticket_id' => $ticketId, 'steps' => $steps]);
