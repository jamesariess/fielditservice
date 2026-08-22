<?php
/**
 * Main Application Layout
 * Pages: require_once this at top, call layout_footer() at bottom
 */

// Start output buffer to capture page content
if (ob_get_level() === 0) {
    ob_start();
}

$currentUser = [
    'name' => Auth::userName() ?? 'Guest',
    'role' => $_SESSION['role_name'] ?? 'User',
];

$mainNav = [
    ['id' => 'dashboard', 'label' => 'Home', 'icon' => 'layout-dashboard', 'url' => '/'],
    ['id' => 'troubleshoot', 'label' => 'Fix', 'icon' => 'stethoscope', 'url' => '/troubleshoot'],
    ['id' => 'ai', 'label' => 'AI', 'icon' => 'sparkles', 'url' => '/ai'],
    ['id' => 'knowledge', 'label' => 'KB', 'icon' => 'book-open', 'url' => '/knowledge'],
    ['id' => 'tickets', 'label' => 'Tickets', 'icon' => 'ticket', 'url' => '/tickets'],
];

$sidebarItems = [
    ['section' => 'Main'],
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'url' => '/'],
    ['id' => 'troubleshoot', 'label' => 'Troubleshoot', 'icon' => 'stethoscope', 'url' => '/troubleshoot'],
    ['id' => 'ai', 'label' => 'IT Support AI', 'icon' => 'sparkles', 'url' => '/ai'],
    ['section' => 'Resources'],
    ['id' => 'knowledge', 'label' => 'Knowledge Base', 'icon' => 'book-open', 'url' => '/knowledge'],
    ['id' => 'equipment', 'label' => 'Equipment', 'icon' => 'package', 'url' => '/equipment'],
    ['id' => 'commands', 'label' => 'Commands', 'icon' => 'terminal', 'url' => '/commands'],
    ['id' => 'tools', 'label' => 'Tools', 'icon' => 'wrench', 'url' => '/tools'],
    ['section' => 'Work'],
    ['id' => 'tickets', 'label' => 'My Tickets', 'icon' => 'ticket', 'url' => '/tickets', 'badge' => '5'],
    ['id' => 'documentation', 'label' => 'Documentation', 'icon' => 'file-text', 'url' => '/documentation'],
    ['id' => 'chat', 'label' => 'Team Chat', 'icon' => 'messages-square', 'url' => '/chat', 'badge' => '3'],
    ['section' => 'Administration', 'perm' => 'users.manage'],
    ['id' => 'admin-users', 'label' => 'Users', 'icon' => 'users', 'url' => '/admin/users', 'perm' => 'users.manage'],
    ['id' => 'admin-roles', 'label' => 'Roles & Permissions', 'icon' => 'shield', 'url' => '/admin/roles', 'perm' => 'roles.manage'],
    ['id' => 'admin-departments', 'label' => 'Departments', 'icon' => 'building-2', 'url' => '/admin/departments', 'perm' => 'departments.manage'],
    ['id' => 'admin-kb', 'label' => 'KB Management', 'icon' => 'file-check', 'url' => '/admin/knowledge', 'perm' => 'knowledge.manage'],
    ['id' => 'admin-equipment', 'label' => 'Equipment Mgmt', 'icon' => 'settings', 'url' => '/admin/equipment', 'perm' => 'equipment.manage'],
    ['id' => 'admin-statistics', 'label' => 'Statistics', 'icon' => 'bar-chart-3', 'url' => '/admin/statistics', 'perm' => 'audit.view'],
    ['id' => 'audit', 'label' => 'Audit Logs', 'icon' => 'scroll-text', 'url' => '/admin/audit', 'perm' => 'audit.view'],
    ['id' => 'admin-ai', 'label' => 'AI Settings', 'icon' => 'bot', 'url' => '/admin/ai', 'perm' => 'ai.manage'],
    ['id' => 'admin-settings', 'label' => 'System Settings', 'icon' => 'settings-2', 'url' => '/admin/settings', 'perm' => 'system.settings'],
];

$filteredSidebar = [];
foreach ($sidebarItems as $item) {
    if (isset($item['section'])) {
        if (!isset($item['perm']) || Auth::hasPermission($item['perm'])) {
            $filteredSidebar[] = $item;
        }
        continue;
    }
    if (empty($item['perm']) || Auth::hasPermission($item['perm'])) {
        $filteredSidebar[] = $item;
    }
}

$initials = '';
foreach (explode(' ', $currentUser['name']) as $p) { $initials .= strtoupper(substr($p, 0, 1)); if (strlen($initials) >= 2) break; }

