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
$solution = $input['content'] ?? $input['solution'] ?? '';
$category = $input['category'] ?? 'General';
$tags = $input['tags'] ?? '';
if (!$title || !$solution) { json_response(['error' => 'Title and content required'], 400); }

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    try {
        Database::insert('knowledge_articles', [
            'title' => $title,
            'category' => $category,
            'solution' => $solution,
            'symptoms' => $input['symptoms'] ?? '',
            'root_cause' => $input['root_cause'] ?? '',
            'tools_used' => $input['tools'] ?? '',
            'commands_used' => $input['commands'] ?? '',
            'device_type' => $input['device_type'] ?? '',
            'author_id' => Auth::userId(),
            'status' => 'submitted',
        ]);
        json_response(['success' => true, 'message' => 'Documentation submitted for review']);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
} else {
    json_response(['success' => true, 'message' => 'Documentation submitted for review', 'demo' => true]);
}
