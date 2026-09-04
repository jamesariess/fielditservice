<?php
/**
 * Authentication & Authorization Helper
 *
 * Security responsibilities:
 *   - Starts a hardened PHP session (see Security::hardenSession()).
 *   - Rotates the session ID on login (prevents session fixation).
 *   - Binds the session to a client fingerprint (prevents obvious hijacking).
 *   - Enforces BOTH an idle timeout and an absolute lifetime server-side.
 *   - Registers successful/failed logins in the audit trail.
 */
require_once __DIR__ . '/Security.php';

class Auth {

    /**
     * Start (or re-use) the hardened session and enforce session policy.
     * Call at the very beginning of every page/API request.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            Security::hardenSession();
            session_start();
        }

        // Session hijack mitigation: if the client fingerprint changed
        // mid-session, wipe the auth data (user will be asked to log in again).
        if (self::isLoggedIn() && ($_SESSION['fingerprint'] ?? null) !== Security::fingerprint()) {
            self::destroyAuth();
        }

        // Server-authoritative idle + absolute timeouts.
        self::enforceTimeouts();
    }

    /**
     * Attempt login. Returns the authenticated user (array) or null.
     * On success the session ID is rotated and session keys are bootstrapped.
     */
    public static function login(string $email, string $password): ?array {
        // ---- Demo mode (no database) ----
        if (defined('DEMO_MODE') && DEMO_MODE) {
            global $DEMO_USERS;
            $demoUser = $DEMO_USERS[$email] ?? null;
            if (!$demoUser || $demoUser['password'] !== $password) return null;

            self::bootstrapSession([
                'user_id'          => $demoUser['id'],
                'user_email'       => $demoUser['email'],
                'user_name'        => $demoUser['full_name'],
                'role_id'          => $demoUser['role_id'],
                'role_name'        => $demoUser['role_name'],
                'department_id'    => $demoUser['department_id'],
                'department_name'  => $demoUser['department_name'],
                'permissions'      => $demoUser['permissions'],
            ]);
            return $demoUser;
        }

        // ---- Database mode ----
        $user = Database::fetch(
            "SELECT id, email, password_hash, full_name, role_id, department_id, status
             FROM users WHERE email = ? AND status = 'active' LIMIT 1",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::audit('LOGIN_FAILED', null, ['email' => $email]);
            return null;
        }

        // Get role name
        $role = Database::fetch("SELECT name FROM roles WHERE id = ?", [$user['role_id']]);

        self::bootstrapSession([
            'user_id'          => $user['id'],
            'user_email'       => $user['email'],
            'user_name'        => $user['full_name'],
            'role_id'          => $user['role_id'],
            'role_name'        => $role['name'] ?? 'User',
            'department_id'    => $user['department_id'],
            'department_name'  => null,
        ]);

        // Permissions are loaded to the session so hasPermission() is fast.
        self::loadPermissions($user['id'], $user['role_id']);

        try {
            Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        } catch (Exception $e) {
            // Non-fatal: never block login because of a failed side-effect.
        }
        self::audit('LOGIN', $user['id'], ['method' => 'password']);

        return $user;
    }

    /**
     * Destroy the session and redirect to the login page.
     */
    public static function logout(): void {
        self::destroyAuth();
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Also expire the session cookie (belt + suspenders).
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            session_unset();
            session_destroy();
        }
        redirect(app_base() . 'login');
    }

    /**
     * True when a user is currently authenticated.
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Gate for authenticated pages/APIs. Responds with a redirect (pages)
     * or HTTP 401 JSON (APIs) when the user is not (or is no longer) logged in.
     */
    public static function requireLogin(): void {
        self::start();
        if (!self::isLoggedIn()) {
            if (self::isApiRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required or session expired']);
                exit;
            }
            header('Location: ' . app_base() . 'login');
            exit;
        }
    }

    /**
     * requireLogin() + a permission check against the user's role permissions.
     */
    public static function requirePermission(string $permission): void {
        self::requireLogin();
        if (!self::hasPermission($permission)) {
            if (self::isApiRequest()) {
                http_response_code(403);
                echo json_encode(['error' => 'Permission denied', 'required' => $permission]);
                exit;
            }
            http_response_code(403);
            include APP_ROOT . '/public/pages/errors/403.php';
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

    /**
     * Get (or create) the CSRF token for the current session.
     * Delegates to the shared Security component.
     */
    public static function generateCsrfToken(): string {
        return Security::generateCsrf();
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /**
     * Rotate the session ID (anti-fixation) and populate the auth keys.
     * Called once after a successful login.
     */
    private static function bootstrapSession(array $fields): void {
        session_regenerate_id(true);

        foreach ($fields as $key => $value) {
            $_SESSION[$key] = $value;
        }

        $_SESSION['login_time']            = time();
        $_SESSION['last_activity']         = time();
        $_SESSION['last_activity_checked'] = time();
        $_SESSION['fingerprint']           = Security::fingerprint();
    }

    /**
     * Enforce SESSION_IDLE_TIMEOUT (no user activity) and SESSION_LIFETIME
     * (absolute cap). Wipes auth data when either is exceeded — requireLogin()
     * then handles the redirect/401. last_activity is refreshed at most once
     * per minute to keep request overhead negligible.
     */
    private static function enforceTimeouts(): void {
        if (!self::isLoggedIn()) return;

        $now        = time();
        $loginTime  = $_SESSION['login_time'] ?? $now;
        $lastActive = $_SESSION['last_activity'] ?? $loginTime;

        $absoluteExpired = ($now - $loginTime)  > SESSION_LIFETIME;
        $idleExpired     = ($now - $lastActive) > SESSION_IDLE_TIMEOUT;

        if ($absoluteExpired || $idleExpired) {
            self::destroyAuth();
            return;
        }

        // Rolling activity watermark (throttled to 1 write/minute).
        $lastChecked = $_SESSION['last_activity_checked'] ?? 0;
        if ($now - $lastChecked >= 60) {
            $_SESSION['last_activity']          = $now;
            $_SESSION['last_activity_checked']  = $now;
        }
    }

    /**
     * Remove all auth-related keys from the session object.
     * Keeps non-auth keys (e.g. the CSRF token) intact.
     */
    private static function destroyAuth(): void {
        $authKeys = [
            'user_id', 'user_email', 'user_name', 'role_id', 'role_name',
            'department_id', 'department_name', 'permissions', 'fingerprint',
            'login_time', 'last_activity', 'last_activity_checked',
        ];
        foreach ($authKeys as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Load permissions for the user's role into the session.
     * Falls back to '*.*' when the permissions tables are missing (dev setup).
     */
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
            $_SESSION['permissions'] = ['*.*'];
        }
    }

    /**
     * Append a row to the audit log (best-effort — never breaks the request).
     */
    private static function audit(string $action, ?int $userId, array $details): void {
        try {
            Database::insert('audit_logs', [
                'user_id'       => $userId,
                'action'        => $action,
                'resource_type' => 'auth',
                'details'       => json_encode($details),
                'ip_address'    => Security::clientIp(),
            ]);
        } catch (Exception $e) {
            // Never let audit failures break authentication.
        }
    }

    /**
     * True when the current request targets /api/*.
     * Path is app-base aware so the check works from any sub-folder.
     */
    private static function isApiRequest(): bool {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $base = app_base();
        if ($base !== '/' && str_starts_with($uri, $base)) {
            $uri = '/' . substr($uri, strlen($base));
        }
        return str_starts_with($uri, '/api/');
    }
}
