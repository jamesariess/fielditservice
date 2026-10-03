<?php

if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
    require_once APP_ROOT . '/includes/TicketSuggestions.php';
}
require_once APP_ROOT . '/includes/Auth.php';

Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

$roleName = strtolower((string)($_SESSION['role_name'] ?? ''));
$canManage = Auth::hasPermission('system.settings') || in_array($roleName, ['admin', 'super admin', 'super_admin', 'manager'], true);
if (!$canManage) {
    json_response(['error' => 'Permission denied'], 403);
}

if (defined('DEMO_MODE') && DEMO_MODE) {
    json_response(['success' => true, 'demo' => true, 'pending' => []]);
}

TicketSuggestions::ensure();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_response(['success' => true, 'pending' => TicketSuggestions::pending()]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

$action = trim((string)($input['action'] ?? ''));
$ids = $input['ids'] ?? [];
if (!is_array($ids)) {
    $ids = [$ids];
}
$ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

if (!$ids) {
    json_response(['error' => 'No suggestions selected'], 400);
}

try {
    if ($action === 'approve') {
        $count = TicketSuggestions::approve($ids, Auth::userId());
        json_response(['success' => true, 'approved' => $count, 'pending' => TicketSuggestions::pending()]);
    }
    if ($action === 'delete') {
        $count = TicketSuggestions::delete($ids);
        json_response(['success' => true, 'deleted' => $count, 'pending' => TicketSuggestions::pending()]);
    }
    json_response(['error' => 'Invalid action'], 400);
} catch (Throwable $e) {
    json_response(['error' => 'Suggestion update failed: ' . $e->getMessage()], 500);
}
