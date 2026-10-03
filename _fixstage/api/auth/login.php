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

// CSRF guard — the login page always sends the token via X-CSRF-Token header.
Security::requireCsrf();

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(['error' => 'Email and password required'], 400);
    exit;
}

// ---- Brute-force protection: per-IP+email failed-attempt throttle ----
$throttleKey = 'login_failures_' . md5($email . '|' . Security::clientIp());
$failures = $_SESSION[$throttleKey] ?? ['count' => 0, 'first' => 0];

// Reset the counter once the lockout window has elapsed.
if (time() - ($failures['first'] ?? 0) > LOCKOUT_DURATION) {
    $failures = ['count' => 0, 'first' => time()];
}

if ($failures['count'] >= MAX_LOGIN_ATTEMPTS) {
    $waitMin = (int) ceil((LOCKOUT_DURATION - (time() - $failures['first'])) / 60);
    json_response(['error' => "Too many failed attempts. Try again in {$waitMin} minute(s)."], 429);
}

$user = Auth::login($email, $password);

if (!$user) {
    // Record the failure against the same IP + account.
    $failures['count']++;
    if ($failures['count'] === 1) {
        $failures['first'] = time();
    }
    $_SESSION[$throttleKey] = $failures;

    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

// Success — clear any prior failure counter.
unset($_SESSION[$throttleKey]);

json_response([
    'success' => true,
    'redirect' => '/',
    'user' => [
        'id' => $user['id'],
        'name' => $user['full_name'] ?? $user['name'],
        'email' => $user['email'],
        'role' => $user['role_name'] ?? ($user['role'] ?? null),
    ],
]);
