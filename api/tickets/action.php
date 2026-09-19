<?php
/**
 * Ticket Action API - resolve, escalate, update
 */
require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
}
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? '';
$ticketId = intval($input['id'] ?? $_POST['id'] ?? 0);

if (!$action || !$ticketId) {
    json_response(['error' => 'Missing action or id'], 400);
}

$demo = !defined('DEMO_MODE') || DEMO_MODE;

if (!$demo) {
    try {
        $now = date('Y-m-d H:i:s');
        $ticket = Database::fetch(
            "SELECT id FROM troubleshooting_sessions WHERE id = ? AND user_id = ?",
            [$ticketId, Auth::userId()]
        );
        if (!$ticket) {
            json_response(['error' => 'Ticket not found or not owned by you'], 404);
        }

        $updates = [];
        switch ($action) {
            case 'resolve':
                $updates = ['status' => 'solved', 'resolved_at' => $now];
                break;
            case 'escalate':
                $updates = ['status' => 'escalated', 'escalated_at' => $now];
                break;
            case 'start':
                $updates = ['status' => 'in_progress'];
                break;
            case 'timein':
                // Time In: stamp started_at with today's date + the current time.
                $updates = ['started_at' => $now, 'status' => 'in_progress'];
                break;
            default:
                json_response(['error' => 'Invalid action'], 400);
        }
        if (!empty($updates)) {
            $sets = [];
            $vals = [];
            foreach ($updates as $k => $v) {
                $sets[] = "$k = ?";
                $vals[] = $v;
            }
            $vals[] = $ticketId;
            $vals[] = Auth::userId();
            Database::query(
                "UPDATE troubleshooting_sessions SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?",
                $vals
            );
        }
        $session = Database::fetch(
            "SELECT * FROM troubleshooting_sessions WHERE id = ? AND user_id = ?",
            [$ticketId, Auth::userId()]
        );
        json_response([
            'success' => true,
            'action' => $action,
            'ticket_id' => $ticketId,
            'time_in' => date('g:i A', strtotime($now)),
            'started_at' => $session['started_at'] ?? $now,
            'session' => $session ?: [],
        ]);
    } catch (Throwable $e) {
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
} else {
    // Demo mode - just return success
    json_response(['success' => true, 'action' => $action, 'ticket_id' => $ticketId, 'demo' => true]);
}
