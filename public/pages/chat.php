<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Team Chat';
$active_menu = 'chat';
require APP_ROOT . '/includes/layout_header.php';

$userId = (int)Auth::userId();
require_once APP_ROOT . '/includes/ChatAccess.php';

function chat_column_exists(string $table, string $column): bool {
    try { return (bool)Database::fetch("SHOW COLUMNS FROM `$table` LIKE ?", [$column]); }
    catch (Throwable $e) { return false; }
}

$hasLastSeen = chat_column_exists('users', 'last_seen_at');
$hasRequests = false;
try { $hasRequests = (bool)Database::fetch("SHOW TABLES LIKE 'chat_department_requests'"); } catch (Throwable $e) {}
if ($hasLastSeen) {
    try { Database::query('UPDATE users SET last_seen_at = NOW() WHERE id = ?', [$userId]); } catch (Throwable $e) {}
}

$me = Database::fetch("SELECT u.id, u.full_name, u.department_id, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.id = ?", [$userId]) ?: [];
$myDepartmentId = (int)($me['department_id'] ?? 0);
$seenExpr = $hasLastSeen ? 'u.last_seen_at' : 'u.last_login';

$sameDeptUsers = Database::fetchAll(
    "SELECT u.id, u.full_name, u.email, u.department_id, d.name AS department_name, $seenExpr AS seen_at,
            CASE WHEN $seenExpr IS NOT NULL AND $seenExpr >= (NOW() - INTERVAL 5 MINUTE) THEN 1 ELSE 0 END AS is_online
     FROM users u LEFT JOIN departments d ON d.id = u.department_id
     WHERE u.id <> ? AND u.status = 'active' AND u.deleted_at IS NULL AND COALESCE(u.department_id, 0) = ?
     ORDER BY is_online DESC, u.full_name",
    [$userId, $myDepartmentId]
) ?: [];

$otherDeptUsers = Database::fetchAll(
    "SELECT u.id, u.full_name, u.email, u.department_id, d.name AS department_name, $seenExpr AS seen_at,
            CASE WHEN $seenExpr IS NOT NULL AND $seenExpr >= (NOW() - INTERVAL 5 MINUTE) THEN 1 ELSE 0 END AS is_online
     FROM users u LEFT JOIN departments d ON d.id = u.department_id
     WHERE u.id <> ? AND u.status = 'active' AND u.deleted_at IS NULL AND COALESCE(u.department_id, 0) <> ?
     ORDER BY d.name, u.full_name",
    [$userId, $myDepartmentId]
) ?: [];

$conversations = Database::fetchAll(
    "SELECT c.id, c.type, COALESCE(NULLIF(c.name, ''), 'Direct conversation') AS name,
            (SELECT cm.content FROM chat_messages cm WHERE cm.conversation_id = c.id ORDER BY cm.created_at DESC, cm.id DESC LIMIT 1) AS last_msg,
            (SELECT cm.created_at FROM chat_messages cm WHERE cm.conversation_id = c.id ORDER BY cm.created_at DESC, cm.id DESC LIMIT 1) AS last_at
     FROM chat_conversations c INNER JOIN chat_participants cp ON cp.conversation_id = c.id AND cp.user_id = ?
     ORDER BY COALESCE(last_at, c.created_at) DESC, c.id DESC",
    [$userId]
) ?: [];
$conversations = array_values(array_filter($conversations, fn($c) => ChatAccess::conversationAllowed((int)$c['id'], $userId)));
$requests = $hasRequests ? Database::fetchAll("SELECT q.*, u.full_name, d.name AS department_name FROM chat_department_requests q JOIN users u ON u.id=q.requester_id LEFT JOIN departments d ON d.id=u.department_id WHERE q.status='pending' ORDER BY q.created_at") : [];
$departments = Database::fetchAll('SELECT id,name FROM departments ORDER BY name');

