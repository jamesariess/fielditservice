<?php
/**
 * API: Troubleshooting Decision Tree
 * GET ?issue=1 - get first node
 * POST - answer question and get next node
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/DemoData.php'; }
Auth::start();
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $issueId = (int)($_GET['issue'] ?? 0);
    if (!$issueId) { json_response(['error' => 'issue parameter required'], 400); exit; }

    // Get issue info
    if (defined('DEMO_MODE') && DEMO_MODE) {
        $issues = DemoData::issues();
        $issue = null;
        foreach ($issues as $i) { if ($i['id'] == $issueId) { $issue = $i; break; } }
    } else {
        $issue = Database::fetch(
            "SELECT i.*, c.name as category_name, c.icon as category_icon
             FROM troubleshooting_issues i
             JOIN troubleshooting_categories c ON c.id = i.category_id
             WHERE i.id = ?", [$issueId]
        );
        if ($issue) {
            $issue['symptoms'] = json_decode($issue['symptoms'] ?? '[]', true);
        }
    }

    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }

    // Get first node
    if (defined('DEMO_MODE') && DEMO_MODE) {
        $tree = DemoData::decisionTree($issueId);
        $firstNode = null;
        $totalNodes = 0;
        if ($tree) {
            foreach ($tree as $node) {
                if (($node['node'] ?? 0) == 1 && empty($node['is_terminal'])) {
                    $firstNode = $node;
                }
                if (empty($node['is_terminal'])) $totalNodes++;
            }
        }
        $progressId = 'demo_' . time() . '_' . $issueId;
    } else {
        $firstNode = Database::fetch(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND node_number = 1 AND is_terminal = 0 ORDER BY id LIMIT 1",
            [$issueId]
        );
        $totalNodes = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$issueId]);
        $progressId = 'db_' . time() . '_' . $issueId;

        // Store progress
        Database::insert('decision_progress', [
            'user_id' => Auth::userId(),
            'issue_id' => $issueId,
            'current_node_id' => $firstNode['id'] ?? null,
            'answers' => json_encode([]),
            'status' => 'in_progress',
        ]);
    }

    if (!$firstNode) { json_response(['error' => 'No decision tree found'], 404); exit; }

    json_response([
        'issue' => $issue,
        'node' => $firstNode,
        'progress_id' => $progressId,
        'total_steps' => $totalNodes,
        'current_step' => 1,
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $progressId = $input['progress_id'] ?? null;
    $answer = $input['answer'] ?? null;
    $nodeId = (int)($input['node_id'] ?? 0);
    $issueId = (int)($input['issue_id'] ?? 0);

    if (!$progressId || !$answer || !$nodeId) {
        json_response(['error' => 'progress_id, answer, and node_id required'], 400);
        exit;
    }

    if (defined('DEMO_MODE') && DEMO_MODE) {
        // Demo mode - use DemoData
        $tree = DemoData::decisionTree($issueId);
        if (!$tree) { json_response(['error' => 'Tree not found'], 404); exit; }

        $currentNode = null;
        foreach ($tree as $node) { if ($node['id'] == $nodeId) { $currentNode = $node; break; } }
        if (!$currentNode) { json_response(['error' => 'Node not found'], 404); exit; }

        $nextNodeKey = $answer === 'yes' ? ($currentNode['yes_next'] ?? null) : ($currentNode['no_next'] ?? null);

        // Handle terminal nodes
        if (is_string($nextNodeKey)) {
            $nextNode = null;
            foreach ($tree as $node) {
                if (($node['node'] ?? '') === $nextNodeKey && !empty($node['is_terminal'])) {
                    $nextNode = $node; break;
                }
            }
            if ($nextNode) {
                json_response([
                    'solved' => ($nextNode['result'] ?? '') === 'solved',
                    'escalated' => ($nextNode['result'] ?? '') === 'escalated',
                    'hardware_replacement' => ($nextNode['result'] ?? '') === 'hardware',
                    'redirect' => ($nextNode['result'] ?? '') === 'redirect',
                    'message' => $nextNode['message'] ?? 'Done',
                    'detail' => $nextNode['detail'] ?? '',
                    'solution' => $nextNode['solution'] ?? '',
                    'steps_taken' => 1,
                ]);
                exit;
            }
        }

        // Handle numeric next node
        if (is_int($nextNodeKey)) {
            $nextNode = null;
            foreach ($tree as $node) { if (($node['node'] ?? 0) == $nextNodeKey && empty($node['is_terminal'])) { $nextNode = $node; break; } }
            if ($nextNode) {
                $total = 0;
                foreach ($tree as $n) { if (empty($n['is_terminal'])) $total++; }
                json_response([
                    'solved' => false,
                    'node' => $nextNode,
                    'progress_id' => $progressId,
                    'current_step' => 2,
                    'total_steps' => $total,
                ]);
                exit;
            }
        }

        json_response(['escalated' => true, 'message' => 'All paths explored. Escalation recommended.']);
        exit;
    }

    // Database mode
    $currentNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nodeId]);
    if (!$currentNode) { json_response(['error' => 'Node not found'], 404); exit; }

    // Get next node based on answer
    $nextNodeNumber = $answer === 'yes' ? $currentNode['yes_next_node'] : null;
    $nextNodeKey = $answer === 'no' ? $currentNode['no_next_node'] : null;

    // If yes_next_node is a number, find that node
    if ($nextNodeNumber && is_numeric($nextNodeNumber)) {
        $nextNode = Database::fetch(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND node_number = ? AND is_terminal = 0",
            [$issueId, (int)$nextNodeNumber]
        );
        if ($nextNode) {
            $totalSteps = Database::count('decision_nodes', 'issue_id = ? AND is_terminal = 0', [$issueId]);
            json_response([
                'solved' => false,
                'node' => $nextNode,
                'progress_id' => $progressId,
                'current_step' => (int)$nextNodeNumber,
                'total_steps' => $totalSteps,
            ]);
            exit;
        }
    }

    // If no_next_node is a terminal key, find that terminal node
    if ($nextNodeKey) {
        $terminalNode = Database::fetch(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND is_terminal = 1 AND terminal_result IS NOT NULL ORDER BY id",
            [$issueId]
        );
        // Try to find a matching terminal node
        $allTerminals = Database::fetchAll(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND is_terminal = 1",
            [$issueId]
        );
        foreach ($allTerminals as $t) {
            if (strpos($t['terminal_message'] ?? '', 'Power') !== false && strpos($nextNodeKey, 'power') !== false) {
                $terminalNode = $t; break;
            }
            if (strpos($t['terminal_message'] ?? '', 'solved') !== false && $nextNodeKey === 'solved_' . $currentNode['node_number']) {
                $terminalNode = $t; break;
            }
        }
        if ($terminalNode) {
            json_response([
                'solved' => ($terminalNode['terminal_result'] ?? '') === 'solved',
                'escalated' => ($terminalNode['terminal_result'] ?? '') === 'escalated',
                'hardware_replacement' => ($terminalNode['terminal_result'] ?? '') === 'hardware',
                'redirect' => ($terminalNode['terminal_result'] ?? '') === 'redirect',
                'message' => $terminalNode['terminal_message'] ?? 'Done',
                'detail' => $terminalNode['terminal_detail'] ?? '',
                'solution' => $terminalNode['terminal_solution'] ?? '',
                'steps_taken' => 1,
            ]);
            exit;
        }
    }

    // No next node - escalate
    json_response([
        'escalated' => true,
        'message' => 'All standard troubleshooting steps completed. Escalation recommended.',
    ]);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);
