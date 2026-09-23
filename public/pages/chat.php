<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Team Chat';
$active_menu = 'chat';
require APP_ROOT . '/includes/layout_header.php';

$conversations = Database::fetchAll("SELECT c.id, c.type, COALESCE(NULLIF(c.name, ''), 'Direct conversation') AS name, (SELECT cm.content FROM chat_messages cm WHERE cm.conversation_id = c.id ORDER BY cm.created_at DESC, cm.id DESC LIMIT 1) AS lastMsg, (SELECT cm.created_at FROM chat_messages cm WHERE cm.conversation_id = c.id ORDER BY cm.created_at DESC, cm.id DESC LIMIT 1) AS lastAt FROM chat_conversations c INNER JOIN chat_participants cp ON cp.conversation_id = c.id AND cp.user_id = ? ORDER BY COALESCE(lastAt, c.created_at) DESC, c.id DESC", [Auth::userId()]);
$requestedConversationId = (int)($_GET['conversation_id'] ?? 0);
$activeConversationId = $requestedConversationId && array_filter($conversations, fn($c) => (int)$c['id'] === $requestedConversationId) ? $requestedConversationId : (int)($conversations[0]['id'] ?? 0);
$activeParticipantCount = $activeConversationId ? (int)(Database::fetch('SELECT COUNT(*) AS total FROM chat_participants WHERE conversation_id = ?', [$activeConversationId])['total'] ?? 0) : 0;
$messages = $activeConversationId ? Database::fetchAll("SELECT cm.content AS msg, cm.created_at, u.full_name AS user, cm.user_id FROM chat_messages cm INNER JOIN users u ON u.id = cm.user_id WHERE cm.conversation_id = ? ORDER BY cm.created_at ASC, cm.id ASC", [$activeConversationId]) : [];

?>
<div style="max-width:1200px;margin:0 auto;height:calc(100vh - 8rem);">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;height:100%;display:flex;overflow:hidden;">
        <!-- Conversations Sidebar -->
        <div style="width:280px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;flex-shrink:0;" class="hide-mobile">
            <div style="padding:12px;border-bottom:1px solid #e5e7eb;">
                <input type="text" placeholder="Search conversations..." style="width:100%;padding:8px 12px 8px 36px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;" class="dark-input">
            </div>
            <div style="flex:1;overflow-y:auto;">
                <?php foreach ($conversations as $i => $conv): $selected = (int)$conv['id'] === $activeConversationId; ?>
                <div role="button" tabindex="0" onclick="window.location='?conversation_id=<?= (int)$conv['id'] ?>'" onkeydown="if(event.key==='Enter') window.location='?conversation_id=<?= (int)$conv['id'] ?>'" style="display:flex;align-items:center;gap:12px;padding:12px 16px;cursor:pointer;<?= $selected ? 'background:#eff6ff;border-right:3px solid #2563eb;' : '' ?>" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='<?= $selected ? '#eff6ff' : '' ?>'">
                    <div style="width:40px;height:40px;border-radius:50%;background:<?= $conv['type']==='group' ? '#dbeafe' : '#f1f5f9' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="<?= $conv['type']==='group' ? 'users' : 'user' ?>" style="width:16px;height:16px;color:#64748b;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;font-weight:600;color:#111827;"><?= e($conv['name']) ?></span>
                            <span style="font-size:10px;color:#94a3b8;"><?= e($conv['time']) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:2px;">
                            <p style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($conv['lastMsg']) ?></p>
                            <?php if ($conv['unread'] > 0): ?>
                            <span style="min-width:20px;height:20px;background:#2563eb;color:#fff;font-size:10px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-left:8px;"><?= $conv['unread'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Chat Area -->
        <div style="flex:1;display:flex;flex-direction:column;">
            <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <div style="width:36px;height:36px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="users" style="width:16px;height:16px;color:#2563eb;"></i>
                </div>
                <div>
                    <div id="chat-title" style="font-size:14px;font-weight:600;color:#111827;"><?= e($conversations[0]['name'] ?? 'Team Chat') ?></div>
                    <div style="font-size:11px;color:#64748b;"><?= $activeParticipantCount ? $activeParticipantCount . ' participant' . ($activeParticipantCount === 1 ? '' : 's') : 'Select a conversation to start' ?></div>
                </div>
            </div>
            <div id="chat-messages" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:16px;" class="custom-scroll">
                <?php foreach ($messages as $msg): $isMe = (int)$msg['user_id'] === (int)Auth::userId(); ?>
                <div style="display:flex;gap:10px;<?= $isMe ? 'flex-direction:row-reverse;' : '' ?>">
                    <div style="width:32px;height:32px;border-radius:50%;background:<?= $isMe ? '#dbeafe' : '#f1f5f9' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:700;color:<?= $isMe ? '#1d4ed8' : '#64748b' ?>;"><?= strtoupper(substr($msg['user'], 0, 2)) ?></span>
                    </div>
                    <div style="max-width:70%;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;<?= $isMe ? 'justify-content:flex-end;' : '' ?>">
                            <span style="font-size:11px;font-weight:600;color:#374151;"><?= e($isMe ? 'You' : $msg['user']) ?></span>
                            <span style="font-size:10px;color:#94a3b8;"><?= e(date('g:i A', strtotime($msg['created_at']))) ?></span>
                        </div>
                        <div style="padding:10px 14px;font-size:13px;line-height:1.5;<?= $isMe ? 'background:#2563eb;color:#fff;border-radius:16px 16px 4px 16px;' : 'background:#f1f5f9;color:#111827;border-radius:16px 16px 16px 4px;' ?>">
                            <?= e($msg['msg']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="padding:14px 20px;border-top:1px solid #e5e7eb;flex-shrink:0;">
                <form onsubmit="chatSendMessage(event)" style="display:flex;gap:10px;align-items:flex-end;">
                    <div style="flex:1;">
                    <input id="chat-msg-input" type="text" placeholder="<?= $activeConversationId ? 'Type a message...' : 'Select a conversation...' ?>" data-conversation-id="<?= $activeConversationId ?>" <?= $activeConversationId ? '' : 'disabled' ?>
                               style="width:100%;padding:10px 16px;border:1px solid #d1d5db;border-radius:20px;font-size:13px;outline:none;" class="dark-input"
                               onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatSendMessage(event);}">
                    </div>
                    <button type="submit" style="width:40px;height:40px;border-radius:50%;background:#2563eb;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="send" style="width:16px;height:16px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
