<?php
/**
 * API: Troubleshooting Decision Tree
 * GET  ?issue=<slug or id>  - get root node for an issue
 * POST { node_id, answer }  - answer yes/no and get next node
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

    // Resolve issue - accept slug or numeric id
    if (is_numeric($issueParam)) {
        $issue = Database::fetch(
            "SELECT i.*, c.name as category_name, c.icon as category_icon
             FROM troubleshooting_issues i
             JOIN troubleshooting_categories c ON c.id = i.category_id
             WHERE i.id = ?", [(int)$issueParam]
        );
    } else {
        $issue = Database::fetch(
            "SELECT i.*, c.name as category_name, c.icon as category_icon
             FROM troubleshooting_issues i
             JOIN troubleshooting_categories c ON c.id = i.category_id
             WHERE i.slug = ?", [$issueParam]
        );
    }

    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }

    // Get root node (parent_id IS NULL for this issue)
    $rootNode = Database::fetch(
        "SELECT * FROM decision_nodes WHERE issue_id = ? AND parent_id IS NULL LIMIT 1",
        [$issue['id']]
    );

    if (!$rootNode) { json_response(['error' => 'No decision tree for this issue'], 404); exit; }

    $totalNodes = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$issue['id']]);

    json_response([
        'issue' => $issue,
        'node' => $rootNode,
        'total_steps' => $totalNodes,
        'current_step' => 1,
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nodeId = (int)($input['node_id'] ?? 0);
    $answer = $input['answer'] ?? '';

    if (!$nodeId || !in_array($answer, ['yes', 'no'])) {
        json_response(['error' => 'node_id and answer (yes/no) required'], 400);
        exit;
    }

    // Get current node
    $currentNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nodeId]);
    if (!$currentNode) { json_response(['error' => 'Node not found'], 404); exit; }

    // Find next node based on answer
    // The tree uses parent_id; children are ordered by id.
    // For YES: follow first child node
    // For NO: follow second child node
    $children = Database::fetchAll(
        "SELECT * FROM decision_nodes WHERE issue_id = ? AND parent_id = ? ORDER BY id ASC",
        [$currentNode['issue_id'], $nodeId]
    );

    // Map: yes -> first child, no -> second child
    $nextNode = null;
    if ($answer === 'yes' && isset($children[0])) {
        $nextNode = $children[0];
    } elseif ($answer === 'no' && isset($children[1])) {
        $nextNode = $children[1];
    } elseif ($answer === 'yes' && isset($children[0])) {
        $nextNode = $children[0];
    }

    // If no children or next node not found, check if current node is a terminal
    if (!$nextNode) {
        // Treat as terminal
        $resultType = 'escalation';
        $solution = 'No further steps available. Escalate to supervisor.';

        // Map result_type to response
        json_response([
            'solved' => ($currentNode['result_type'] ?? '') === 'solved',
            'escalated' => ($currentNode['result_type'] ?? '') === 'escalation',
            'hardware_replacement' => ($currentNode['result_type'] ?? '') === 'hardware',
            'redirect' => ($currentNode['result_type'] ?? '') === 'redirect',
            'message' => $currentNode['question'] ?? 'Troubleshooting complete',
            'detail' => $currentNode['description'] ?? '',
            'solution' => $currentNode['result_solution'] ?? $solution,
            'result_type' => $currentNode['result_type'] ?? $resultType,
            'redirect_slug' => $currentNode['result_type'] === 'redirect' ? $currentNode['result_solution'] : null,
        ]);
        exit;
    }

    // If next node is terminal, return terminal response
    if ($nextNode['is_terminal']) {
        json_response([
            'solved' => ($nextNode['result_type'] ?? '') === 'solved',
            'escalated' => ($nextNode['result_type'] ?? '') === 'escalation',
            'hardware_replacement' => ($nextNode['result_type'] ?? '') === 'hardware',
            'redirect' => ($nextNode['result_type'] ?? '') === 'redirect',
            'message' => $nextNode['question'] ?? 'Resolution',
            'detail' => $nextNode['description'] ?? '',
            'solution' => $nextNode['result_solution'] ?? '',
            'result_type' => $nextNode['result_type'] ?? '',
            'redirect_slug' => $nextNode['result_type'] === 'redirect' ? $nextNode['result_solution'] : null,
        ]);
        exit;
    }

    // Non-terminal - return next node
    $totalNodes = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$nextNode['issue_id']]);

    json_response([
        'solved' => false,
        'node' => $nextNode,
        'total_steps' => $totalNodes,
        'current_step' => 0,
    ]);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);
