<?php
/**
 * Field IT Support Hub - Main Router
 *
 * Route map:
 *   /                 → dashboard          (auth required)
 *   /login            → auth/login         (guest only)
 *   /logout           → logout
 *   /troubleshoot     → troubleshoot
 *   ...               → app pages          (auth required)
 *   /admin/*          → admin pages        (auth + permission required, enforced centrally)
 *   /api/*            → api endpoints      (auth required per-endpoint)
 */

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';

// Only load Database in non-demo mode
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
}

require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/DemoData.php';

Auth::start();

// Serve static files directly
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    // For static files, use the full URI path relative to the server document root
    // Try document root first (Apache), then __DIR__ (built-in server)
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $uri;
    if (!file_exists($filePath)) {
        $filePath = __DIR__ . $uri;
    }
    if (file_exists($filePath)) {
        $mimeTypes = [
            'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    exit;
}

// Strip base path when accessed through Apache (e.g., /fielditservice/public/login -> /login)
$publicPos = strpos($uri, '/public/');
if ($publicPos !== false) {
    $uri = substr($uri, $publicPos + 7); // +7 for '/public/'
}

// Strip .php extension for clean URLs
$uri = preg_replace('/\.php$/', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

/**
 * ------------------------------------------------------------------
 * ADMIN ROUTES — every entry is centrally secured by permission key.
 * Pages live in public/pages/admin/ and ALSO re-check via
 * includes/admin_guard.php (defense in depth).
 * ------------------------------------------------------------------
 */
$adminRoutes = [
    '/admin/users'          => ['file' => APP_ROOT . '/public/pages/admin/users.php',          'perm' => 'users.manage'],
    '/admin/roles'          => ['file' => APP_ROOT . '/public/pages/admin/roles.php',          'perm' => 'roles.manage'],
    '/admin/departments'    => ['file' => APP_ROOT . '/public/pages/admin/departments.php',    'perm' => 'departments.manage'],
    '/admin/knowledge'      => ['file' => APP_ROOT . '/public/pages/admin/knowledge.php',      'perm' => 'knowledge.manage'],
    '/admin/equipment'      => ['file' => APP_ROOT . '/public/pages/admin/equipment.php',      'perm' => 'equipment.manage'],
    '/admin/ai'             => ['file' => APP_ROOT . '/public/pages/admin/ai.php',             'perm' => 'ai.manage'],
    '/admin/audit'          => ['file' => APP_ROOT . '/public/pages/admin/audit.php',          'perm' => 'audit.view'],
    '/admin/statistics'     => ['file' => APP_ROOT . '/public/pages/admin/statistics.php',     'perm' => 'audit.view'],
    '/admin/settings'       => ['file' => APP_ROOT . '/public/pages/admin/settings.php',       'perm' => 'system.settings'],
    '/admin/troubleshoot'   => ['file' => APP_ROOT . '/public/pages/admin/troubleshoot.php',   'perm' => 'system.settings'],
];

/**
 * ------------------------------------------------------------------
 * APP ROUTES (authenticated area)
 * ------------------------------------------------------------------
 */
$routes = [
    '/' => APP_ROOT . '/public/pages/dashboard.php',
    '/troubleshoot' => APP_ROOT . '/public/pages/troubleshoot.php',
    '/troubleshoot/wizard' => APP_ROOT . '/public/pages/troubleshoot-wizard.php',
    '/troubleshoot/submit' => APP_ROOT . '/public/pages/troubleshoot-submit.php',
    '/knowledge' => APP_ROOT . '/public/pages/knowledge.php',
    '/knowledge/view' => APP_ROOT . '/public/pages/knowledge-view.php',
    '/equipment' => APP_ROOT . '/public/pages/equipment-unified.php',
    '/equipment/model' => APP_ROOT . '/public/pages/equipment-model.php',
    '/brand' => APP_ROOT . '/public/pages/brand.php',
    '/commands' => APP_ROOT . '/public/pages/commands.php',
    '/tools' => APP_ROOT . '/public/pages/tools.php',
    '/ai' => APP_ROOT . '/public/pages/ai.php',
    '/tickets' => APP_ROOT . '/public/pages/tickets.php',
    '/documentation' => APP_ROOT . '/public/pages/documentation.php',
    '/chat' => APP_ROOT . '/public/pages/chat.php',
    '/profile' => APP_ROOT . '/public/pages/profile.php',
    '/api/troubleshooting/submissions' => APP_ROOT . '/api/troubleshooting/submissions.php',
    '/api/troubleshooting/errors' => APP_ROOT . '/api/troubleshooting/errors.php',
    '/api/troubleshooting/nodes' => APP_ROOT . '/api/troubleshooting/nodes.php',
];

// Check for logout
if ($uri === '/logout') {
    Auth::logout();
}

// Handle login (guest route)
if ($uri === '/login') {
    if (Auth::isLoggedIn()) {
        redirect('/');
    }
    require APP_ROOT . '/public/pages/auth/login.php';
    exit;
}

// ADMIN: enforce permission at the router level (before any admin markup renders)
if (isset($adminRoutes[$uri])) {
    Auth::requireLogin();
    if (!Auth::hasPermission($adminRoutes[$uri]['perm'])) {
        http_response_code(403);
        include APP_ROOT . '/public/pages/errors/403.php';
        exit;
    }
    $adminFile = $adminRoutes[$uri]['file'];
    if (file_exists($adminFile)) {
        require $adminFile;
    } else {
        http_response_code(404);
        include APP_ROOT . '/public/pages/errors/404.php';
    }
    exit;
}

// Handle regular app routes
if (isset($routes[$uri])) {
    $file = $routes[$uri];
    if (file_exists($file)) {
        Auth::requireLogin();
        require $file;
    } else {
        http_response_code(404);
        include APP_ROOT . '/public/pages/errors/404.php';
    }
    exit;
}

// Handle API routes
if (str_starts_with($uri, '/api/')) {
    $apiPath = substr($uri, 4);
    $apiFile = APP_ROOT . '/api' . $apiPath . '.php';
    $apiIndex = APP_ROOT . '/api' . $apiPath . '/index.php';
    // Try exact file, then index.php in directory
    if (file_exists($apiFile)) {
        require $apiFile;
    } elseif (file_exists($apiIndex)) {
        require $apiIndex;
    } else {
        http_response_code(404);
        json_response(['error' => 'API endpoint not found'], 404);
    }
    exit;
}

// 404 - Not found
http_response_code(404);
include APP_ROOT . '/public/pages/errors/404.php';
