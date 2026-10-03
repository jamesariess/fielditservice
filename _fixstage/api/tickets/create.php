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
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? $input['problem_description'] ?? '');
$category = trim($input['category'] ?? '');
$priority = $input['priority'] ?? 'medium';
$department = trim($input['department'] ?? '');
$location = trim($input['location'] ?? '');
$device = trim($input['device'] ?? '');
$issueId = (int)($input['issue_id'] ?? 0);

if (!$title) { json_response(['error' => 'title required'], 400); exit; }

try {
    // Try to match category to issue
    if (!$issueId && $category) {
        $cat = Database::fetch("SELECT id FROM troubleshooting_categories WHERE name = ?", [$category]);
        if ($cat) {
            $issue = Database::fetch("SELECT id FROM troubleshooting_issues WHERE category_id = ? LIMIT 1", [$cat['id']]);
            $issueId = $issue['id'] ?? 0;
        }
    }

    // Generate ticket number
    $lastTicket = Database::fetch("SELECT ticket_number FROM troubleshooting_sessions ORDER BY id DESC LIMIT 1");
    $nextNum = 1006;
    if ($lastTicket && preg_match('/TK-(\d+)/', $lastTicket['ticket_number'], $m)) {
        $nextNum = (int)$m[1] + 1;
    }
    $ticketNum = 'TK-' . $nextNum;

    $sessionId = Database::insert('troubleshooting_sessions', [
        'ticket_number' => $ticketNum,
        'user_id' => Auth::userId(),
        'issue_id' => $issueId ?: null,
        'customer_name' => Auth::userName() ?? 'User',
        'department' => $department,
        'location' => $location,
        'device_type' => $device,
        'problem_description' => $title . ($description ? '\n' . $description : ''),
        'priority' => $priority,
        'status' => 'new',
    ]);

    json_response([
        'success' => true,
        'ticket_id' => $sessionId,
        'ticket_number' => $ticketNum,
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to create ticket: ' . $e->getMessage()], 500);
}
