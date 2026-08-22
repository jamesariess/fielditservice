<?php
/**
 * API: Decision Tree Node Management
 * GET  ?issue_id=N       - get all nodes for an issue
 * POST                   - create new node
 * PUT                    - update existing node
 * DELETE ?id=N           - delete a node
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

if (!$isAdmin) {
    json_response(['error' => 'Admin access required'], 403); exit;
}

// GET - Get all nodes for an issue
if ($method === 'GET') {
    $issueId = (int)($_GET['issue_id'] ?? 0);
    if (!$issueId) { json_response(['error' => 'issue_id required'], 400); exit; }
    
    $issue = Database::fetch("SELECT * FROM troubleshooting_issues WHERE id = ?", [$issueId]);
    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }
    
    $nodes = Database::fetchAll(
        "SELECT * FROM decision_nodes WHERE issue_id = ? ORDER BY id ASC",
        [$issueId]
    );
    
    json_response(['issue' => $issue, 'nodes' => $nodes]);
    exit;
}

// POST - Create new node
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $issueId = (int)($input['issue_id'] ?? 0);
    
    if (!$issueId) { json_response(['error' => 'issue_id required'], 400); exit; }
    
    $id = Database::insert('decision_nodes', [
        'issue_id' => $issueId,
        'parent_id' => !empty($input['parent_id']) ? (int)$input['parent_id'] : null,
        'yes_next' => !empty($input['yes_next']) ? (int)$input['yes_next'] : null,
        'no_next' => !empty($input['no_next']) ? (int)$input['no_next'] : null,
        'question' => $input['question'] ?? '',
        'description' => $input['description'] ?? '',
        'risk' => $input['risk'] ?? 'safe',
        'is_terminal' => $input['is_terminal'] ?? 0,
        'result_type' => $input['result_type'] ?? null,
        'result_solution' => $input['result_solution'] ?? null,
    ]);
    
    json_response(['id' => $id, 'message' => 'Node created'], 201);
    exit;
}

// PUT - Update existing node
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nodeId = (int)($input['id'] ?? 0);
    
    if (!$nodeId) { json_response(['error' => 'id required'], 400); exit; }
    
    $existing = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nodeId]);
    if (!$existing) { json_response(['error' => 'Node not found'], 404); exit; }
    
    $data = [];
    if (isset($input['question'])) $data['question'] = $input['question'];
    if (isset($input['description'])) $data['description'] = $input['description'];
    if (isset($input['risk'])) $data['risk'] = $input['risk'];
    if (isset($input['is_terminal'])) $data['is_terminal'] = (int)$input['is_terminal'];
    if (isset($input['result_type'])) $data['result_type'] = $input['result_type'];
    if (isset($input['result_solution'])) $data['result_solution'] = $input['result_solution'];
    if (array_key_exists('parent_id', $input)) $data['parent_id'] = $input['parent_id'] ? (int)$input['parent_id'] : null;
    if (array_key_exists('yes_next', $input)) $data['yes_next'] = $input['yes_next'] ? (int)$input['yes_next'] : null;
    if (array_key_exists('no_next', $input)) $data['no_next'] = $input['no_next'] ? (int)$input['no_next'] : null;
    
    if (empty($data)) { json_response(['error' => 'No fields to update'], 400); exit; }
    
    Database::update('decision_nodes', $data, 'id = ?', [$nodeId]);
    json_response(['message' => 'Node updated']);
    exit;
}

// DELETE - Delete a node
if ($method === 'DELETE') {
    $nodeId = (int)($_GET['id'] ?? 0);
    if (!$nodeId) { json_response(['error' => 'id required'], 400); exit; }
    
    // Check if other nodes reference this one
    $referenced = Database::fetch(
        "SELECT COUNT(*) as cnt FROM decision_nodes WHERE yes_next = ? OR no_next = ? OR parent_id = ?",
        [$nodeId, $nodeId, $nodeId]
    );
    
    if ($referenced && $referenced['cnt'] > 0) {
        json_response(['error' => 'Cannot delete: other nodes reference this node. Update their links first.'], 400);
        exit;
    }
    
    Database::delete('decision_nodes', 'id = ?', [$nodeId]);
    json_response(['message' => 'Node deleted']);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);
