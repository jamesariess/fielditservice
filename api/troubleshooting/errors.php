<?php
/**
 * API: Error Codes Management
 * GET  ?q=search_term   - search error codes
 * GET  ?id=N            - get single error code
 * GET  ?category=bsod  - filter by category
 * GET  ?all=1           - list all (admin)
 * POST                  - create new error code (admin)
 * PUT                   - update error code (admin)
 * DELETE ?id=N          - delete error code (admin)
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$roleName = $_SESSION['role_name'] ?? '';
$isAdmin = in_array(strtolower($roleName), ['admin', 'super admin', 'super_admin']);

// GET - List all (admin)
if ($method === 'GET' && !empty($_GET['all']) && $isAdmin) {
    $codes = Database::fetchAll("SELECT * FROM error_codes ORDER BY category, code");
    json_response($codes);
    exit;
}

// GET - Single error code
if ($method === 'GET' && !empty($_GET['id'])) {
    $code = Database::fetch("SELECT * FROM error_codes WHERE id = ?", [(int)$_GET['id']]);
    if (!$code) { json_response(['error' => 'Not found'], 404); exit; }
    json_response($code);
    exit;
}

// GET - Search
if ($method === 'GET' && !empty($_GET['q'])) {
    $q = trim($_GET['q']);
    $search = "%{$q}%";
    $codes = Database::fetchAll(
        "SELECT * FROM error_codes 
         WHERE code LIKE ? OR title LIKE ? OR description LIKE ? OR common_causes LIKE ?
         ORDER BY CASE WHEN code LIKE ? THEN 0 WHEN title LIKE ? THEN 1 ELSE 2 END, severity DESC
         LIMIT 20",
        [$search, $search, $search, $search, $search, $search]
    );
    json_response($codes);
    exit;
}

// GET - Filter by category
if ($method === 'GET' && !empty($_GET['category'])) {
    $cat = $_GET['category'];
    if (in_array($cat, ['bsod','windows','network','hardware','printer','driver','update','other'])) {
        $codes = Database::fetchAll("SELECT * FROM error_codes WHERE category = ? ORDER BY severity DESC, code LIMIT 50", [$cat]);
        json_response($codes);
        exit;
    }
}

// GET - Categories with counts
if ($method === 'GET') {
    $categories = Database::fetchAll("SELECT category, COUNT(*) as count FROM error_codes GROUP BY category ORDER BY count DESC");
    json_response(['categories' => $categories]);
    exit;
}

// POST - Create new error code (admin)
if ($method === 'POST') {
    if (!$isAdmin) { json_response(['error' => 'Admin access required'], 403); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $code = trim($input['code'] ?? '');
    $title = trim($input['title'] ?? '');
    
    if (empty($code) || empty($title)) {
        json_response(['error' => 'Code and title are required'], 400); exit;
    }
    
    // Check for duplicate
    $existing = Database::fetch("SELECT id FROM error_codes WHERE code = ?", [$code]);
    if ($existing) {
        json_response(['error' => 'Error code "' . $code . '" already exists'], 400); exit;
    }
    
    $id = Database::insert('error_codes', [
        'code' => $code,
        'title' => $title,
        'category' => $input['category'] ?? 'other',
        'description' => $input['description'] ?? '',
        'common_causes' => $input['common_causes'] ?? '',
        'fix_steps' => $input['fix_steps'] ?? '',
        'severity' => $input['severity'] ?? 'medium',
    ]);
    
    json_response(['id' => $id, 'message' => 'Error code created'], 201);
    exit;
}

// PUT - Update error code (admin)
if ($method === 'PUT') {
    if (!$isAdmin) { json_response(['error' => 'Admin access required'], 403); exit; }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) { json_response(['error' => 'id required'], 400); exit; }
    
    $existing = Database::fetch("SELECT * FROM error_codes WHERE id = ?", [$id]);
    if (!$existing) { json_response(['error' => 'Not found'], 404); exit; }
    
    $data = [];
    if (isset($input['code'])) $data['code'] = $input['code'];
    if (isset($input['title'])) $data['title'] = $input['title'];
    if (isset($input['category'])) $data['category'] = $input['category'];
    if (isset($input['description'])) $data['description'] = $input['description'];
    if (isset($input['common_causes'])) $data['common_causes'] = $input['common_causes'];
    if (isset($input['fix_steps'])) $data['fix_steps'] = $input['fix_steps'];
    if (isset($input['severity'])) $data['severity'] = $input['severity'];
    
    if (empty($data)) { json_response(['error' => 'No fields to update'], 400); exit; }
    
    Database::update('error_codes', $data, 'id = ?', [$id]);
    json_response(['message' => 'Error code updated']);
    exit;
}

// DELETE - Delete error code (admin)
if ($method === 'DELETE') {
    if (!$isAdmin) { json_response(['error' => 'Admin access required'], 403); exit; }
    
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { json_response(['error' => 'id required'], 400); exit; }
    
    Database::delete('error_codes', 'id = ?', [$id]);
    json_response(['message' => 'Error code deleted']);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);
