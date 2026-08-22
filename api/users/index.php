<?php
/**
 * Users API - invite, list, update, delete
 */
require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// /api/users/invite or /api/users/{id}
$action = end($segments);

if ($method === 'POST' && $action === 'invite') {
    $email = $input['email'] ?? '';
    $name = $input['name'] ?? '';
    $role = $input['role'] ?? 'standard_user';
    $dept = $input['department'] ?? 'IT';
    if (!$email || !$name) { json_response(['error' => 'Email and name required'], 400); }
    $demo = !defined('DEMO_MODE') || DEMO_MODE;
    if (!$demo) {
        try {
            $db = Database::getInstance();
            $token = bin2hex(random_bytes(32));
            $hash = password_hash('changeme', PASSWORD_DEFAULT);
            $db->execute("INSERT INTO users (email, full_name, password_hash, role_id, department_id, status, invitation_token) VALUES (?, ?, ?, (SELECT id FROM roles WHERE name = ? LIMIT 1), (SELECT id FROM departments WHERE name = ? LIMIT 1), 'pending', ?)", [$email, $name, $hash, $role, $dept, $token]);
            json_response(['success' => true, 'message' => 'Invitation sent to ' . $email, 'token' => $token]);
        } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
    } else {
        json_response(['success' => true, 'message' => 'Invitation sent to ' . $email, 'demo' => true]);
    }
} elseif ($method === 'DELETE') {
    $userId = intval($_GET['id'] ?? $action);
    if (!$userId) { json_response(['error' => 'Invalid user ID'], 400); }
    $demo = !defined('DEMO_MODE') || DEMO_MODE;
    if (!$demo) {
        try {
            $db = Database::getInstance();
            $db->execute("UPDATE users SET status = 'inactive' WHERE id = ?", [$userId]);
            json_response(['success' => true]);
        } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
    } else {
        json_response(['success' => true, 'demo' => true]);
    }
} else {
    json_response(['error' => 'Invalid endpoint'], 400);
}
