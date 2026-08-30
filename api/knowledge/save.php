<?php
/**
 * API: Save Knowledge Article (Create or Update)
 * POST /api/knowledge/save.php  → create
 * PUT  /api/knowledge/save.php  → update
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();
Auth::requirePermission('knowledge.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_response(['error' => 'POST or PUT required'], 405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];

$title = trim($input['title'] ?? '');
$category = trim($input['category'] ?? 'General');
$issue = trim($input['issue'] ?? '');
$solution = trim($input['solution'] ?? '');
$symptoms = trim($input['symptoms'] ?? '');
$root_cause = trim($input['root_cause'] ?? '');
$tools_used = trim($input['tools_used'] ?? '');
$commands_used = trim($input['commands_used'] ?? '');
$device_type = trim($input['device_type'] ?? '');
$manufacturer = trim($input['manufacturer'] ?? '');
$status = $input['status'] ?? 'draft';
$id = intval($input['id'] ?? 0);

if (empty($title)) { json_response(['error' => 'Title is required'], 400); exit; }
if (empty($issue)) { json_response(['error' => 'Issue description is required'], 400); exit; }
if (empty($solution)) { json_response(['error' => 'Solution steps are required'], 400); exit; }

// Validate status
if (!in_array($status, ['draft', 'submitted', 'under_review', 'published'])) $status = 'draft';

try {
    $data = [
        'title' => $title,
        'category' => $category,
        'issue' => $issue,
        'solution' => $solution,
        'symptoms' => $symptoms,
        'root_cause' => $root_cause,
        'tools_used' => $tools_used,
        'commands_used' => $commands_used,
        'device_type' => $device_type,
        'manufacturer' => $manufacturer,
        'status' => $status,
    ];

    if ($id > 0) {
        // Update existing article
        $existing = Database::fetch("SELECT id, title FROM knowledge_articles WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$existing) { json_response(['error' => 'Article not found'], 404); exit; }

        Database::update('knowledge_articles', $data, 'id = ?', [$id]);

        // Audit log
        Database::insert('audit_logs', [
            'user_id' => Auth::userId(),
            'action' => 'UPDATE',
            'resource_type' => 'knowledge',
            'resource_id' => $id,
            'details' => json_encode(['title' => $title, 'old_title' => $existing['title']]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        json_response(['success' => true, 'id' => $id, 'message' => 'Article updated']);
    } else {
        // Create new article
        $data['author_id'] = Auth::userId();
        $newId = Database::insert('knowledge_articles', $data);

        Database::insert('audit_logs', [
            'user_id' => Auth::userId(),
            'action' => 'CREATE',
            'resource_type' => 'knowledge',
            'resource_id' => $newId,
            'details' => json_encode(['title' => $title]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        json_response(['success' => true, 'id' => $newId, 'message' => 'Article created']);
    }
} catch (Exception $e) {
    json_response(['error' => 'Failed to save article: ' . $e->getMessage()], 500);
}
