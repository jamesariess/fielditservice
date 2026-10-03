<?php
/**
 * API: Notifications
 * GET /api/notifications - list user notifications
 * PUT /api/notifications - mark as read
 * DELETE /api/notifications - clear all
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

$userId = Auth::userId();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['notifications' => [], 'unread_count' => 0]);
    }

    $notifications = Database::fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
        [$userId]
    );
    $unread = Database::count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
    json_response(['notifications' => $notifications, 'unread_count' => $unread]);
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notifId = $input['id'] ?? null;

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['success' => true]);
    }

    if ($notifId) {
        Database::update('notifications', ['is_read' => 1], 'id = ? AND user_id = ?', [$notifId, $userId]);
    } else {
        Database::update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]);
    }
    json_response(['success' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
