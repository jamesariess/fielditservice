<?php
/**
 * Settings API - save system settings
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

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    try {
        $db = Database::getInstance();
        foreach ($input as $key => $value) {
            if ($key === 'action') continue;
            $db->execute("INSERT INTO system_settings (`key`, `value`, `updated_by`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_by = VALUES(updated_by)", [$key, $value, Auth::userId()]);
        }
        json_response(['success' => true, 'message' => 'Settings saved']);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
} else {
    json_response(['success' => true, 'message' => 'Settings saved', 'demo' => true]);
}
