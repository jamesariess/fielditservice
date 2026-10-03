<?php
/**
 * File Upload API
 * POST /api/upload.php
 * Multipart form data: file, type (tool|equipment|guide)
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = $_FILES['file']['error'] ?? 'unknown';
    json_response(['error' => 'Upload failed. Error code: ' . $errorCode], 400);
    exit;
}

$file = $_FILES['file'];

// $type becomes part of a filesystem path, so it must never be free text.
$allowedTypes = ['tool', 'tools', 'equipment', 'guide', 'general'];
$type = (string)($_POST['type'] ?? 'general');
if (!in_array($type, $allowedTypes, true)) { $type = 'general'; }

$allowedMime = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    // SVG is deliberately not accepted: it can carry script that would run on
    // this origin once opened. Store PNG/JPG instead.
];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate MIME type (sniffed from the file's contents, not the client header)
$mime = null;
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) { $mime = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo); }
}
if ($mime === null) { $mime = (string)$file['type']; }
if (!isset($allowedMime[$mime])) {
    json_response(['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'], 400);
    exit;
}

// Validate size
if ($file['size'] > $maxSize) {
    json_response(['error' => 'File too large. Max 5MB'], 400);
    exit;
}

// Create upload directory based on type
$uploadDir = APP_ROOT . '/public/uploads/' . $type;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename — the extension comes from the sniffed MIME type,
// never from the uploaded filename.
$ext = $allowedMime[$mime];
$filename = $type . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$filepath = $uploadDir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    json_response(['error' => 'Failed to save file'], 500);
    exit;
}

// Return a URL for THIS install. The path used to be hardcoded to
// /fielditservice/public/uploads/..., which 404s on any host where the app is
// not in a folder called fielditservice (e.g. a domain-root InfinityFree site).
$urlPath = app_base() . 'uploads/' . $type . '/' . $filename;

json_response([
    'success' => true,
    'url' => $urlPath,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $file['type'],
]);
