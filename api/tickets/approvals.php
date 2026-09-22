<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
    require_once APP_ROOT . '/includes/TicketFieldMemory.php';
    require_once APP_ROOT . '/includes/TicketStepSuggestions.php';
}
require_once APP_ROOT . '/includes/Auth.php';

Auth::start();
Auth::requireLogin();
header('Content-Type: application/json');

$roleName = strtolower((string)($_SESSION['role_name'] ?? ''));
$canManage = Auth::hasPermission('system.settings') || in_array($roleName, ['admin', 'super admin', 'super_admin', 'manager'], true);
if (!$canManage) { json_response(['error' => 'Permission denied'], 403); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); }
if (defined('DEMO_MODE') && DEMO_MODE) { json_response(['success' => true, 'demo' => true]); }

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$kind = trim((string)($input['kind'] ?? ''));
$action = trim((string)($input['action'] ?? ''));
$ids = array_values(array_filter(array_map('intval', (array)($input['ids'] ?? [])), fn($id) => $id > 0));
if (!$ids) { json_response(['error' => 'Select at least one item'], 400); }

try {
    if ($kind === 'checklist') {
        if (!in_array($action, ['approve', 'reject'], true)) {
            json_response(['error' => 'Invalid checklist review action'], 400);
        }
        $selectedIssueId = (int)($input['issue_id'] ?? 0);
        $updated = 0;
        $removedIds = [];
        $duplicateIds = [];
        $problemRequiredIds = [];
        foreach ($ids as $id) {
            $result = TicketStepSuggestions::review($id, $action, Auth::userId(), (string)($_SESSION['user_name'] ?? 'Admin'), $selectedIssueId);
            if ($result['status'] === 'problem_required') { $problemRequiredIds[] = $id; continue; }
            if ($result['status'] === 'duplicate') { $duplicateIds[] = $id; continue; }
            if (in_array($result['status'], ['approved', 'rejected', 'missing'], true)) {
                $removedIds[] = $id;
                $updated += (int)$result['updated'];
            }
        }
        if (count($ids) === 1 && $problemRequiredIds) {
            json_response(['error' => 'This legacy ticket has no saved problem and cannot be approved as reusable.'], 422);
        }
        json_response([
            'success' => true,
            'updated' => $updated,
            'removed_ids' => $removedIds,
            'duplicate_ids' => $duplicateIds,
            'problem_required_ids' => $problemRequiredIds,
            'duplicate' => count($ids) === 1 && !empty($duplicateIds),
            'message' => $duplicateIds
                ? $updated . ' step(s) approved; ' . count($duplicateIds) . ' already exist and were not added again.'
                : '',
        ]);
    }

    if (!in_array($kind, ['result', 'recommendation', 'confirmed_by'], true)) {
        json_response(['error' => 'Invalid approval type'], 400);
    }
    if ($action === 'approve') {
        json_response(['success' => true, 'updated' => TicketFieldMemory::approve($ids, Auth::userId())]);
    }
    if ($action === 'delete') {
        json_response(['success' => true, 'updated' => TicketFieldMemory::deletePending($ids)]);
    }
    json_response(['error' => 'Invalid action'], 400);
} catch (Throwable $e) {
    json_response(['error' => 'Approval update failed: ' . $e->getMessage()], 500);
}
