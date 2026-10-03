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
Auth::requirePermission('users.manage');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// /api/users/invite or /api/users/{id}
$action = end($segments);

if ($method === 'POST' && $action === 'invite') {
    $email = strtolower(trim($input['email'] ?? ''));
    $name = trim($input['name'] ?? '');
    $password = (string)($input['password'] ?? '');
    $role = $input['role'] ?? 'standard_user';
    $dept = $input['department'] ?? 'IT';
    if (!$email || !$name || !$password) { json_response(['error' => 'Name, email, and password are required'], 400); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { json_response(['error' => 'Enter a valid email address'], 400); }
    if (strlen($password) < 8) { json_response(['error' => 'Password must be at least 8 characters'], 400); }
    $demo = !defined('DEMO_MODE') || DEMO_MODE;
    if (!$demo) {
        try {
            $db = Database::getInstance();
            if (Database::fetch('SELECT id FROM users WHERE email = ? LIMIT 1', [$email])) {
                json_response(['error' => 'An account with this email already exists'], 409);
                exit;
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $roleId = (int)$role;
            $deptId = (int)$dept;
            if ($roleId <= 0) {
                $roleRow = Database::fetch("SELECT id FROM roles WHERE name = ? LIMIT 1", [$role]);
                $roleId = (int)($roleRow['id'] ?? 0);
            }
            if ($deptId <= 0) {
                $deptRow = Database::fetch("SELECT id FROM departments WHERE name = ? LIMIT 1", [$dept]);
                $deptId = (int)($deptRow['id'] ?? 0);
            }
            if ($roleId <= 0 || $deptId <= 0) { json_response(['error' => 'Select a valid role and department'], 400); }
            // Users are created as active accounts. Keep this compatible with
            // the deployed schema, which has no invitation_token column.
            Database::execute("INSERT INTO users (email, full_name, password_hash, role_id, department_id, status) VALUES (?, ?, ?, ?, ?, 'active')", [$email, $name, $hash, $roleId, $deptId]);
            Database::insert('audit_logs', [
                'user_id' => Auth::userId(), 'action' => 'INVITE', 'resource_type' => 'user',
                'resource_id' => (int)$db->lastInsertId(),
                'details' => json_encode(['email' => $email, 'full_name' => $name, 'role_id' => $roleId, 'department_id' => $deptId]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'created_at' => date('Y-m-d H:i:s')
            ]);
            json_response(['success' => true, 'message' => 'User created: ' . $email]);
        } catch (Throwable $e) {
            error_log('Create user failed: ' . $e->getMessage());
            json_response(['error' => 'Unable to create the user. Check that the selected role and department exist.'], 500);
        }
    } else {
        json_response(['success' => true, 'message' => 'User created: ' . $email, 'demo' => true]);
    }
} elseif ($method === 'POST' && $action === 'save') {
    $userId = intval($input['id'] ?? 0);
    $name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $roleId = intval($input['role_id'] ?? 0);
    $deptId = intval($input['department_id'] ?? 0);
    if (!$userId) { json_response(['error' => 'Invalid user ID'], 400); }
    $demo = !defined('DEMO_MODE') || DEMO_MODE;
    if (!$demo) {
        try {
            $db = Database::getInstance();
            $set = ['full_name = ?'];
            $params = [$name];
            if ($email) { $set[] = 'email = ?'; $params[] = $email; }
            if ($roleId) { $set[] = 'role_id = ?'; $params[] = $roleId; }
            if ($deptId) { $set[] = 'department_id = ?'; $params[] = $deptId; }
            $params[] = $userId;
            Database::execute('UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
            Database::insert('audit_logs', [
                'user_id' => Auth::userId(), 'action' => 'UPDATE', 'resource_type' => 'user', 'resource_id' => $userId,
                'details' => json_encode(['full_name' => $name, 'email' => $email, 'role_id' => $roleId, 'department_id' => $deptId]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'created_at' => date('Y-m-d H:i:s')
            ]);
            json_response(['success' => true, 'message' => 'User updated']);
        } catch (Throwable $e) {
            error_log('Update user failed: ' . $e->getMessage());
            json_response(['error' => 'Unable to update the user.'], 500);
        }
    } else {
        json_response(['success' => true, 'message' => 'User updated', 'demo' => true]);
    }
} elseif ($method === 'DELETE') {
    $userId = intval($_GET['id'] ?? $action);
    if (!$userId) { json_response(['error' => 'Invalid user ID'], 400); }
    if ($userId === (int)Auth::userId()) { json_response(['error' => 'You cannot deactivate your own account'], 400); }
    $demo = !defined('DEMO_MODE') || DEMO_MODE;
    if (!$demo) {
        try {
            $db = Database::getInstance();
            Database::execute("UPDATE users SET status = 'inactive' WHERE id = ?", [$userId]);
            Database::insert('audit_logs', [
                'user_id' => Auth::userId(), 'action' => 'DEACTIVATE', 'resource_type' => 'user', 'resource_id' => $userId,
                'details' => json_encode(['status' => 'inactive']),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'created_at' => date('Y-m-d H:i:s')
            ]);
            json_response(['success' => true]);
        } catch (Throwable $e) {
            error_log('Deactivate user failed: ' . $e->getMessage());
            json_response(['error' => 'Unable to deactivate the user.'], 500);
        }
    } else {
        json_response(['success' => true, 'demo' => true]);
    }
} else {
    json_response(['error' => 'Invalid endpoint'], 400);
}
