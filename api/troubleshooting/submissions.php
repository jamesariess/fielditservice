<?php
/**
 * API: Troubleshooting Submissions
 * GET  ?mine=1          - list my submissions
 * GET  ?all=1           - list all submissions (admin)
 * GET  ?id=N            - get single submission detail
 * POST { title, submission_type, ... } - create new submission
 * POST { id, action: approve/reject }  - approve/reject (admin)
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
$userId = Auth::userId();
$roleName = $_SESSION['role_name'] ?? '';
$isAdmin = in_array(strtolower($roleName), ['admin', 'super admin', 'super_admin']);

// GET - List or get submissions
if ($method === 'GET') {
    // Get single submission
    if (!empty($_GET['id'])) {
        $sub = Database::fetch(
            "SELECT s.*, u.full_name as submitter_name, u.email as submitter_email,
                    a.full_name as approver_name
             FROM troubleshooting_submissions s
             JOIN users u ON s.submitted_by = u.id
             LEFT JOIN users a ON s.approved_by = a.id
             WHERE s.id = ?",
            [(int)$_GET['id']]
        );
        if (!$sub) { json_response(['error' => 'Not found'], 404); exit; }
        if (!$isAdmin && $sub['submitted_by'] != $userId) {
            json_response(['error' => 'Forbidden'], 403); exit;
        }
        json_response($sub);
        exit;
    }
    
    // List all submissions (admin only)
    if (!empty($_GET['all']) && $isAdmin) {
        $status = $_GET['status'] ?? '';
        $sql = "SELECT s.*, u.full_name as submitter_name
                FROM troubleshooting_submissions s
                JOIN users u ON s.submitted_by = u.id";
        $params = [];
        if ($status && in_array($status, ['pending','approved','rejected'])) {
            $sql .= " WHERE s.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY s.created_at DESC LIMIT 50";
        json_response(Database::fetchAll($sql, $params));
        exit;
    }
    
    // List my submissions
    $sql = "SELECT s.*, u.full_name as submitter_name
            FROM troubleshooting_submissions s
            JOIN users u ON s.submitted_by = u.id
            WHERE s.submitted_by = ?
            ORDER BY s.created_at DESC LIMIT 50";
    json_response(Database::fetchAll($sql, [$userId]));
    exit;
}

// POST
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // ---- APPROVE / REJECT (admin) ----
    if (!empty($input['action']) && in_array($input['action'], ['approve', 'reject'])) {
        if (!$isAdmin) {
            json_response(['error' => 'Admin access required.'], 403); exit;
        }
        
        $subId = (int)($input['id'] ?? 0);
        $action = $input['action'];
        $notes = $input['admin_notes'] ?? '';
        
        if (!$subId) { json_response(['error' => 'id required'], 400); exit; }
        
        $sub = Database::fetch("SELECT * FROM troubleshooting_submissions WHERE id = ?", [$subId]);
        if (!$sub) { json_response(['error' => 'Not found'], 404); exit; }
        
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        
        // Update submission status
        Database::update('troubleshooting_submissions', [
            'status' => $newStatus,
            'admin_notes' => $notes,
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$subId]);
        
        // If approved, apply the changes
        if ($action === 'approve' && $sub['submission_type'] === 'new_issue') {
            $issueId = Database::insert('troubleshooting_issues', [
                'title' => $sub['title'],
                'slug' => $sub['slug'],
                'description' => $sub['description'],
                'severity' => $sub['severity'],
                'category_id' => $sub['category_id'],
                'status' => 'approved',
                'submitted_by' => $sub['submitted_by'],
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);
            
            if ($sub['nodes_data']) {
                $nodes = json_decode($sub['nodes_data'], true);
                if (is_array($nodes)) {
                    foreach ($nodes as $node) {
                        Database::insert('decision_nodes', [
                            'issue_id' => $issueId,
                            'parent_id' => $node['parent_id'] ?? null,
                            'yes_next' => $node['yes_next'] ?? null,
                            'no_next' => $node['no_next'] ?? null,
                            'question' => $node['question'] ?? '',
                            'description' => $node['description'] ?? '',
                            'risk' => $node['risk'] ?? 'safe',
                            'is_terminal' => $node['is_terminal'] ?? 0,
                            'result_type' => $node['result_type'] ?? null,
                            'result_solution' => $node['result_solution'] ?? null,
                        ]);
                    }
                }
            }
            
            Database::update('troubleshooting_submissions', ['issue_id' => $issueId], 'id = ?', [$subId]);
        }
        
        if ($action === 'approve' && $sub['submission_type'] === 'new_steps' && !empty($sub['issue_id'])) {
            if ($sub['nodes_data']) {
                $nodes = json_decode($sub['nodes_data'], true);
                if (is_array($nodes)) {
                    foreach ($nodes as $node) {
                        Database::insert('decision_nodes', [
                            'issue_id' => $sub['issue_id'],
                            'parent_id' => $node['parent_id'] ?? null,
                            'yes_next' => $node['yes_next'] ?? null,
                            'no_next' => $node['no_next'] ?? null,
                            'question' => $node['question'] ?? '',
                            'description' => $node['description'] ?? '',
                            'risk' => $node['risk'] ?? 'safe',
                            'is_terminal' => $node['is_terminal'] ?? 0,
                            'result_type' => $node['result_type'] ?? null,
                            'result_solution' => $node['result_solution'] ?? null,
                        ]);
                    }
                }
            }
        }
        
        json_response(['message' => "Submission {$newStatus}", 'status' => $newStatus]);
        exit;
    }
    
    // ---- CREATE NEW SUBMISSION ----
    $title = trim($input['title'] ?? '');
    $type = $input['submission_type'] ?? 'new_steps';
    $description = $input['description'] ?? '';
    $deviceType = $input['device_type'] ?? '';
    $severity = $input['severity'] ?? 'medium';
    $categoryId = (int)($input['category_id'] ?? 0);
    $issueId = !empty($input['issue_id']) ? (int)$input['issue_id'] : null;
    $nodesData = $input['nodes_data'] ?? null;
    
    if (empty($title)) {
        json_response(['error' => 'Title is required'], 400); exit;
    }
    
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    $id = Database::insert('troubleshooting_submissions', [
        'issue_id' => $issueId,
        'submitted_by' => $userId,
        'submission_type' => $type,
        'title' => $title,
        'slug' => $slug,
        'description' => $description,
        'category_id' => $categoryId ?: null,
        'severity' => $severity,
        'device_type' => $deviceType,
        'nodes_data' => $nodesData ? json_encode($nodesData) : null,
    ]);
    
    json_response(['id' => $id, 'status' => 'pending', 'message' => 'Submission created. Waiting for admin approval.'], 201);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);
