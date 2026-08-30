<?php
/**
 * API: AI Conversation Rating
 * POST /api/ai/rate-conversation.php — rate the overall chat session
 * Called by the UI after 10 minutes of chat inactivity.
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/config/ai_db.php';
require_once APP_ROOT . '/includes/AIDatabase.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$userId   = Auth::userId();
$sessionId = trim($input['session_id'] ?? '');
$rating   = (int)($input['rating'] ?? 0);
$comment  = trim(mb_substr($input['comment'] ?? '', 0, 1000));

if (!$rating || $rating < 1 || $rating > 5) {
    json_response(['error' => 'rating (1-5) required'], 400);
}

if (defined('DEMO_MODE') && DEMO_MODE) {
    json_response(['success' => true, 'demo' => true]);
}

if (class_exists('Database') && AIDatabase::isConnected()) {
    try {
        // Ensure the table exists (first run convenience)
        AIDatabase::execute("CREATE TABLE IF NOT EXISTS ai_conversation_ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64) NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            comment TEXT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_sess (session_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        AIDatabase::insert('ai_conversation_ratings', [
            'session_id' => $sessionId ?: ('session_' . $userId),
            'user_id'    => $userId,
            'rating'     => $rating,
            'comment'    => $comment ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AIDatabase::insert('audit_logs', [
            'user_id'       => $userId,
            'action'        => 'AI_CHAT_RATED',
            'resource_type' => 'ai_conversation',
            'resource_id'   => 0,
            'details'       => json_encode(['session_id' => $sessionId, 'rating' => $rating]),
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    } catch (Exception $e) {
        error_log('rate-conversation: ' . $e->getMessage());
        json_response(['success' => true, 'stored' => false]);
    }
}

json_response(['success' => true, 'stored' => true]);