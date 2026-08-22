<?php
/**
 * API: Send Chat Message
 * POST /api/chat/send.php
 * Body: { "conversation_id": 1, "message": "Hello" }
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$convId = (int)($input['conversation_id'] ?? 0);
$message = trim($input['message'] ?? '');

if (!$convId || empty($message)) {
    json_response(['error' => 'conversation_id and message required'], 400);
    exit;
}

try {
    // Verify user is participant
    $participant = Database::fetch(
        "SELECT id FROM chat_participants WHERE conversation_id = ? AND user_id = ?",
        [$convId, Auth::userId()]
    );
    if (!$participant) { json_response(['error' => 'Not a member of this conversation'], 403); exit; }

    $msgId = Database::insert('chat_messages', [
        'conversation_id' => $convId,
        'sender_id' => Auth::userId(),
        'message' => strip_tags($message),
    ]);

    json_response(['success' => true, 'message_id' => $msgId]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to send message'], 500);
}
