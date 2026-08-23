<?php
/**
 * Two-Phase Troubleshooting API
 * 
 * GET  ?issue=<slug>     → Returns all questions for Phase 1 (checklist)
 * POST { action: 'start', issue_id, answers: {q_id: 'yes'/'no'} }
 *                       → Returns filtered steps for Phase 2
 * POST { action: 'answer_step', node_id, answer: 'worked'/'not_worked', step_history }
 *                       → Returns next step or final result
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

// ===== GET: Return all questions for an issue =====
if ($method === 'GET') {
    $issueParam = $_GET['issue'] ?? '';
    if (empty($issueParam)) { json_response(['error' => 'issue parameter required'], 400); exit; }

    // Resolve issue
    $roleName = $_SESSION['role_name'] ?? '';
    $isAdmin = in_array(strtolower($roleName), ['admin', 'super admin', 'super_admin']);
    $statusFilter = $isAdmin ? '' : " AND (i.status IS NULL OR i.status = 'approved')";
    
    if (is_numeric($issueParam)) {
        $issue = Database::fetch("SELECT i.* FROM troubleshooting_issues i WHERE i.id = ?{$statusFilter}", [(int)$issueParam]);
    } else {
        $issue = Database::fetch("SELECT i.* FROM troubleshooting_issues i WHERE i.slug = ?{$statusFilter}", [$issueParam]);
    }
    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }

    // Get device filter
    $device = $_GET['device'] ?? 'all';
    $deviceFilter = ($device && $device !== 'all') ? " AND (device_type = 'all' OR device_type = '" . mysqli_real_escape_string(Database::$link, $device) . "')" : '';
    
    // Get all questions (ordered by step_order) - filtered by device
    $questions = Database::fetchAll(
        "SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'question'{$deviceFilter} ORDER BY step_order ASC",
        [$issue['id']]
    );

    // Get all steps (for counting) - filtered by device
    $totalSteps = Database::count('decision_nodes', 'issue_id = ? AND node_type = "step"' . $deviceFilter, [$issue['id']]);

    // Create session
    $sessionId = Database::insert('troubleshooting_sessions', [
        'issue_id' => $issue['id'],
        'user_id' => Auth::userId(),
        'device_type' => $_GET['device'] ?? null,
    ]);

    json_response([
        'issue' => $issue,
        'questions' => $questions,
        'total_questions' => count($questions),
        'total_steps' => $totalSteps,
        'session_id' => $sessionId,
    ]);
    exit;
}

// ===== POST: Handle actions =====
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) { $input = []; }
    $action = $input['action'] ?? '';
    $issueId = (int)($input['issue_id'] ?? 0);
    $sessionId = (int)($input['session_id'] ?? 0);

    // ----- ACTION: start (get filtered steps based on question answers) -----
    if ($action === 'start') {
        $answers = $input['answers'] ?? []; // {q_id: 'yes'/'no'}
        
        if (!$issueId || empty($answers)) {
            json_response(['error' => 'issue_id and answers required'], 400); exit;
        }

        // Get device filter
        $device = $input['device'] ?? ($_GET['device'] ?? 'all');
        $devFilter = ($device && $device !== 'all') ? " AND (device_type = 'all' OR device_type = '" . mysqli_real_escape_string(Database::$link, $device) . "')" : '';
        
        // Get all questions to understand the flow - filtered by device
        $questions = Database::fetchAll(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'question'{$devFilter} ORDER BY step_order ASC",
            [$issueId]
        );

        // Get all steps for this issue - filtered by device
        $allSteps = Database::fetchAll(
            "SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'step'{$devFilter} ORDER BY step_order ASC",
            [$issueId]
        );
        
        // Filter steps based on visibility settings
        $relevantSteps = [];
        foreach ($allSteps as $step) {
            $visMode = $step['visibility_mode'] ?? 'always';
            $visQId = $step['visible_for_question_id'] ?? null;
            
            if ($visMode === 'always' || $visQId === null) {
                // Always visible — include it
                $relevantSteps[] = $step;
            } elseif ($visMode === 'both') {
                // Visible for both YES and NO — include it
                $relevantSteps[] = $step;
            } elseif ($visMode === 'yes_only' && $visQId) {
                // Only visible when the linked question was answered YES
                $answer = $answers[$visQId] ?? ($answers[strval($visQId)] ?? null);
                if ($answer === 'yes') $relevantSteps[] = $step;
            } elseif ($visMode === 'no_only' && $visQId) {
                // Only visible when the linked question was answered NO
                $answer = $answers[$visQId] ?? ($answers[strval($visQId)] ?? null);
                if ($answer === 'no' || $answer === null) $relevantSteps[] = $step;
            } elseif ($visMode === 'yes_only' && !$visQId) {
                // No question linked but yes_only — skip
                continue;
            } elseif ($visMode === 'no_only' && !$visQId) {
                // No question linked but no_only — skip
                continue;
            } else {
                // Default: include it
                $relevantSteps[] = $step;
            }
        }
        
        $steps = $relevantSteps;
        
        // Update session with question answers
        if ($sessionId) {
            try {
                $qYes = 0; $qNo = 0;
                foreach ($answers as $a) {
                    if ($a === 'yes') $qYes++;
                    else $qNo++;
                }
                Database::update('troubleshooting_sessions', [
                    'total_questions' => count($answers),
                    'questions_yes' => $qYes,
                    'questions_no' => $qNo,
                    'total_steps' => count($steps),
                ], 'id = ?', [$sessionId]);
            } catch (Exception $e) {}
        }

        json_response([
            'phase' => 'steps',
            'steps' => $steps,
            'total_steps' => count($steps),
            'session_id' => $sessionId,
            'question_answers' => $answers,
        ]);
        exit;
    }

    // ----- ACTION: answer_step (technician reports worked/not_worked) -----
    if ($action === 'answer_step') {
        $nodeId = (int)($input['node_id'] ?? 0);
        $answer = $input['answer'] ?? '';
        $stepHistory = $input['step_history'] ?? [];
        $timeSpent = (int)($input['time_spent'] ?? 0);
        
        if (!$nodeId || !in_array($answer, ['worked', 'not_worked'])) {
            json_response(['error' => 'node_id and answer (worked/not_worked) required'], 400); exit;
        }
        
        $node = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$nodeId]);
        if (!$node) { json_response(['error' => 'Node not found'], 404); exit; }
        
        // Record this step completion
        if ($sessionId) {
            try {
                Database::insert('session_steps', [
                    'session_id' => $sessionId,
                    'node_id' => $nodeId,
                    'step_order' => $node['step_order'] ?? 0,
                    'answer' => $answer,
                    'time_spent_seconds' => $timeSpent,
                ]);
            } catch (Exception $e) {}
        }
        
        // If worked and terminal → SOLVED
        if ($answer === 'worked' && $node['is_terminal']) {
            try { if ($sessionId) updateSession($sessionId, $stepHistory, $answer, $node); } catch (Exception $e) {}
            json_response(buildResult($node, $stepHistory, $sessionId));
            exit;
        }
        
        // If worked and has yes_next → go to next step
        if ($answer === 'worked' && $node['yes_next']) {
            $nextNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$node['yes_next']]);
            if ($nextNode && $nextNode['node_type'] === 'step') {
                json_response([
                    'phase' => 'step',
                    'node' => $nextNode,
                    'previous_worked' => true,
                    'previous_step' => $node['question'],
                ]);
                exit;
            }
        }
        
        // If not worked and has no_next → go to next step
        if ($answer === 'not_worked' && $node['no_next']) {
            $nextNode = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$node['no_next']]);
            if ($nextNode && $nextNode['node_type'] === 'step') {
                json_response([
                    'phase' => 'step',
                    'node' => $nextNode,
                    'previous_worked' => false,
                    'previous_step' => $node['question'],
                ]);
                exit;
            }
        }
        
        // Terminal or no more steps → RESULT
        if ($node['is_terminal'] || (!$node['yes_next'] && !$node['no_next'])) {
            try { if ($sessionId) updateSession($sessionId, $stepHistory, $answer, $node); } catch (Exception $e) {}
            json_response(buildResult($node, $stepHistory, $sessionId));
            exit;
        }
        
        // Fallback → RESULT
        try { if ($sessionId) updateSession($sessionId, $stepHistory, $answer, $node); } catch (Exception $e) {}
        json_response(buildResult($node, $stepHistory, $sessionId));
        exit;
    }

    json_response(['error' => 'Unknown action'], 400);
    exit;
}

json_response(['error' => 'Method not allowed'], 405);

// ===== HELPERS =====

function collectStepChain($startId, $issueId) {
    $ids = [];
    $queue = [$startId];
    $visited = [];
    
    while (!empty($queue)) {
        $id = array_shift($queue);
        if (!$id || in_array($id, $visited)) continue;
        $visited[] = $id;
        
        $node = Database::fetch("SELECT * FROM decision_nodes WHERE id = ?", [$id]);
        if (!$node) continue;
        
        if ($node['node_type'] === 'step') {
            $ids[] = $id;
            // Follow the chain
            if ($node['yes_next'] && !in_array($node['yes_next'], $visited)) {
                $queue[] = $node['yes_next'];
            }
            if ($node['no_next'] && !in_array($node['no_next'], $visited)) {
                $queue[] = $node['no_next'];
            }
        }
    }
    
    return $ids;
}

function buildResult($node, $stepHistory, $sessionId) {
    $resultType = $node['result_type'] ?? 'escalation';
    
    // Build report
    $report = "TROUBLESHOOTING REPORT\n";
    $report .= str_repeat("=", 50) . "\n";
    $report .= "Issue: " . ($stepHistory[0]['issue_title'] ?? 'Unknown') . "\n";
    $report .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $report .= "Technician: " . (Auth::userName() ?? 'Unknown') . "\n";
    $report .= str_repeat("-", 50) . "\n\n";
    
    // Diagnostic answers
    $report .= "DIAGNOSTIC ANSWERS:\n";
    foreach ($stepHistory as $s) {
        if (($s['type'] ?? '') === 'question') {
            $report .= "  Q: " . ($s['question'] ?? '') . "\n";
            $report .= "  A: " . strtoupper($s['answer'] ?? '?') . "\n\n";
        }
    }
    
    // Steps performed
    $report .= "STEPS PERFORMED:\n";
    $stepsDone = array_filter($stepHistory, function($s) { return ($s['type'] ?? '') === 'step'; });
    foreach ($stepsDone as $s) {
        $status = ($s['answer'] ?? '') === 'worked' ? '✅ WORKED' : '❌ DID NOT WORK';
        $report .= "  " . ($s['question'] ?? '') . "\n";
        $report .= "  Result: " . $status . "\n\n";
    }
    
    $report .= str_repeat("-", 50) . "\n";
    $report .= "RESULT: " . strtoupper($resultType) . "\n";
    $report .= "SOLUTION: " . ($node['result_solution'] ?? $node['question'] ?? 'No solution') . "\n";
    $report .= str_repeat("=", 50) . "\n";

    if ($sessionId) {
        try { updateSession($sessionId, $stepHistory, null, $node); } catch (Exception $e) {}
    }

    return [
        'phase' => 'result',
        'solved' => $resultType === 'solved',
        'escalated' => $resultType === 'escalation',
        'hardware_replacement' => $resultType === 'hardware',
        'result_type' => $resultType,
        'message' => $node['question'] ?? 'Complete',
        'solution' => $node['result_solution'] ?? '',
        'detail' => $node['description'] ?? '',
        'report' => $report,
        'steps_completed' => count($stepsDone),
        'session_id' => $sessionId,
    ];
}

function updateSession($sessionId, $stepHistory, $lastAnswer, $lastNode) {
    $steps = array_filter($stepHistory, function($s) { return ($s['type'] ?? '') === 'step'; });
    $sWorked = count(array_filter($steps, function($s) { return ($s['answer'] ?? '') === 'worked'; }));
    $sFailed = count(array_filter($steps, function($s) { return ($s['answer'] ?? '') === 'not_worked'; }));
    
    Database::update('troubleshooting_sessions', [
        'steps_completed' => count($steps) + ($lastAnswer ? 1 : 0),
        'steps_worked' => $sWorked + ($lastAnswer === 'worked' ? 1 : 0),
        'steps_failed' => $sFailed + ($lastAnswer === 'not_worked' ? 1 : 0),
        'status' => ($lastNode['result_type'] ?? '') === 'solved' ? 'completed' : 'escalated',
        'completed_at' => date('Y-m-d H:i:s'),
        'result_type' => $lastNode['result_type'] ?? null,
        'result_solution' => $lastNode['result_solution'] ?? null,
    ], 'id = ?', [$sessionId]);
}
