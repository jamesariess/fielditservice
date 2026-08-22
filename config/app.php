<?php
/**
 * Field IT Support Hub - Application Configuration
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Application
define('APP_NAME', 'Field IT Support Hub');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost:8000');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'fieldit_hub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SESSION_LIFETIME', 8 * 3600); // 8 hours
define('CSRF_TOKEN_NAME', '_csrf');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// File Upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'text/plain']);

// AI Configuration
define('AI_PROVIDER', 'ollama'); // ollama, openai, none
define('OLLAMA_URL', 'http://localhost:11434');
define('OLLAMA_MODEL', 'llama3.2');
define('AI_RATE_LIMIT', 30); // requests per minute per user

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
