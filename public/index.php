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

// HTTP security headers (defense-in-depth) — applied to every response.
Security::applyHeaders();

// Parse + normalize the request path to an app-relative route.
//
// Works for every install style:
//   /fielditservice/login          -> /login
//   /fielditservice/public/login   -> /login   (legacy style)
//   /login                         -> /login   (domain-root install)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
if (str_ends_with($scriptName, '/public/index.php')) {
    // Front controller lives in /public  => app base is its parent directory.
    $appBase = rtrim(substr($scriptName, 0, -strlen('/public/index.php')), '/');
} else {
    $appBase = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
}

if ($appBase !== '' && $appBase !== '/' && str_starts_with($uri, $appBase . '/')) {
    $uri = substr($uri, strlen($appBase));
}
// Legacy /public prefix (kept for old bookmarks/links).
if ($uri === '/public') {
    $uri = '/';
}
if (str_starts_with($uri, '/public/')) {
    $uri = substr($uri, strlen('/public'));
}

// Serve static files directly (CSS/JS/images/fonts).
// $uri is now app-relative (e.g. /assets/js/app.js), so it resolves cleanly
// against the /public directory this router lives in. The docroot fallback
// covers installs served directly from the domain root.
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    $filePath = __DIR__ . $uri;                      // clean URL style (assets live under /public)
    if (!file_exists($filePath)) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $uri; // domain-root / docroot style
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
    '/equipment' => APP_ROOT . '/public/pages/equipment.php',
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

/**
 * ------------------------------------------------------------------
 * FRIENDLY REDIRECTS — direct hits on real page files.
 * ------------------------------------------------------------------
 * Old bookmarks may point straight at PHP files, e.g.
 *   /fielditservice/public/pages/auth/login.php
 * public/pages/.htaccess blocks raw execution (it would bypass auth)
 * and hands the request here. Rather than a hard 403, we reverse-look
 * the file in the route tables above and redirect to its clean route.
 * Files with no route (e.g. errors/*.php) fall through to a 404.
 */
if (preg_match('#^/pages/(.+?)(?:\.php)?$#', $uri, $directHit)) {
    // Canonical (forward-slash) file path — APP_ROOT may use backslashes on Windows.
    $requestedFile = str_replace('\\', '/', APP_ROOT . '/public/pages/' . $directHit[1] . '.php');

    // Build a one-time file -> clean-route reverse map from the tables above.
    $routeByFile = [];
    foreach ($routes as $route => $pageFile) {
        $routeByFile[str_replace('\\', '/', $pageFile)] = $route;
    }
    foreach ($adminRoutes as $route => $config) {
        $routeByFile[str_replace('\\', '/', $config['file'])] = $route;
    }
    // Special guest route (handled outside the tables above).
    $routeByFile[str_replace('\\', '/', APP_ROOT . '/public/pages/auth/login.php')] = '/login';

    if (isset($routeByFile[$requestedFile])) {
        redirect(rtrim(app_base(), '/') . $routeByFile[$requestedFile]);
    }

    http_response_code(404);
    include APP_ROOT . '/public/pages/errors/404.php';
    exit;
}

// Check for logout
if ($uri === '/logout') {
    Auth::logout();
}

// Handle login (guest route)
if ($uri === '/login') {
    if (Auth::isLoggedIn()) {
        redirect(app_base());
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
    // CSRF gate for state-changing, authenticated API calls.
    // The JS layer auto-attaches the X-CSRF-Token header to every fetch,
    // so any request missing (or mismatching) the token is rejected here.
    if ($_SERVER['REQUEST_METHOD'] !== 'GET'
        && $uri !== '/api/auth/login'      // login has its own explicit CSRF check
        && Auth::isLoggedIn()
    ) {
        Security::requireCsrf();
    }

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
