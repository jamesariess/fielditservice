<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Team Chat';
$active_menu = 'chat';
require APP_ROOT . '/includes/layout_header.php';

$conversations = [
    ['id'=>1,'name'=>'Field IT Team','lastMsg'=>'New ticket assigned to you','time'=>'5m','unread'=>3,'type'=>'group'],
    ['id'=>2,'name'=>'Maria S.','lastMsg'=>'Thanks for the help!','time'=>'30m','unread'=>0,'type'=>'dm'],
    ['id'=>3,'name'=>'Support Channel','lastMsg'=>'Printer issue at reception','time'=>'1h','unread'=>1,'type'=>'group'],
    ['id'=>4,'name'=>'Carlos R.','lastMsg'=>'Can you check the server room?','time'=>'2h','unread'=>0,'type'=>'dm'],
    ['id'=>5,'name'=>'Escalations','lastMsg'=>'New escalation from Juan D.','time'=>'4h','unread'=>2,'type'=>'group'],
];

$messages = [
    ['user'=>'Admin','msg'=>'Good morning team! Reminder to check your assigned tickets today.','time'=>'9:00 AM','isMe'=>false],
    ['user'=>'Juan D.','msg'=>"I'll start with the printer issue at reception.",'time'=>'9:05 AM','isMe'=>false],
    ['user'=>'You','msg'=>"I'm working on the no display issue in finance.",'time'=>'9:12 AM','isMe'=>true],
    ['user'=>'Admin','msg'=>'Thanks for the update. Let me know if you need help.','time'=>'9:15 AM','isMe'=>false],
    ['user'=>'Maria S.','msg'=>'Has anyone dealt with the new Epson printers? Paper jam issue.','time'=>'10:30 AM','isMe'=>false],
    ['user'=>'You','msg'=>"Yes, open the rear panel and pull paper from the back, not the front.",'time'=>'10:35 AM','isMe'=>true],
];

?>
<div style="max-width:1200px;margin:0 auto;height:calc(100vh - 8rem);">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;height:100%;display:flex;overflow:hidden;">
        <!-- Conversations Sidebar -->
        <div style="width:280px;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;flex-shrink:0;" class="hide-mobile">
            <div style="padding:12px;border-bottom:1px solid #e5e7eb;">
                <input type="text" placeholder="Search conversations..." style="width:100%;padding:8px 12px 8px 36px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;" class="dark-input">
            </div>
            <div style="flex:1;overflow-y:auto;">
                <?php foreach ($conversations as $i => $conv): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;cursor:pointer;<?= $i === 0 ? 'background:#eff6ff;border-right:3px solid #2563eb;' : '' ?>" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='<?= $i === 0 ? '#eff6ff' : '' ?>'">
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
                    <div style="font-size:14px;font-weight:600;color:#111827;">Field IT Team</div>
                    <div style="font-size:11px;color:#64748b;">5 members online</div>
                </div>
            </div>
            <div id="chat-messages" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:16px;" class="custom-scroll">
                <?php foreach ($messages as $msg): ?>
                <div style="display:flex;gap:10px;<?= $msg['isMe'] ? 'flex-direction:row-reverse;' : '' ?>">
                    <div style="width:32px;height:32px;border-radius:50%;background:<?= $msg['isMe'] ? '#dbeafe' : '#f1f5f9' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:700;color:<?= $msg['isMe'] ? '#1d4ed8' : '#64748b' ?>;"><?= strtoupper(substr($msg['user'], 0, 2)) ?></span>
                    </div>
                    <div style="max-width:70%;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;<?= $msg['isMe'] ? 'justify-content:flex-end;' : '' ?>">
                            <span style="font-size:11px;font-weight:600;color:#374151;"><?= e($msg['user']) ?></span>
                            <span style="font-size:10px;color:#94a3b8;"><?= e($msg['time']) ?></span>
                        </div>
                        <div style="padding:10px 14px;font-size:13px;line-height:1.5;<?= $msg['isMe'] ? 'background:#2563eb;color:#fff;border-radius:16px 16px 4px 16px;' : 'background:#f1f5f9;color:#111827;border-radius:16px 16px 16px 4px;' ?>">
                            <?= e($msg['msg']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="padding:14px 20px;border-top:1px solid #e5e7eb;flex-shrink:0;">
                <form onsubmit="chatSendMessage(event)" style="display:flex;gap:10px;align-items:flex-end;">
                    <div style="flex:1;">
                        <input id="chat-msg-input" type="text" placeholder="Type a message..." data-conversation-id="1"
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
