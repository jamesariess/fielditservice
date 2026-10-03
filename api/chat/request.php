<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/ChatAccess.php';
require_once APP_ROOT . '/includes/Activity.php';
Auth::start(); Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error'=>'POST required'],405); exit; }
$input=json_decode(file_get_contents('php://input'),true) ?: [];
try {
    if (!ChatAccess::requestsAvailable()) { json_response(['error'=>'Chat access requests are not configured yet. Ask your administrator to import the chat migration.'],503); exit; }
    if (isset($input['request_id'])) {
        if (!ChatAccess::canReview()) { json_response(['error'=>'Administrator or supervisor access required.'],403); exit; }
        $status=$input['status'] ?? '';
        if (!in_array($status,['approved','rejected'],true)) { json_response(['error'=>'Invalid decision.'],400); exit; }
        $request=Database::fetch('SELECT * FROM chat_department_requests WHERE id=?',[(int)$input['request_id']]);
        if (!$request || (int)$request['requester_id']===(int)Auth::userId()) { json_response(['error'=>'You cannot review your own request.'],403); exit; }
        Database::query("UPDATE chat_department_requests SET status=?,reviewed_by=?,reviewed_at=NOW() WHERE id=? AND status='pending'",[$status,Auth::userId(),$request['id']]);
        if ($request['status'] === 'pending') Activity::notifyUsers([(int)$request['requester_id']], 'chat_access', 'Chat access '.$status, 'Your cross-department chat request was '.$status.'.', '/team-messages');
    } else {
        $target=(int)($input['user_id'] ?? 0);
        if (!empty($input['department_id'])) {
            $recipient=Database::fetch("SELECT id FROM users WHERE department_id=? AND id<>? AND status='active' AND deleted_at IS NULL ORDER BY id LIMIT 1",[(int)$input['department_id'],Auth::userId()]);
            $target=(int)($recipient['id'] ?? 0);
        }
        if ($target===(int)Auth::userId() || !Database::fetch("SELECT id FROM users WHERE id=? AND status='active' AND deleted_at IS NULL",[$target])) { json_response(['error'=>'Select an active user.'],400); exit; }
        if (ChatAccess::allowed((int)Auth::userId(),$target)) { json_response(['success'=>true,'approved'=>true]); exit; }
        $reason=trim((string)($input['reason'] ?? ''));
        if ($reason==='' || strlen($reason)>2000) { json_response(['error'=>'Enter a reason, up to 2000 characters.'],400); exit; }
        Database::query("INSERT INTO chat_department_requests (requester_id,target_user_id,reason) VALUES (?,?,?) ON DUPLICATE KEY UPDATE reason=VALUES(reason),status='pending',reviewed_by=NULL,reviewed_at=NULL",[Auth::userId(),$target,$reason]);
        Activity::notifyUsers(Activity::managers($target), 'chat_access_request', 'Department chat access request', Auth::userName().' requested access: '.$reason, '/team-messages');
    }
    json_response(['success'=>true]);
} catch (Throwable $e) { error_log('Chat request: '.$e->getMessage()); json_response(['error'=>'Could not save chat request.'],500); }
