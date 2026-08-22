<?php
/**
 * API: AI Feedback
 * POST /api/ai/feedback.php - rate AI response
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = Auth::userId();
$messageId = (int)($input['message_id'] ?? 0);
$rating = $input['rating'] ?? null;
$solved = $input['solved'] ?? null;

if (!$messageId || !$rating) {
    json_response(['error' => 'message_id and rating required'], 400);
}

if (defined('DEMO_MODE') && DEMO_MODE) {
    json_response(['success' => true]);
}

if (class_exists('Database')) {
    Database::insert('ai_feedback', [
        'message_id' => $messageId,
        'user_id' => $userId,
        'rating' => $rating,
        'solved' => $solved,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    Database::insert('audit_logs', [
        'user_id' => $userId,
        'action' => 'AI_FEEDBACK',
        'resource_type' => 'ai_message',
        'resource_id' => $messageId,
        'details' => json_encode(['rating' => $rating, 'solved' => $solved]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

json_response(['success' => true]);
