<?php
/**
 * Field IT Support Hub - Application Configuration
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Hosting configuration can come from environment variables or from the
// gitignored config/production.php file used by shared PHP hosting.
$deploymentConfig = [];
$deploymentConfigPath = __DIR__ . '/production.php';
if (is_file($deploymentConfigPath)) {
    $loadedConfig = require $deploymentConfigPath;
    if (is_array($loadedConfig)) $deploymentConfig = $loadedConfig;
}
$configValue = static function (string $key, $default = null) use ($deploymentConfig) {
    $environmentValue = getenv($key);
    if ($environmentValue !== false && $environmentValue !== '') return $environmentValue;
    return $deploymentConfig[$key] ?? $default;
};

// Application
define('APP_NAME', 'Field IT Support Hub');
define('APP_VERSION', '1.0.0');
define('APP_ENV', (string)$configValue('APP_ENV', 'local'));
define('APP_URL', rtrim((string)$configValue('APP_URL', 'http://localhost:8000'), '/'));

// Database
define('DB_HOST', (string)$configValue('DB_HOST', 'localhost'));
define('DB_PORT', (int)$configValue('DB_PORT', 3306));
define('DB_NAME', (string)$configValue('DB_NAME', 'fieldit_hub'));
define('DB_USER', (string)$configValue('DB_USER', 'root'));
define('DB_PASS', (string)$configValue('DB_PASS', ''));
define('DB_CHARSET', (string)$configValue('DB_CHARSET', 'utf8mb4'));

// Security
define('SESSION_LIFETIME', 8 * 3600);       // absolute max session life: 8 hours
define('SESSION_IDLE_TIMEOUT', 30 * 60);    // idle timeout: 30 min without user activity
define('SESSION_WARNING_SECONDS', 5 * 60);  // how early the UI warns before expiring
define('CSRF_TOKEN_NAME', '_csrf');
define('MAX_LOGIN_ATTEMPTS', 5);            // failed attempts before lockout
define('LOCKOUT_DURATION', 900);            // lockout window: 15 minutes

// File Upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'text/plain']);

// AI Configuration — the API key lives in config/secrets.php (gitignored).
// Copy config/secrets.example.php to config/secrets.php and add your real key there.
if (is_file(__DIR__ . '/secrets.php')) { require_once __DIR__ . '/secrets.php'; }
if (!defined('OPENAI_API_KEY')) { define('OPENAI_API_KEY', ''); } // empty = AI falls back to DB answers
define('AI_PROVIDER', 'openai'); // ollama, openai, none
define('OPENAI_BASE_URL', 'https://api.groq.com/openai/v1'); // Groq OpenAI-compatible endpoint
define('OPENAI_MODEL', 'openai/gpt-oss-120b');
// AI usage limits — admins always have unlimited AI access
define('AI_BURST_LIMIT', 30);  // max messages per minute (all users, incl. burst protection)
define('AI_DAILY_LIMIT', 50);  // max messages per user per day (0 = unlimited; admins always unlimited)

// Timezone
date_default_timezone_set((string)$configValue('APP_TIMEZONE', 'UTC'));

// Errors are logged in production but never rendered to visitors.
error_reporting(E_ALL);
ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');
ini_set('log_errors', 1);
