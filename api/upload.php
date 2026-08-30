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
$type = $_POST['type'] ?? 'general';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate MIME type
if (!in_array($file['type'], $allowedTypes)) {
    json_response(['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP, SVG'], 400);
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

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = $type . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$filepath = $uploadDir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    json_response(['error' => 'Failed to save file'], 500);
    exit;
}

// Return the URL path relative to the project root
$urlPath = '/fielditservice/public/uploads/' . $type . '/' . $filename;

json_response([
    'success' => true,
    'url' => $urlPath,
    'filename' => $filename,
    'size' => $file['size'],
    'type' => $file['type'],
]);
