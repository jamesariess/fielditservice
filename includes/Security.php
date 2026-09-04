<?php
/**
 * Security — centralized hardening helpers.
 *
 * Responsibilities:
 *   - Harden session cookie / ini settings (before session_start()).
 *   - Emit HTTP security headers on every response.
 *   - CSRF token generation and verification.
 *   - Client fingerprinting (basic session-hijack mitigation).
 *
 * Keep all security primitives in one place so page code stays clean.
 */
class Security
{
    /**
     * Apply hardened cookie + ini settings. Call ONCE, before session_start().
     */
    public static function hardenSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Reject uninitialized session IDs (anti-fixation) and URL-based IDs.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,          // absolute lifetime (seconds)
            'path'     => '/',
            'httponly' => true,                      // no JavaScript access to the cookie
            'samesite' => 'Strict',                  // never sent on cross-site requests (CSRF)
            'secure'   => self::isHttps(),           // only sent over HTTPS when available
        ]);
    }

    /**
     * Emit HTTP security headers. Safe to call on every response.
     */
    public static function applyHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('X-XSS-Protection: 1; mode=block'); // legacy; harmless
    }

    /**
     * Get (or lazily create) the CSRF token for the current session.
     */
    public static function generateCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the CSRF token. Accepts it from the X-CSRF-Token header,
     * a form field, or the JSON body. Constant-time comparison.
     */
    public static function csrfValid(): bool
    {
        $expected = $_SESSION['csrf_token'] ?? '';
        if ($expected === '') {
            return false;
        }

        $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if ($sent === '') {
            $sent = $_POST[CSRF_TOKEN_NAME] ?? '';
        }
        if ($sent === '') {
            // Some clients send it inside the request body.
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $sent = $body[CSRF_TOKEN_NAME] ?? '';
        }

        return is_string($sent) && hash_equals($expected, $sent);
    }

    /**
     * Like csrfValid() but responds with HTTP 419 and exits when invalid.
     * Use for state-changing endpoints.
     */
    public static function requireCsrf(): void
    {
        if (!self::csrfValid()) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'CSRF token mismatch. Refresh the page and try again.']);
            exit;
        }
    }

    /**
     * Whether the request is travelling over HTTPS
     * (works behind TLS-terminating proxies too).
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    }

    /**
     * Client IP. Only REMOTE_ADDR is trusted — X-Forwarded-For can be spoofed.
     */
    public static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Lightweight client fingerprint (user-agent + IP hash).
     * Used to detect obvious session hijacking.
     */
    public static function fingerprint(): string
    {
        return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . '|' . self::clientIp());
    }
}