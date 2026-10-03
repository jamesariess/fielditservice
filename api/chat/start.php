<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/ChatAccess.php';
Auth::start(); Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error'=>'POST required'],405); exit; }
$input=json_decode(file_get_contents('php://input'),true) ?: [];
$target=(int)($input['user_id'] ?? 0); $user=(int)Auth::userId();
try {
    if ($target === $user || !ChatAccess::allowed($user,$target)) { json_response(['error'=>'This user is unavailable or cross-department approval is required.'],403); exit; }
    $pdo=Database::getInstance(); $pdo->beginTransaction();
    // Serialize creation by the pair's lower user ID to avoid duplicate conversations.
    Database::fetch('SELECT id FROM users WHERE id=? FOR UPDATE',[min($user,$target)]);
    $conv=Database::fetch("SELECT c.id FROM chat_conversations c JOIN chat_participants a ON a.conversation_id=c.id AND a.user_id=? JOIN chat_participants b ON b.conversation_id=c.id AND b.user_id=? WHERE c.type='direct' AND (SELECT COUNT(*) FROM chat_participants p WHERE p.conversation_id=c.id)=2 LIMIT 1",[$user,$target]);
    $id=(int)($conv['id'] ?? 0);
    if (!$id) {
        $id=Database::insert('chat_conversations',['type'=>'direct','name'=>'Direct conversation','created_by'=>$user]);
        Database::insert('chat_participants',['conversation_id'=>$id,'user_id'=>$user]);
        Database::insert('chat_participants',['conversation_id'=>$id,'user_id'=>$target]);
    }
    $pdo->commit(); ChatAccess::touch();
    json_response(['success'=>true,'conversation_id'=>$id]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Chat start: '.$e->getMessage()); json_response(['error'=>'Could not open conversation.'],500);
}
