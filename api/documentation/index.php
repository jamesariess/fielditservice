<?php
/**
 * Documentation API - submit documentation for review
 */
require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); }
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$title = $input['title'] ?? '';
$content = $input['content'] ?? '';
$category = $input['category'] ?? 'General';
$tags = $input['tags'] ?? '';
if (!$title || !$content) { json_response(['error' => 'Title and content required'], 400); }

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    try {
        $db = Database::getInstance();
        $db->execute("INSERT INTO knowledge_articles (title, content, category, tags, author_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'draft', NOW())", [$title, $content, $category, $tags, Auth::userId()]);
        json_response(['success' => true, 'message' => 'Documentation submitted for review']);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
} else {
    json_response(['success' => true, 'message' => 'Documentation submitted for review', 'demo' => true]);
}
