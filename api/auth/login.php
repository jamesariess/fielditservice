<?php
/**
 * API: User Login
 * POST /api/auth/login
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
} else {
    require_once APP_ROOT . '/includes/DemoData.php';
}
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/DemoData.php';

Auth::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(['error' => 'Email and password required'], 400);
    exit;
}

$user = Auth::login($email, $password);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

json_response([
    'success' => true,
    'redirect' => '/',
    'user' => [
        'id' => $user['id'],
        'name' => $user['full_name'] ?? $user['name'],
        'email' => $user['email'],
        'role' => $user['role_name'] ?? $user['role'],
    ],
]);
