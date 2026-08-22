<?php
/**
 * Profile API - update user profile
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
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    try {
        $db = Database::getInstance();
        $sets = [];
        $vals = [];
        if ($name) { $sets[] = "full_name = ?"; $vals[] = $name; }
        if ($email) { $sets[] = "email = ?"; $vals[] = $email; }
        if ($phone) { $sets[] = "phone = ?"; $vals[] = $phone; }
        if (!empty($sets)) {
            $vals[] = Auth::userId();
            $db->execute("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
        }
        json_response(['success' => true, 'message' => 'Profile updated']);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
} else {
    json_response(['success' => true, 'message' => 'Profile updated', 'demo' => true]);
}
