<?php
/**
 * LOCAL DEV ONLY - throwaway router for the PHP built-in server.
 *
 * Serves the project root as the docroot so that BOTH the real app
 * (via public/index.php) and the files under tests/ are reachable from one
 * origin. That matters: the mobile crawl harness renders app routes in
 * same-origin iframes, which it can only read when everything shares a port.
 *
 * Usage:
 *   DB_HOST=localhost DB_NAME=fieldit_hub DB_USER=fieldit_dev DB_PASS=fieldit_dev \
 *     php -S 127.0.0.1:8125 -t . _z_devserver_router.php
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Under the built-in server SCRIPT_NAME is the request path itself, which
// breaks public/index.php's app-base heuristic (it truncated /api/auth/login
// to /login). Pin it to the router so the app behaves like Apache/InfinityFree.
$_SERVER['SCRIPT_NAME'] = '/_z_devserver_router.php';
$_SERVER['PHP_SELF'] = '/_z_devserver_router.php';

// Real files on disk (tests/*.php, tests/*.html, ...) are served as-is.
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

// The app's public/ directory is the real docroot, but this router hosts the
// project root so tests/ and the app share one origin. Assets are therefore
// served straight out of public/ here: PHP's built-in server sets SCRIPT_NAME
// to the asset path itself, which makes public/index.php derive a wrong base
// and 404 them.
if (preg_match('#^/(assets|uploads)/#', $path)) {
    $file = __DIR__ . '/public' . $path;
    if (is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject', 'mp4' => 'video/mp4', 'pdf' => 'application/pdf',
        ];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=31536000');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

require __DIR__ . '/public/index.php';
