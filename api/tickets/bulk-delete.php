<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Activity.php';

Auth::start();
Auth::requireLogin();
$role = strtolower((string)($_SESSION['role_name'] ?? ''));
if (!Auth::hasPermission('system.settings') && !in_array($role, ['admin', 'super admin', 'super_admin', 'manager'], true)) {
    json_response(['error' => 'Permission denied'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$ids = array_values(array_unique(array_filter(array_map('intval', (array)($input['ticket_ids'] ?? [])))));
if (!$ids || count($ids) > 200) {
    json_response(['error' => 'Select between 1 and 200 tickets to delete.'], 400);
}

$marks = implode(',', array_fill(0, count($ids), '?'));
try {
    $db = Database::getInstance();
    $db->beginTransaction();

    $sessionTicketIds = Database::fetchAll("SELECT id FROM tickets WHERE session_id IN ($marks)", $ids);
    $linkedTicketIds = array_map('intval', array_column($sessionTicketIds, 'id'));
    if ($linkedTicketIds) {
        $ticketMarks = implode(',', array_fill(0, count($linkedTicketIds), '?'));
        Database::execute("DELETE FROM escalations WHERE ticket_id IN ($ticketMarks)", $linkedTicketIds);
        Database::execute("DELETE FROM tickets WHERE id IN ($ticketMarks)", $linkedTicketIds);
    }
    Database::execute("DELETE FROM ticket_step_suggestions WHERE session_id IN ($marks)", $ids);
    Database::execute("DELETE FROM session_steps WHERE session_id IN ($marks)", $ids);
    $deleted = Database::execute("DELETE FROM troubleshooting_sessions WHERE id IN ($marks)", $ids);
    $db->commit();

    Activity::log('DELETE_BATCH', 'ticket', null, ['ticket_ids' => $ids, 'deleted' => $deleted]);
    json_response(['success' => true, 'deleted' => $deleted]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('Bulk ticket deletion failed: ' . $e->getMessage());
    json_response(['error' => 'Unable to delete the selected tickets. No tickets were removed.'], 500);
}
