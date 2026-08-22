<?php
/**
 * Field IT Support Hub - Main Router
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

// Route map
$routes = [
    '/' => APP_ROOT . '/public/pages/dashboard.php',
    '/login' => APP_ROOT . '/public/pages/login.php',
    '/logout' => 'logout',
    '/troubleshoot' => APP_ROOT . '/public/pages/troubleshoot.php',
    '/troubleshoot/wizard' => APP_ROOT . '/public/pages/troubleshoot-wizard.php',
    '/knowledge' => APP_ROOT . '/public/pages/knowledge.php',
    '/knowledge/view' => APP_ROOT . '/public/pages/knowledge-view.php',
    '/equipment' => APP_ROOT . '/public/pages/equipment.php',
    '/equipment/model' => APP_ROOT . '/public/pages/equipment-model.php',
    '/commands' => APP_ROOT . '/public/pages/commands.php',
    '/tools' => APP_ROOT . '/public/pages/tools.php',
    '/ai' => APP_ROOT . '/public/pages/ai.php',
    '/tickets' => APP_ROOT . '/public/pages/tickets.php',
    '/documentation' => APP_ROOT . '/public/pages/documentation.php',
    '/chat' => APP_ROOT . '/public/pages/chat.php',
    '/profile' => APP_ROOT . '/public/pages/profile.php',
];

// Admin routes
$adminRoutes = [
    '/admin/users' => APP_ROOT . '/public/pages/admin/users.php',
    '/admin/roles' => APP_ROOT . '/public/pages/admin/roles.php',
    '/admin/departments' => APP_ROOT . '/public/pages/admin/departments.php',
    '/admin/knowledge' => APP_ROOT . '/public/pages/admin/knowledge.php',
    '/admin/equipment' => APP_ROOT . '/public/pages/admin/equipment.php',
    '/admin/ai' => APP_ROOT . '/public/pages/admin/ai.php',
    '/admin/audit' => APP_ROOT . '/public/pages/admin/audit.php',
    '/admin/statistics' => APP_ROOT . '/public/pages/admin/statistics.php',
    '/admin/settings' => APP_ROOT . '/public/pages/admin/settings.php',
    '/admin/troubleshoot' => APP_ROOT . '/public/pages/admin-troubleshoot.php',
    '/troubleshoot/submit' => APP_ROOT . '/public/pages/troubleshoot-submit.php',
    '/api/troubleshooting/submissions' => APP_ROOT . '/api/troubleshooting/submissions.php',
    '/api/troubleshooting/errors' => APP_ROOT . '/api/troubleshooting/errors.php',
    '/api/troubleshooting/nodes' => APP_ROOT . '/api/troubleshooting/nodes.php',
];

// Check for logout
if ($uri === '/logout') {
    Auth::logout();
}

// Check admin routes first
if (isset($adminRoutes[$uri])) {
    Auth::requireLogin();
    // Permission will be checked in the page file
    $file = $adminRoutes[$uri];
    if (file_exists($file)) {
        require $file;
    } else {
        http_response_code(404);
        include APP_ROOT . '/public/pages/404.php';
    }
    exit;
}

// Handle public routes (login)
if ($uri === '/login') {
    if (Auth::isLoggedIn()) {
        redirect('/');
    }
    require APP_ROOT . '/public/pages/login.php';
    exit;
}

// Handle regular routes
if (isset($routes[$uri])) {
    $file = $routes[$uri];
    if (file_exists($file)) {
        Auth::requireLogin();
        require $file;
    } else {
        http_response_code(404);
        include APP_ROOT . '/public/pages/404.php';
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
if (file_exists(APP_ROOT . '/public/pages/404.php')) {
    include APP_ROOT . '/public/pages/404.php';
} else {
    echo '<h1>404 - Page Not Found</h1>';
}
