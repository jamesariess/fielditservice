<?php
/**
 * Layout Footer - closes the page after content is output.
 * Pages: require layout_header.php, echo content, require layout_footer.php
 */
$currentUser = [
    'name' => Auth::userName() ?? 'Guest',
    'role' => $_SESSION['role_name'] ?? 'User',
];

// App base path — centralised helper (see includes/helpers.php).
$fUrlBase = app_base();
$mainNav = [
    ['id' => 'dashboard', 'label' => 'Home', 'icon' => 'layout-dashboard', 'url' => $fUrlBase],
    ['id' => 'troubleshoot', 'label' => 'Fix', 'icon' => 'stethoscope', 'url' => $fUrlBase . 'troubleshoot'],
    ['id' => 'ai', 'label' => 'AI', 'icon' => 'sparkles', 'url' => $fUrlBase . 'ai'],
    ['id' => 'knowledge', 'label' => 'KB', 'icon' => 'book-open', 'url' => $fUrlBase . 'knowledge'],
    ['id' => 'tickets', 'label' => 'Tickets', 'icon' => 'ticket', 'url' => $fUrlBase . 'tickets'],
];
?>
        </div>
    </div>

    <nav class="bottom-nav">
        <?php foreach ($mainNav as $item): ?>
            <a href="<?= e($item['url']) ?>" class="<?= ($active_menu ?? '') === $item['id'] ? 'active' : '' ?>">
                <i data-lucide="<?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div id="toast-container" style="position:fixed;top:80px;right:12px;left:12px;z-index:200;display:flex;flex-direction:column;gap:8px;pointer-events:none;">
        <div style="pointer-events:auto;"></div>
    </div>

    <div id="search-modal" class="modal-overlay" style="display:none;">
        <div class="backdrop" onclick="closeSearchModal()"></div>
        <div class="modal-panel" style="max-width:560px;margin-top:10vh;">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid #e5e7eb;">
                <i data-lucide="search" style="width:18px;height:18px;color:#94a3b8;"></i>
                <input type="text" id="modal-search-input" placeholder="Search everything..." style="flex:1;background:none;border:none;font-size:15px;outline:none;color:#111827;" class="dark-input">
                <kbd style="font-size:11px;color:#94a3b8;background:#f1f5f9;padding:3px 8px;border-radius:4px;font-family:monospace;">ESC</kbd>
            </div>
            <div id="search-results" style="max-height:60vh;overflow-y:auto;padding:12px;">
                <div class="empty-state" style="padding:32px;"><div class="empty-state-icon"><i data-lucide="search"></i></div><h3>Search the platform</h3><p>Find troubleshooting guides, commands, equipment, and knowledge articles</p></div>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