/**
 * Layout Footer — call this at the end of every page file.
 * Captures page content from buffer and outputs closing HTML.
 */
function layout_footer() {
    global $mainNav, $active_menu;
    $page_content = ob_get_clean();
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

    <script src="/assets/js/app.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
<?php
    echo $page_content;
}

// Output header HTML (page content will be buffered after this)
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?= Auth::generateCsrfToken() ?>">
    <meta name="theme-color" content="#1e40af">
    <title><?= e($page_title ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'] }, colors: { brand: {50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' } } } }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .bottom-nav { display:none; position:fixed; bottom:0; left:0; right:0; z-index:60; background:rgba(255,255,255,0.95); backdrop-filter:blur(12px); border-top:1px solid #e5e7eb; padding:6px 0 env(safe-area-inset-bottom,6px); }
        .bottom-nav a { flex:1; display:flex; flex-direction:column; align-items:center; gap:2px; padding:6px 4px; font-size:10px; font-weight:600; color:#94a3b8; text-decoration:none; transition:color 0.15s; }
        .bottom-nav a.active { color:#2563eb; }
        .bottom-nav a i { width:20px; height:20px; }
        @media (max-width:767px) {
            .bottom-nav { display:flex; }
            .app-main { margin-left:0 !important; padding-bottom:72px; }
            .hide-mobile-nav { display:none !important; }
        }
        .sidebar-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:40; opacity:0; pointer-events:none; transition:opacity 0.25s; }
        .sidebar-overlay.active { opacity:1; pointer-events:auto; }
        @media (min-width:768px) and (max-width:1023px) {
            .sidebar { transform:translateX(-100%); z-index:50; }
            .sidebar.open { transform:translateX(0); box-shadow:0 0 40px rgba(0,0,0,0.2); }
            .app-main { margin-left:0 !important; }
        }
        @media (min-width:1024px) {
            .sidebar { transform:translateX(0); }
            .app-main { margin-left:var(--sidebar-width); }
        }
    </style>
</head>
<body class="h-full">
    <div id="sidebar-overlay" class="sidebar-overlay" onclick="closeSidebar()"></div>
    <aside id="sidebar" class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i data-lucide="headphones"></i></div>
            <div><div class="sidebar-logo-text">Field IT Hub</div><div class="sidebar-logo-sub">Support Platform</div></div>
        </div>
        <nav class="sidebar-nav custom-scroll">
            <?php foreach ($filteredSidebar as $item): ?>
                <?php if (isset($item['section'])): ?>
                    <div class="sidebar-section-label" style="margin-top:8px;"><?= $item['section'] ?></div>
                    <?php continue; ?>
                <?php endif; ?>
                <a href="<?= e($item['url']) ?>" class="sidebar-link <?= ($active_menu ?? '') === $item['id'] ? 'active' : '' ?>">
                    <i data-lucide="<?= $item['icon'] ?>"></i>
                    <span><?= e($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?><span class="badge"><?= $item['badge'] ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= $initials ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= e($currentUser['name']) ?></div>
                <div class="sidebar-user-role"><?= e($currentUser['role']) ?></div>
            </div>
            <a href="/logout" class="header-btn" data-tooltip="Logout"><i data-lucide="log-out" style="width:16px;height:16px;"></i></a>
        </div>
    </aside>
    <div class="app-main">
        <header class="app-header">
            <button onclick="openSidebar()" class="header-btn hide-desktop"><i data-lucide="menu" style="width:20px;height:20px;"></i></button>
            <div class="header-search hide-mobile-nav">
                <i data-lucide="search"></i>
                <input type="text" id="global-search" placeholder="Search anything..." onkeyup="handleGlobalSearch(this.value)">
                <span class="header-kbd">Ctrl+K</span>
            </div>
            <div class="header-actions">
                <button onclick="toggleDarkMode()" class="header-btn" data-tooltip="Theme">
                    <i data-lucide="sun" style="width:18px;height:18px;" class="dark:hidden"></i>
                    <i data-lucide="moon" style="width:18px;height:18px;" class="hidden dark:block"></i>
                </button>
                <button class="header-btn" data-tooltip="Notifications"><i data-lucide="bell" style="width:18px;height:18px;"></i><span class="dot"></span></button>
            </div>
        </header>
        <div class="page-content">
<?php
// Page content goes here (echoed by page files), then layout_footer() closes the page.
// Note: ob_start() at the top captures everything the page echoes until layout_footer() calls ob_get_clean().
?>
