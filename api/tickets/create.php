<?php
/**
 * API: Create Ticket
 * POST /api/tickets/create.php
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$issueId = (int)($input['issue_id'] ?? 0);
$device = trim($input['device'] ?? '');
$department = trim($input['department'] ?? '');
$location = trim($input['location'] ?? '');
$priority = $input['priority'] ?? 'medium';
$symptoms = trim($input['symptoms'] ?? '');

if (!$issueId) { json_response(['error' => 'issue_id required'], 400); exit; }

try {
    // Get issue info
    $issue = Database::fetch("SELECT id, title FROM troubleshooting_issues WHERE id = ?", [$issueId]);
    if (!$issue) { json_response(['error' => 'Issue not found'], 404); exit; }

    // Generate ticket number
    $lastTicket = Database::fetch("SELECT id FROM troubleshooting_sessions ORDER BY id DESC LIMIT 1");
    $ticketNum = 'TK-' . (1001 + ($lastTicket['id'] ?? 0));

    $sessionId = Database::insert('troubleshooting_sessions', [
        'ticket_number' => $ticketNum,
        'user_id' => Auth::userId(),
        'issue_id' => $issueId,
        'title' => $issue['title'] . ($device ? " — {$device}" : ''),
        'device' => $device,
        'department' => $department,
        'location' => $location,
        'priority' => $priority,
        'status' => 'in_progress',
    ]);

    json_response([
        'success' => true,
        'ticket_id' => $sessionId,
        'ticket_number' => $ticketNum,
        'redirect' => '/troubleshoot/wizard?issue=' . $issueId,
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to create ticket: ' . $e->getMessage()], 500);
}
