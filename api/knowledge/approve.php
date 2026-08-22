<?php
/**
 * API: Knowledge Article Approval
 * POST /api/knowledge/approve.php
 * Body: { "article_id": 1, "action": "approve" | "reject" }
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();
Auth::requirePermission('knowledge.approve');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$articleId = (int)($input['article_id'] ?? 0);
$action = $input['action'] ?? '';

if (!$articleId || !in_array($action, ['approve', 'reject'])) {
    json_response(['error' => 'article_id and action (approve/reject) required'], 400);
    exit;
}

try {
    $newStatus = $action === 'approve' ? 'published' : 'draft';
    $article = Database::fetch("SELECT id, title FROM knowledge_articles WHERE id = ?", [$articleId]);
    if (!$article) { json_response(['error' => 'Article not found'], 404); exit; }

    Database::update('knowledge_articles', [
        'status' => $newStatus,
        'reviewer_id' => Auth::userId(),
    ], 'id = ?', [$articleId]);

    Database::insert('audit_logs', [
        'user_id' => Auth::userId(),
        'action' => strtoupper($action),
        'resource_type' => 'knowledge',
        'resource_id' => $articleId,
        'details' => json_encode(['title' => $article['title'], 'new_status' => $newStatus]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    json_response(['success' => true, 'status' => $newStatus]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to update article'], 500);
}
