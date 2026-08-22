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
        $db = Database::getInstance();
        $updates = [];
        switch ($action) {
            case 'resolve':
                $updates = ['status' => 'solved', 'resolved_at' => date('Y-m-d H:i:s')];
                break;
            case 'escalate':
                $updates = ['status' => 'escalated', 'escalated_at' => date('Y-m-d H:i:s')];
                break;
            case 'start':
                $updates = ['status' => 'in_progress'];
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
            $db->execute("UPDATE troubleshooting_sessions SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
        }
        json_response(['success' => true, 'action' => $action, 'ticket_id' => $ticketId]);
    } catch (Exception $e) {
        json_response(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
} else {
    // Demo mode - just return success
    json_response(['success' => true, 'action' => $action, 'ticket_id' => $ticketId, 'demo' => true]);
}
