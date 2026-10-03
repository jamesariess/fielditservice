<?php
if (!defined('APP_ROOT')) { exit; }
$page_title='Notifications'; $active_menu='';
require APP_ROOT.'/includes/layout_header.php';
$notifications=Database::fetchAll('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC,id DESC LIMIT 200',[Auth::userId()]);
?>
<section style="max-width:900px;margin:auto;">
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;"><h1 style="font-size:24px;font-weight:700;">Notifications</h1><button type="button" class="btn btn-secondary" onclick="markAllNotificationsRead(event)"><i data-lucide="check-check"></i> Mark all read</button></div>
<?php if (!$notifications): ?><p>No notifications yet.</p><?php endif; ?>
<?php foreach ($notifications as $notification): ?>
<button type="button" class="notif-item" style="width:100%;text-align:left;background:white;border-bottom:1px solid #e2e8f0;padding:16px;display:flex;gap:12px;" data-notification-id="<?= (int)$notification['id'] ?>" data-notification-url="<?= e($notification['url'] ?? '') ?>" onclick="openNotification(this)">
<i data-lucide="bell" style="color:#2563eb;flex-shrink:0;"></i><span style="min-width:0;overflow-wrap:anywhere;"><strong><?= e($notification['title']) ?></strong><span style="display:block;"><?= e($notification['message']) ?></span><small><?= e($notification['created_at']) ?></small></span><span class="<?= (int)$notification['is_read'] ? 'notif-dot-read' : 'notif-dot-unread' ?>"></span>
</button>
<?php endforeach; ?>
</section>
<?php require APP_ROOT.'/includes/layout_footer.php'; ?>
