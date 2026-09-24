<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT.'/config/app.php'; require_once APP_ROOT.'/config/demo.php'; require_once APP_ROOT.'/includes/helpers.php'; require_once APP_ROOT.'/includes/Database.php'; require_once APP_ROOT.'/includes/Auth.php';
require_once APP_ROOT.'/includes/Activity.php';
Auth::start(); Auth::requireLogin();
$role = strtolower((string)($_SESSION['role_name'] ?? ''));
if (!Auth::hasPermission('system.settings') && !in_array($role, ['admin','super admin','super_admin','manager'], true)) { json_response(['error'=>'Permission denied'],403); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') { json_response(['error'=>'PATCH required'],405); exit; }
$id=(int)($_GET['id']??0); $input=json_decode(file_get_contents('php://input'),true) ?: [];
$allowedStatus=['new','in_progress','solved','partial','escalated','unsolved','cancelled']; $status=$input['status']??null;
if (!$id || ($status !== null && !in_array($status,$allowedStatus,true))) { json_response(['error'=>'Invalid ticket update'],400); exit; }
$fields=[];$params=[];
foreach(['ticket_number','company_name','location','task','problem_description','priority','status'] as $field){
    if(!array_key_exists($field,$input)) continue;
    if($field==='status'&&$input[$field]==='cancelled'){$fields[]='status = ?';$params[]='unsolved';$fields[]='resolution_type = ?';$params[]='cancelled';continue;}
    if($field==='priority'&&!in_array($input[$field],['low','medium','high','critical'],true)){json_response(['error'=>'Invalid priority'],400);exit;}
    if($field==='task' && mb_strlen(trim((string)$input[$field])) > 255){json_response(['error'=>'Task must be 255 characters or fewer'],400);exit;}
    $fields[]=$field.' = ?';$params[] = trim((string)$input[$field]);
}
if(array_key_exists('steps_performed',$input)){
    if(!is_array($input['steps_performed'])){json_response(['error'=>'Troubleshooting steps must be a list'],400);exit;}
    $steps=[];$seen=[];
    foreach($input['steps_performed'] as $step){$step=trim((string)$step);$key=mb_strtolower($step);if($step===''||isset($seen[$key]))continue;$seen[$key]=true;$steps[]=$step;}
    $fields[]='steps_performed = ?';$params[]=json_encode($steps);
}
if(array_key_exists('issue_id',$input)){
    $issueId=(int)$input['issue_id'];
    if($issueId > 0 && !Database::fetch('SELECT id FROM troubleshooting_issues WHERE id = ?',[$issueId])){json_response(['error'=>'Selected problem no longer exists'],400);exit;}
    $fields[]='issue_id = ?';$params[]=$issueId ?: null;
}
if(!$fields){json_response(['error'=>'No changes supplied'],400);exit;} $target=Database::fetch('SELECT user_id, ticket_number FROM troubleshooting_sessions WHERE id = ?',[$id]); $params[]=$id; Database::execute('UPDATE troubleshooting_sessions SET '.implode(', ',$fields).' WHERE id = ?',$params); Activity::log($status==='cancelled'?'CANCEL':'UPDATE','ticket',$id,$input); if($target && (int)$target['user_id'] !== (int)Auth::userId()) Activity::notifyUsers([(int)$target['user_id']], $status==='cancelled'?'ticket_cancelled':'ticket_updated', ($status==='cancelled'?'Ticket cancelled: ':'Ticket updated: ').($target['ticket_number'] ?: 'SD'.$id), 'A manager updated your ticket.', '/tickets'); json_response(['success'=>true]);
