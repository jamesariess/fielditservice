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
$risk = trim((string)($input['risk_level'] ?? 'safe'));
$titles = array_values(array_filter(array_map(static fn($title) => trim((string)$title), (array)($input['titles'] ?? [$input['title'] ?? '']))));
if ($issueId < 1 || !$titles) { json_response(['error' => 'Select a problem and enter at least one troubleshooting step'], 400); }
if (count($titles) > 50) { json_response(['error' => 'You can add up to 50 steps at once'], 400); }
if (array_filter($titles, static fn($title) => mb_strlen($title) > 200)) { json_response(['error' => 'Each troubleshooting step must be 200 characters or fewer'], 400); }
if (!in_array($risk, ['safe', 'caution', 'danger'], true)) { json_response(['error' => 'Invalid risk level'], 400); }

try {
    if (!Database::fetch('SELECT id FROM troubleshooting_issues WHERE id = ?', [$issueId])) {
        json_response(['error' => 'Selected problem no longer exists'], 404);
    }
    $normalized = array_map(static fn($title) => mb_strtolower($title), $titles);
    if (count($normalized) !== count(array_unique($normalized))) { json_response(['error' => 'Remove duplicate steps from this batch'], 400); }
    foreach ($normalized as $title) {
        if (Database::fetch('SELECT id FROM troubleshooting_steps WHERE issue_id = ? AND LOWER(TRIM(title)) = ?', [$issueId, $title])) {
            json_response(['error' => 'One or more steps already exist for the selected problem'], 409);
        }
    }
    $db = Database::getInstance();
    $db->beginTransaction();
    $max = Database::fetch('SELECT COALESCE(MAX(step_number), 0) AS step_number FROM troubleshooting_steps WHERE issue_id = ?', [$issueId]);
    $nextNumber = (int)($max['step_number'] ?? 0) + 1;
    $ids = [];
    foreach ($titles as $title) {
        $ids[] = Database::insert('troubleshooting_steps', [
            'issue_id' => $issueId,
            'step_number' => $nextNumber++,
            'title' => $title,
            'instruction' => $title,
            'risk_level' => $risk,
            'is_final' => 0,
        ]);
    }
    $db->commit();
    Activity::log('CREATE_BATCH', 'troubleshooting_step', null, ['issue_id' => $issueId, 'step_ids' => $ids, 'risk_level' => $risk]);
    json_response(['success' => true, 'ids' => $ids, 'created' => count($ids)]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('Create troubleshooting step: ' . $e->getMessage());
    json_response(['error' => 'Could not save the troubleshooting step'], 500);
}
