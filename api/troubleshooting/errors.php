<?php
/**
 * API: Error Code Search
 * GET  ?q=search_term   - search error codes by code, title, or description
 * GET  ?id=N            - get single error code detail
 * GET  ?category=bsod  - filter by category
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

// Get single error code
if (!empty($_GET['id'])) {
    $code = Database::fetch("SELECT * FROM error_codes WHERE id = ?", [(int)$_GET['id']]);
    if (!$code) { json_response(['error' => 'Not found'], 404); exit; }
    json_response($code);
    exit;
}

// Search or list error codes
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

if (!empty($q)) {
    // Search by code, title, or description
    $search = "%{$q}%";
    $codes = Database::fetchAll(
        "SELECT * FROM error_codes 
         WHERE code LIKE ? OR title LIKE ? OR description LIKE ? OR common_causes LIKE ?
         ORDER BY 
            CASE WHEN code LIKE ? THEN 0 
                 WHEN title LIKE ? THEN 1 
                 ELSE 2 END,
            severity DESC
         LIMIT 20",
        [$search, $search, $search, $search, $search, $search]
    );
    json_response($codes);
    exit;
}

// Filter by category
if (!empty($category) && in_array($category, ['bsod','windows','network','hardware','printer','driver','update','other'])) {
    $codes = Database::fetchAll(
        "SELECT * FROM error_codes WHERE category = ? ORDER BY severity DESC, code LIMIT 50",
        [$category]
    );
    json_response($codes);
    exit;
}

// List all categories with counts
$categories = Database::fetchAll(
    "SELECT category, COUNT(*) as count FROM error_codes GROUP BY category ORDER BY count DESC"
);
json_response(['categories' => $categories]);
exit;
