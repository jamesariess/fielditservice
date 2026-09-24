<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Activity.php';

Auth::start();
Auth::requireLogin();
$role = strtolower((string)($_SESSION['role_name'] ?? ''));
if (!Auth::hasPermission('system.settings') && !in_array($role, ['admin', 'super admin', 'super_admin', 'manager'], true)) {
    json_response(['error' => 'Permission denied'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$issueId = (int)($input['issue_id'] ?? 0);
$title = trim((string)($input['title'] ?? ''));
$risk = trim((string)($input['risk_level'] ?? 'safe'));
if ($issueId < 1 || $title === '') { json_response(['error' => 'Select a problem and enter a troubleshooting step'], 400); }
if (mb_strlen($title) > 200) { json_response(['error' => 'A troubleshooting step must be 200 characters or fewer'], 400); }
if (!in_array($risk, ['safe', 'caution', 'danger'], true)) { json_response(['error' => 'Invalid risk level'], 400); }

try {
    if (!Database::fetch('SELECT id FROM troubleshooting_issues WHERE id = ?', [$issueId])) {
        json_response(['error' => 'Selected problem no longer exists'], 404);
    }
    $existing = Database::fetch('SELECT id FROM troubleshooting_steps WHERE issue_id = ? AND LOWER(TRIM(title)) = ?', [$issueId, mb_strtolower($title)]);
    if ($existing) { json_response(['error' => 'This step already exists for the selected problem'], 409); }
    $max = Database::fetch('SELECT COALESCE(MAX(step_number), 0) AS step_number FROM troubleshooting_steps WHERE issue_id = ?', [$issueId]);
    $id = Database::insert('troubleshooting_steps', [
        'issue_id' => $issueId,
        'step_number' => (int)($max['step_number'] ?? 0) + 1,
        'title' => $title,
        'instruction' => $title,
        'risk_level' => $risk,
        'is_final' => 0,
    ]);
    Activity::log('CREATE', 'troubleshooting_step', (int)$id, ['issue_id' => $issueId, 'title' => $title, 'risk_level' => $risk]);
    json_response(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    error_log('Create troubleshooting step: ' . $e->getMessage());
    json_response(['error' => 'Could not save the troubleshooting step'], 500);
}
