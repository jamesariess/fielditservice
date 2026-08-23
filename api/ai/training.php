<?php
/**
 * API: AI Training Management
 * GET ?action=list — List training files
 * GET ?action=conversations — Get conversation logs
 * POST — Save personality, add/delete training files
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/config/ai_db.php';
require_once APP_ROOT . '/includes/AIDatabase.php';
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();

if (!Auth::isLoggedIn()) { json_response(['error' => 'Unauthorized'], 401); exit; }
// Check admin permission
$userId = $_SESSION['user_id'] ?? 0;
$user = AIDatabase::fetch("SELECT role_id FROM users WHERE id = ?", [$userId]);
$role = AIDatabase::fetch("SELECT name FROM roles WHERE id = ?", [$user['role_id'] ?? 0]);
if (($role['name'] ?? '') !== 'admin' && ($role['name'] ?? '') !== 'super_admin') {
    // Allow any logged-in user for now (training is org-wide)
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// GET requests
if ($method === 'GET') {
    if ($action === 'list') {
        $files = AIDatabase::fetchAll("SELECT * FROM ai_training_files ORDER BY created_at DESC");
        json_response($files);
        exit;
    }
    if ($action === 'conversations') {
        $logs = AIDatabase::fetchAll("SELECT * FROM ai_conversation_logs ORDER BY created_at DESC LIMIT 50");
        $sessions = AIDatabase::fetch("SELECT COUNT(DISTINCT session_id) as cnt FROM ai_conversation_logs");
        $msgCount = AIDatabase::fetch("SELECT COUNT(*) as cnt FROM ai_conversation_logs");
        json_response([
            'logs' => $logs,
            'sessions' => $sessions['cnt'] ?? 0,
            'messages' => $msgCount['cnt'] ?? 0,
        ]);
        exit;
    }
    json_response(['error' => 'Unknown action'], 400);
    exit;
}

// POST requests
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'save_personality') {
    $data = $input['data'] ?? [];
    $existing = AIDatabase::fetch("SELECT id FROM ai_personality WHERE is_active = 1 LIMIT 1");
    if ($existing) {
        AIDatabase::update('ai_personality', $data, ['id' => $existing['id']]);
    } else {
        $data['is_active'] = 1;
        AIDatabase::insert('ai_personality', $data);
    }
    json_response(['message' => 'Personality saved!']);
    exit;
}

if ($action === 'add_file') {
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');
    if (!$title || !$content) { json_response(['error' => 'Title and content required'], 400); exit; }
    $id = AIDatabase::insert('ai_training_files', [
        'title' => $title,
        'content' => $content,
        'category' => $input['category'] ?? 'general',
        'tags' => $input['tags'] ?? '',
        'file_type' => 'text',
        'uploaded_by' => $_SESSION['user_id'] ?? 0,
    ]);
    json_response(['id' => $id, 'message' => 'Training content added!']);
    exit;
}

if ($action === 'delete_file') {
    $id = (int)($input['id'] ?? 0);
    if (!$id) { json_response(['error' => 'ID required'], 400); exit; }
    AIDatabase::delete('ai_training_files', ['id' => $id]);
    json_response(['message' => 'Deleted']);
    exit;
}

json_response(['error' => 'Unknown action'], 400);
