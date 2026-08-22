<?php
/**
 * API: Invitations
 * POST /api/invitations - create invitation
 * GET /api/invitations/verify?token=xxx - verify invitation
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Verify invitation (public endpoint)
if (str_contains($uri, 'verify')) {
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
        json_response(['error' => 'Token required'], 400);
    }

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['valid' => true, 'email' => 'user@example.com', 'role' => 'Field IT', 'department' => 'IT Department']);
    }

    $invitation = Database::fetch(
        "SELECT i.*, r.name as role_name, d.name as department_name
         FROM invitations i
         LEFT JOIN roles r ON r.id = i.role_id
         LEFT JOIN departments d ON d.id = i.department_id
         WHERE i.token = ? AND i.used_at IS NULL AND i.expires_at > NOW()",
        [$token]
    );

    if (!$invitation) {
        json_response(['error' => 'Invalid or expired invitation'], 400);
    }

    json_response([
        'valid' => true,
        'email' => $invitation['email'],
        'role' => $invitation['role_name'],
        'department' => $invitation['department_name'],
    ]);
}

// Create invitation (requires auth + permission)
Auth::requirePermission('users.manage');

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $roleId = (int)($input['role_id'] ?? 4);
    $deptId = (int)($input['department_id'] ?? 1);

    if (!$email) {
        json_response(['error' => 'Valid email required'], 400);
    }

    if (defined('DEMO_MODE') && DEMO_MODE) {
        $token = bin2hex(random_bytes(32));
        json_response(['success' => true, 'token' => $token, 'link' => '/register?token=' . $token]);
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

    $invitationId = Database::insert('invitations', [
        'token' => $token,
        'email' => $email,
        'role_id' => $roleId,
        'department_id' => $deptId,
        'invited_by' => Auth::userId(),
        'expires_at' => $expiresAt,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    // Audit log
    Database::insert('audit_logs', [
        'user_id' => Auth::userId(),
        'action' => 'CREATE',
        'resource_type' => 'invitation',
        'resource_id' => $invitationId,
        'details' => json_encode(['email' => $email, 'role_id' => $roleId]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    json_response([
        'success' => true,
        'token' => $token,
        'link' => '/register?token=' . $token,
        'expires_at' => $expiresAt,
    ]);
}

json_response(['error' => 'Method not allowed'], 405);