$activeConversationId = (int)($_GET['conversation_id'] ?? ($conversations[0]['id'] ?? 0));
if ($activeConversationId && !ChatAccess::conversationAllowed($activeConversationId, $userId)) {
    $activeConversationId = 0;
}
$messages = $activeConversationId ? Database::fetchAll(
    "SELECT cm.content AS msg, cm.created_at, u.full_name AS user, cm.user_id
     FROM chat_messages cm INNER JOIN users u ON u.id = cm.user_id
     WHERE cm.conversation_id = ? ORDER BY cm.created_at ASC, cm.id ASC",
    [$activeConversationId]
) : [];
$activeParticipantCount = $activeConversationId ? (int)(Database::fetch('SELECT COUNT(*) AS total FROM chat_participants WHERE conversation_id = ?', [$activeConversationId])['total'] ?? 0) : 0;
$assistantMode = isset($_GET['assistant']) || !$activeConversationId;
if ($assistantMode) { $activeConversationId = 0; $messages = []; }
?>

<style>
.team-chat-shell{width:100%;max-width:none;margin:0;height: min(760px,calc(100dvh - 8rem));min-height:360px;display:grid;grid-template-columns:280px minmax(0,1fr);background:#fff;border:1px solid #dbe3ef;border-radius:8px;overflow:hidden}
.tc-side{border-right:1px solid #e5edf7;display:flex;flex-direction:column;min-width:0;background:#f8fbff}.tc-side-head{padding:16px;border-bottom:1px solid #e5edf7}.tc-side-head h2{margin:0;color:#101827;font-size:16px;font-weight:800}.tc-side-head p{margin:3px 0 0;color:#64748b;font-size:12px}
.tc-section{padding:12px 12px 4px}.tc-section-title{margin:0 0 8px;color:#64748b;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.tc-list{display:grid;gap:7px}
.tc-person,.tc-conv{display:flex;align-items:center;gap:10px;width:100%;padding:10px;border:1px solid #dbe3ef;border-radius:10px;background:#fff;color:#172033;text-align:left;cursor:pointer;text-decoration:none}.tc-person:hover,.tc-conv:hover,.tc-conv.active{border-color:#93b4f8;background:#eff6ff}
.tc-avatar{position:relative;display:grid;place-items:center;width:36px;height:36px;flex:0 0 36px;border-radius:10px;background:#e8f0ff;color:#2563eb;font-weight:800;font-size:12px}.tc-avatar svg{width:17px;height:17px}.tc-online-dot{position:absolute;right:-2px;bottom:-2px;width:10px;height:10px;border:2px solid #fff;border-radius:50%;background:#94a3b8}.tc-online-dot.online{background:#22c55e}
.tc-meta{min-width:0;flex:1}.tc-name{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#172033;font-size:13px;font-weight:800}.tc-sub{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b;font-size:11px}.tc-request{margin-left:auto;padding:6px 8px;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:800;cursor:pointer}
.tc-chat{display:flex;min-width:0;flex-direction:column;background:#fff}.tc-chat-head{display:flex;align-items:center;gap:12px;padding:15px 18px;border-bottom:1px solid #e5edf7}.tc-chat-head h1{margin:0;color:#101827;font-size:16px}.tc-chat-head p{margin:2px 0 0;color:#64748b;font-size:12px}
.tc-messages{flex:1;display:flex;flex-direction:column;gap:14px;overflow-y:auto;padding:18px;background:linear-gradient(180deg,#fff,#f8fbff)}.tc-msg{display:flex;gap:10px}.tc-msg.me{flex-direction:row-reverse}.tc-bubble-wrap{max-width:min(620px,74%)}.tc-msg-top{display:flex;gap:7px;margin-bottom:4px;color:#64748b;font-size:11px}.tc-msg.me .tc-msg-top{justify-content:flex-end}.tc-bubble{padding:10px 13px;border-radius:15px;background:#eef2f7;color:#172033;font-size:13px;line-height:1.45;white-space:pre-wrap}.tc-msg.me .tc-bubble{background:#2563eb;color:#fff;border-radius:15px 15px 4px 15px}
.tc-compose{padding:14px 18px;border-top:1px solid #e5edf7;background:#fff}.tc-compose form{display:flex;gap:10px;align-items:center}.tc-compose input{flex:1;height:42px;border:1px solid #cbd5e1;border-radius:999px;padding:0 16px;color:#172033;font-size:13px}.tc-compose input:focus{outline:0;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}.tc-send{display:grid;place-items:center;width:42px;height:42px;border:0;border-radius:50%;background:#2563eb;color:#fff;cursor:pointer}.tc-send:disabled{opacity:.45;cursor:not-allowed}.tc-send svg{width:17px;height:17px}
.tc-empty{margin:auto;text-align:center;color:#64748b}.tc-empty i{width:34px;height:34px;color:#94a3b8}.tc-empty h2{margin:10px 0 4px;color:#172033;font-size:16px}.tc-db-note{margin:10px 12px 12px;padding:10px;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;font-size:11px;line-height:1.4}
.tc-chat,.tc-messages,.tc-side{min-height:0}.tc-compose input{min-width:0}.tc-bubble{overflow-wrap:anywhere}.tc-fieldmate{background:#0f766e;color:#fff}.tc-fieldmate svg{width:22px;height:22px}.tc-chat-head .tc-new{margin-left:auto}.tc-empty{max-width:440px;padding:24px}.tc-starter{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:20px}.tc-starter button{padding:10px;border:1px solid #dbe3ef;border-radius:6px;background:#f8fafc;color:#172033;font:inherit;cursor:pointer}
.dark .team-chat-shell,.dark .tc-chat,.dark .tc-compose{background:#111827;border-color:#374151}.dark .tc-side,.dark .tc-messages{background:#161f2b}.dark .tc-side-head,.dark .tc-chat-head,.dark .tc-compose{border-color:#374151}.dark .tc-person,.dark .tc-conv,.dark .tc-starter button{background:#1f2937;border-color:#374151;color:#f3f4f6}.dark .tc-person:hover,.dark .tc-conv:hover,.dark .tc-conv.active{background:#263747;border-color:#60a5fa}.dark .tc-name,.dark .tc-side-head h2,.dark .tc-chat-head h1,.dark .tc-empty h2{color:#f3f4f6}.dark .tc-sub,.dark .tc-section-title,.dark .tc-msg-top,.dark .tc-empty,.dark .tc-chat-head p,.dark .tc-side-head p{color:#b2bfce}.dark .tc-bubble{background:#263445;color:#f3f4f6}.dark .tc-msg.me .tc-bubble{background:#2563eb;color:#fff}.dark .tc-avatar{background:#25394b;color:#93c5fd}.dark .tc-fieldmate{background:#0f766e;color:#fff}.dark .tc-request{background:#213449;border-color:#395572;color:#bfdbfe}.dark .tc-compose input{background:#1f2937;border-color:#4b5563;color:#f3f4f6}.dark .tc-db-note{background:#332819;border-color:#70552d;color:#fde68a}
.tc-people-toggle{display:none}.tc-compose textarea{flex:1;min-width:0;resize:none;min-height:46px;max-height:120px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;padding:12px;color:#172033;font:inherit;font-size:16px;line-height:22px}.tc-compose textarea:focus{outline:2px solid #60a5fa;outline-offset:1px}.dark .tc-compose textarea{background:#1f2937;color:#f3f4f6;border-color:#4b5563}.tc-compose form{align-items:flex-end}.tc-send{flex:0 0 44px;width:44px;height:46px;border-radius:8px}.tc-empty h2{font-size:22px}.tc-empty p{font-size:14px;line-height:1.6}.tc-starter button{text-align:left;display:flex;align-items:center;gap:10px}.tc-starter svg{width:18px;height:18px;flex-shrink:0}.tc-bubble{line-height:1.65}.tc-compose-hint{margin:8px 0 0;font-size:11px;text-align:center;color:#64748b}.dark .tc-compose-hint{color:#b2bfce}
@media(max-width:800px){.team-chat-shell{position:relative;height:calc(100dvh - var(--header-height,64px) - var(--mobile-nav-height,72px) - 32px);min-height:0;grid-template-columns:1fr;grid-template-rows:minmax(0,1fr);border-radius:8px}.tc-side{display:none;position:absolute;inset:0;z-index:5;max-height:none;border:0;background:#f8fbff}.team-chat-shell.people-open .tc-side{display:flex}.tc-side-head{display:flex;align-items:center;justify-content:space-between}.tc-side-head p{display:none}.tc-people-toggle{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border:1px solid #dbe3ef;border-radius:8px;background:transparent;color:#475569;cursor:pointer}.tc-people-toggle svg{width:18px;height:18px}.dark .tc-people-toggle{border-color:#374151;color:#e5e7eb}.tc-bubble-wrap{max-width:100%;min-width:0}.tc-msg .tc-avatar{display:none}.tc-msg.me .tc-bubble-wrap{max-width:88%}.tc-msg-top{font-size:11px}.tc-bubble{padding:12px 14px;border-radius:8px;font-size:14px}.tc-compose{padding:12px}.tc-chat-head{padding:12px;gap:10px}.tc-chat-head h1{font-size:16px}.tc-chat-head p{font-size:11px}.tc-messages{padding:16px;gap:16px;background:#fff}.dark .tc-messages{background:#161f2b}.tc-empty{margin:auto;max-width:340px;padding:16px 0;text-align:left}.tc-empty> .tc-avatar{margin:0 0 16px!important}.tc-empty h2{font-size:22px}.tc-starter{display:grid;grid-template-columns:1fr;gap:8px;justify-content:stretch;margin-top:18px}.tc-starter button{min-height:44px;width:100%;font-size:13px;padding:12px}.tc-new{width:38px;height:38px;display:grid;place-items:center;padding:0}.tc-new svg{width:18px;height:18px}.fieldmate-keyboard .bottom-nav{display:none!important}.tc-compose-hint{font-size:10px}}
</style>

<div class="team-chat-shell">
    <aside class="tc-side">
        <div class="tc-side-head"><div><h2>Conversations</h2><p><?= e($me['department_name'] ?? 'Your department') ?></p></div><button type="button" class="tc-people-toggle" onclick="teamChatPeople(false)" aria-label="Close conversations"><i data-lucide="x"></i></button></div>
        <?php if (!$hasLastSeen || !$hasRequests): ?><div class="tc-db-note">Chat access requests and live presence need administrator setup.</div><?php endif; ?>
        <div style="overflow:auto;padding-bottom:12px;">
            <section class="tc-section"><a class="tc-conv <?= $assistantMode ? 'active' : '' ?>" href="<?= e(app_base()) ?>team-messages?assistant=1"><span class="tc-avatar tc-fieldmate"><i data-lucide="bot"></i></span><span class="tc-meta"><span class="tc-name">FieldMate</span><span class="tc-sub">IT support assistant</span></span></a></section>
            <section class="tc-section"><h3 class="tc-section-title">Available in your department</h3><div class="tc-list">
                <?php if (!$sameDeptUsers): ?><div class="tc-sub">No other active users in your department.</div><?php endif; ?>
                <?php foreach ($sameDeptUsers as $u): $initials = strtoupper(substr(trim($u['full_name']),0,2)); ?>
                <button type="button" class="tc-person" onclick="teamChatStart(<?= (int)$u['id'] ?>)"><span class="tc-avatar"><?= e($initials) ?><i class="tc-online-dot <?= (int)$u['is_online'] ? 'online' : '' ?>"></i></span><span class="tc-meta"><span class="tc-name"><?= e($u['full_name']) ?></span><span class="tc-sub"><?= (int)$u['is_online'] ? 'Online now' : 'Offline' ?> · <?= e($u['department_name'] ?: 'No department') ?></span></span></button>
                <?php endforeach; ?>
            </div></section>
            <section class="tc-section"><h3 class="tc-section-title">Other departments</h3><div class="tc-list">
                <?php foreach ($departments as $department): if ((int)$department['id'] === $myDepartmentId) continue; ?>
                <button type="button" class="tc-person" onclick="teamChatDepartment(<?= (int)$department['id'] ?>)"><i data-lucide="building-2"></i><span class="tc-meta"><?= e($department['name']) ?></span><span class="tc-request">Request access</span></button>
                <?php endforeach; ?>
                <?php $otherDeptUsers = array_filter($otherDeptUsers, fn($u) => ChatAccess::allowed($userId, (int)$u['id'])); ?>
                <?php foreach ($otherDeptUsers as $u): $initials = strtoupper(substr(trim($u['full_name']),0,2)); ?>
                <button type="button" class="tc-person" onclick="teamChatStart(<?= (int)$u['id'] ?>)"><span class="tc-avatar"><?= e($initials) ?><i class="tc-online-dot <?= (int)$u['is_online'] ? 'online' : '' ?>"></i></span><span class="tc-meta"><span class="tc-name"><?= e($u['full_name']) ?></span><span class="tc-sub"><?= (int)$u['is_online'] ? 'Online now' : 'Offline' ?> · <?= e($u['department_name'] ?: 'No department') ?></span></span></button>
                <?php endforeach; ?>
            </div></section>
            <?php if (ChatAccess::canReview()): ?><section class="tc-section"><h3 class="tc-section-title">Access requests</h3><?php foreach ($requests as $request): ?><div class="tc-person"><span class="tc-meta"><span class="tc-name"><?= e($request['full_name']) ?></span><span><?= e($request['reason']) ?></span></span><button type="button" class="tc-request" onclick="teamChatReview(<?= (int)$request['id'] ?>,'approved')">Approve</button><button type="button" class="tc-request" onclick="teamChatReview(<?= (int)$request['id'] ?>,'rejected')">Reject</button></div><?php endforeach; ?></section><?php endif; ?>
            <section class="tc-section"><h3 class="tc-section-title">Conversations</h3><div class="tc-list">
                <?php foreach ($conversations as $conv): $selected = (int)$conv['id'] === $activeConversationId; ?>
                <a class="tc-conv <?= $selected ? 'active' : '' ?>" href="<?= e(app_base()) ?>team-messages?conversation_id=<?= (int)$conv['id'] ?>"><span class="tc-avatar"><i data-lucide="<?= $conv['type']==='group' ? 'users' : 'user' ?>"></i></span><span class="tc-meta"><span class="tc-name"><?= e($conv['name']) ?></span><span class="tc-sub"><?= e($conv['last_msg'] ?: 'No messages yet') ?></span></span></a>
                <?php endforeach; ?>
            </div></section>
        </div>
    </aside>
    <main class="tc-chat">
        <header class="tc-chat-head"><button type="button" class="tc-people-toggle" onclick="teamChatPeople(true)" aria-label="Open conversations" title="Conversations"><i data-lucide="panel-left"></i></button><span class="tc-avatar <?= $assistantMode ? 'tc-fieldmate' : '' ?>"><i data-lucide="<?= $assistantMode ? 'bot' : 'messages-square' ?>"></i></span><div><h1><?= $assistantMode ? 'FieldMate' : 'Conversation' ?></h1><p><?= $assistantMode ? 'IT support assistant' : $activeParticipantCount.' participants' ?></p></div><?php if ($assistantMode): ?><button type="button" class="tc-request tc-new" onclick="fieldMateNew()" title="New conversation" aria-label="New conversation"><i data-lucide="square-pen"></i></button><?php endif; ?></header>
        <div id="chat-messages" class="tc-messages custom-scroll">
            <?php if ($assistantMode): ?><div class="tc-empty" id="fieldmate-welcome"><span class="tc-avatar tc-fieldmate" style="margin:0 auto 12px;width:48px;height:48px;"><i data-lucide="bot"></i></span><h2>Hi, I'm FieldMate.</h2><p>What's happening with your device?</p><div class="tc-starter"><button type="button" onclick="fieldMateQuick('My computer restarts unexpectedly')">Unexpected restarts</button><button type="button" onclick="fieldMateQuick('My laptop has no display')">No display</button><button type="button" onclick="fieldMateQuick('Help me diagnose a hardware fault')">Hardware diagnosis</button></div></div><?php endif; ?>
            <?php foreach ($messages as $msg): $isMe = (int)$msg['user_id'] === $userId; $initials = strtoupper(substr(trim($msg['user']),0,2)); ?>
            <div class="tc-msg <?= $isMe ? 'me' : '' ?>"><span class="tc-avatar"><?= e($initials) ?></span><div class="tc-bubble-wrap"><div class="tc-msg-top"><strong><?= e($isMe ? 'You' : $msg['user']) ?></strong><span><?= e(date('g:i A', strtotime($msg['created_at']))) ?></span></div><div class="tc-bubble"><?= e($msg['msg']) ?></div></div></div>
            <?php endforeach; ?>
        </div>
        <div class="tc-compose"><form onsubmit="<?= $assistantMode ? 'fieldMateSend(event)' : 'chatSendMessage(event)' ?>"><?php if ($assistantMode): ?><textarea id="chat-msg-input" rows="1" maxlength="2000" placeholder="Describe your issue..." data-conversation-id="0" aria-label="Message to FieldMate"></textarea><?php else: ?><input id="chat-msg-input" type="text" maxlength="2000" placeholder="Type a message..." data-conversation-id="<?= (int)$activeConversationId ?>" autocomplete="off" aria-label="Message"><?php endif; ?><button class="tc-send" type="submit" title="Send message" aria-label="Send message"><i data-lucide="arrow-up"></i></button></form><?php if ($assistantMode): ?><p class="tc-compose-hint">Verify repairs against approved service procedures.</p><?php endif; ?></div>
    </main>
</div>

<script>
function teamChatPeople(open){document.querySelector('.team-chat-shell').classList.toggle('people-open',open);}
function teamChatStart(userId){api('/api/chat/start',{method:'POST',body:{user_id:userId}}).then(function(data){if(data.conversation_id)window.location=APP_BASE+'team-messages?conversation_id='+data.conversation_id;}).catch(function(err){showToast(err.message||'Could not start chat.','error');});}
function teamChatDepartment(id){var reason=prompt('Reason for requesting access to this department:');if(!reason)return;api('/api/chat/request',{method:'POST',body:{department_id:id,reason:reason}}).then(function(){showToast('Access requested.','success');}).catch(function(err){showToast(err.message,'error');});}
function teamChatReview(id,status){api('/api/chat/request',{method:'POST',body:{request_id:id,status:status}}).then(function(){location.reload();}).catch(function(err){showToast(err.message,'error');});}
function teamChatRequest(userId){var reason=prompt('Why do you need to chat with this department?');if(reason===null)return;api('/api/chat/request',{method:'POST',body:{user_id:userId,reason:reason}}).then(function(){showToast('Permission request sent to admins/supervisors.','success');}).catch(function(err){showToast(err.message||'Could not send request.','error');});}
setTimeout(function(){var el=document.getElementById('chat-messages');if(el)el.scrollTop=el.scrollHeight;},50);
var teamChatLastCount=<?= count($messages) ?>;
setInterval(function(){if(document.hidden)return;var input=document.getElementById('chat-msg-input');var id=input.dataset.conversationId;if(id==='0')return;api('/api/chat?conversation_id='+id).then(function(data){if(data.messages.length===teamChatLastCount)return;var el=document.getElementById('chat-messages');el.innerHTML='';data.messages.forEach(function(message){chatAddMsgToUI(message.content,message.user_id===<?= $userId ?>?'out':'in');});teamChatLastCount=data.messages.length;}).catch(function(){});},5000);
</script>
<?php if ($assistantMode): ?>
<script>window.fieldMateUserId=<?= $userId ?>;</script>
<script src="<?= e(app_base()) ?>assets/js/fieldmate.js?v=<?= filemtime(APP_ROOT.'/public/assets/js/fieldmate.js') ?>"></script>
<?php endif; ?>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
