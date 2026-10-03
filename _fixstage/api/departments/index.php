<?php
/**
 * Departments API - add department
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
$name = $input['name'] ?? '';
$desc = $input['description'] ?? '';
if (!$name) { json_response(['error' => 'Name required'], 400); }

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    try {
        $db = Database::getInstance();
        $db->execute("INSERT INTO departments (name, description, is_active) VALUES (?, ?, 1)", [$name, $desc]);
        json_response(['success' => true, 'message' => 'Department added']);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
} else {
    json_response(['success' => true, 'message' => 'Department added', 'demo' => true]);
}
