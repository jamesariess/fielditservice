<?php
/**
 * API: Troubleshooting Decision Tree
 * GET  ?issue=<slug or id>  - get root node for an issue
 * POST { node_id, answer, issue_id, step_history }  - answer yes/no and get next node
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

if ($method === 'GET') {
    $issueParam = $_GET['issue'] ?? '';
    if (empty($issueParam)) { json_response(['error' => 'issue parameter required'], 400); exit; }

    // Resolve issue (admins see all, users only see approved)
    $roleName = $_SESSION['role_name'] ?? '';
    $isAdmin = in_array(strtolower($roleName), ['admin', 'super admin', 'super_admin']);
    $statusFilter = $isAdmin ? '' : " AND (i.status IS NULL OR i.status = 'approved')";
    if (is_numeric($issueParam)) {
        $issue = Database::fetch(
            "SELECT i.*, c.name as category_name, c.icon as category_icon
             FROM troubleshooting_issues i
             JOIN troubleshooting_categories c ON c.id = i.category_id
             WHERE i.id = ?{$statusFilter}", [(int)$issueParam]
        );
    } else {
        $issue = Database::fetch(
            "SELECT i.*, c.name as category_name, c.icon as category_icon
             FROM troubleshooting_issues i
             JOIN troubleshooting_categories c ON c.id = i.category_id
             WHERE i.slug = ?{$statusFilter}", [$issueParam]
        );
    }

    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }

    // Get root node
    $rootNode = Database::fetch(
        "SELECT * FROM decision_nodes WHERE issue_id = ? AND parent_id IS NULL LIMIT 1",
        [$issue['id']]
    );

    if (!$rootNode) { json_response(['error' => 'No decision tree for this issue'], 404); exit; }

    $totalSteps = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$issue['id']]);

    json_response([
        'issue' => $issue,
        'node' => $rootNode,
        'total_steps' => $totalSteps,
        'current_step' => 1,
        'progress_id' => uniqid('sess_'),
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nodeId = (int)($input['node_id'] ?? 0);
    $answer = $input['answer'] ?? '';
    $issueId = (int)($input['issue_id'] ?? 0);
    $stepHistory = $input['step_history'] ?? [];

    if (!$nodeId || !in_array($answer, ['yes', 'no'])) {
        json_response(['error' => 'node_id and answer (yes/no) required'], 400);
        exit;
    }

    // Get current node
    $currentNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nodeId]);
    if (!$currentNode) { json_response(['error' => 'Node not found'], 404); exit; }

    // Use yes_next/no_next for branching
    $nextNodeId = null;
    if ($answer === 'yes' && !empty($currentNode['yes_next'])) {
        $nextNodeId = $currentNode['yes_next'];
    } elseif ($answer === 'no' && !empty($currentNode['no_next'])) {
        $nextNodeId = $currentNode['no_next'];
    }

    // If no next node, treat current as terminal
    if (!$nextNodeId) {
        $response = buildTerminalResponse($currentNode, $stepHistory);
        json_response($response);
        exit;
    }

    // Get next node
    $nextNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nextNodeId]);
    if (!$nextNode) {
        $response = buildTerminalResponse($currentNode, $stepHistory);
        json_response($response);
        exit;
    }

    // If next node is terminal, return terminal response
    if ($nextNode['is_terminal']) {
        $response = buildTerminalResponse($nextNode, $stepHistory);
        json_response($response);
        exit;
    }

    // Non-terminal - return next node
    $totalSteps = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$nextNode['issue_id']]);
    $currentStep = count($stepHistory) + 2;

    json_response([
        'solved' => false,
        'node' => $nextNode,
        'total_steps' => $totalSteps,
        'current_step' => $currentStep,
    ]);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);

/**
 * Build terminal response with summary report
 */
function buildTerminalResponse($node, $stepHistory = []) {
    $resultType = $node['result_type'] ?? 'escalation';
    
    // Build summary from step history
    $summaryLines = [];
    foreach ($stepHistory as $i => $step) {
        $num = $i + 1;
        $question = $step['question'] ?? 'Unknown step';
        $answer = strtoupper($step['answer'] ?? '?');
        $summaryLines[] = "Step {$num}: {$question} → {$answer}";
    }
    $summary = implode("\n", $summaryLines);

    // Build full report
    $report = "TROUBLESHOOTING REPORT\n";
    $report .= str_repeat("=", 40) . "\n";
    $report .= "Issue: " . ($stepHistory[0]['issue_title'] ?? 'Unknown') . "\n";
    $report .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $report .= "Technician: " . (Auth::userName() ?? 'Unknown') . "\n";
    $report .= str_repeat("-", 40) . "\n\n";
    $report .= "STEPS TAKEN:\n";
    $report .= $summary . "\n\n";
    $report .= str_repeat("-", 40) . "\n";
    $report .= "RESULT: " . strtoupper($resultType) . "\n";
    $report .= "SOLUTION: " . ($node['result_solution'] ?? 'No solution recorded') . "\n";
    $report .= str_repeat("=", 40) . "\n";

    return [
        'solved' => $resultType === 'solved',
        'escalated' => $resultType === 'escalation',
        'hardware_replacement' => $resultType === 'hardware',
        'redirect' => $resultType === 'redirect',
        'message' => $node['question'] ?? 'Troubleshooting complete',
        'detail' => $node['description'] ?? '',
        'solution' => $node['result_solution'] ?? '',
        'result_type' => $resultType,
        'steps_taken' => count($stepHistory),
        'summary' => $summary,
        'report' => $report,
    ];
}
