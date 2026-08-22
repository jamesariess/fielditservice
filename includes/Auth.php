<?php
/**
 * Authentication & Authorization Helper
 */
class Auth {

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public static function login(string $email, string $password): ?array {
        // Demo mode
        if (defined('DEMO_MODE') && DEMO_MODE) {
            global $DEMO_USERS;
            $demoUser = $DEMO_USERS[$email] ?? null;
            if (!$demoUser || $demoUser['password'] !== $password) return null;

            $_SESSION['user_id'] = $demoUser['id'];
            $_SESSION['user_email'] = $demoUser['email'];
            $_SESSION['user_name'] = $demoUser['full_name'];
            $_SESSION['role_id'] = $demoUser['role_id'];
            $_SESSION['role_name'] = $demoUser['role_name'];
            $_SESSION['department_id'] = $demoUser['department_id'];
            $_SESSION['department_name'] = $demoUser['department_name'];
            $_SESSION['permissions'] = $demoUser['permissions'];
            $_SESSION['login_time'] = time();
            return $demoUser;
        }

        // Database mode
        $user = Database::fetch(
            "SELECT id, email, password_hash, full_name, role_id, department_id, status
             FROM users WHERE email = ? AND status = 'active' LIMIT 1",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            try {
                Database::insert('audit_logs', [
                    'user_id' => null,
                    'action' => 'LOGIN_FAILED',
                    'resource_type' => 'auth',
                    'details' => json_encode(['email' => $email]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
            } catch (Exception $e) {}
            return null;
        }

        // Get role name
        $role = Database::fetch("SELECT name FROM roles WHERE id = ?", [$user['role_id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $role['name'] ?? 'User';
        $_SESSION['department_id'] = $user['department_id'];
        $_SESSION['login_time'] = time();

        // Load permissions from role_permissions
        self::loadPermissions($user['id'], $user['role_id']);

        try {
            Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
            Database::insert('audit_logs', [
                'user_id' => $user['id'],
                'action' => 'LOGIN',
                'resource_type' => 'auth',
                'details' => json_encode(['method' => 'password']),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        } catch (Exception $e) {}

        return $user;
    }

    public static function logout(): void {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void {
        self::start();
        if (!self::isLoggedIn()) {
            if (self::isApiRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
        if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
            self::logout();
        }
    }

    public static function requirePermission(string $permission): void {
        self::requireLogin();
        if (!self::hasPermission($permission)) {
            if (self::isApiRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Permission denied', 'required' => $permission]);
                exit;
            }
            http_response_code(403);
            include APP_ROOT . '/public/pages/403.php';
            exit;
        }
    }

    public static function hasPermission(string $permission): bool {
        $perms = $_SESSION['permissions'] ?? [];
        return in_array($permission, $perms) || in_array('*.*', $perms);
    }

    public static function userId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function userName(): ?string {
        return $_SESSION['user_name'] ?? null;
    }

    public static function userEmail(): ?string {
        return $_SESSION['user_email'] ?? null;
    }

    public static function departmentId(): ?int {
        return $_SESSION['department_id'] ?? null;
    }

    public static function roleId(): ?int {
        return $_SESSION['role_id'] ?? null;
    }

    private static function loadPermissions(int $userId, int $roleId): void {
        try {
            $permissions = Database::fetchAll(
                "SELECT DISTINCT p.permission_key
                 FROM permissions p
                 INNER JOIN role_permissions rp ON rp.permission_id = p.id AND rp.role_id = ?",
                [$roleId]
            );
            $_SESSION['permissions'] = array_column($permissions, 'permission_key');
        } catch (Exception $e) {
            // If table missing, grant all permissions for now
            $_SESSION['permissions'] = ['*.*'];
        }
    }

    private static function isApiRequest(): bool {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        return str_starts_with($uri, '/api/');
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
