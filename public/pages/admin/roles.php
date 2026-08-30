<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Roles & Permissions';
$active_menu = 'admin-roles';
$required_permission = 'roles.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';

?>

<div class="max-w-6xl mx-auto">
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico violet"><i data-lucide="shield"></i></div>
                <div>
                    <h1 class="page-hero-title">Roles &amp; Permissions</h1>
                    <p class="page-hero-sub">Configure role-based access control</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Roles List -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <?php
        $roles = [
            ['name' => 'Super Admin', 'desc' => 'Full system access', 'users' => 1, 'color' => 'red', 'icon' => 'crown'],
            ['name' => 'Admin', 'desc' => 'Manage content and users', 'users' => 0, 'color' => 'purple', 'icon' => 'shield'],
            ['name' => 'Supervisor', 'desc' => 'Department management', 'users' => 1, 'color' => 'blue', 'icon' => 'briefcase'],
            ['name' => 'Field IT', 'desc' => 'Troubleshoot and document', 'users' => 1, 'color' => 'green', 'icon' => 'wrench'],
            ['name' => 'Standard User', 'desc' => 'View and request support', 'users' => 1, 'color' => 'gray', 'icon' => 'user'],
        ];
        foreach ($roles as $r): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-brand-300 transition cursor-pointer">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-<?= $r['color'] ?>-100 dark:bg-<?= $r['color'] ?>-900/20 flex items-center justify-center">
                        <i data-lucide="<?= $r['icon'] ?>" class="w-5 h-5 text-<?= $r['color'] ?>-600 dark:text-<?= $r['color'] ?>-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white"><?= $r['name'] ?></h3>
                        <p class="text-xs text-gray-500"><?= $r['users'] ?> users assigned</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?= $r['desc'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Permission Matrix -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Permission Matrix</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase min-w-[200px]">Module</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Super Admin</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Admin</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Supervisor</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Field IT</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <?php
                    $modules = [
                        ['name' => 'Troubleshooting', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'Knowledge Base', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'Equipment', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'Commands', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'AI Assistant', 'perms' => [1, 1, 1, 1, 0]],
                        ['name' => 'Tickets', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'Documentation', 'perms' => [1, 1, 1, 1, 0]],
                        ['name' => 'Team Chat', 'perms' => [1, 1, 1, 1, 1]],
                        ['name' => 'User Management', 'perms' => [1, 1, 0, 0, 0]],
                        ['name' => 'Roles & Permissions', 'perms' => [1, 0, 0, 0, 0]],
                        ['name' => 'Departments', 'perms' => [1, 1, 1, 0, 0]],
                        ['name' => 'Contacts', 'perms' => [1, 1, 1, 0, 0]],
                        ['name' => 'Audit Logs', 'perms' => [1, 1, 0, 0, 0]],
                        ['name' => 'System Settings', 'perms' => [1, 0, 0, 0, 0]],
                    ];
                    foreach ($modules as $mod): ?>
                        <tr class="table-row">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white"><?= $mod['name'] ?></td>
                            <?php foreach ($mod['perms'] as $has): ?>
                                <td class="px-3 py-3 text-center">
                                    <?php if ($has): ?>
                                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"><i data-lucide="check" class="w-3.5 h-3.5 text-green-600"></i></span>
                                    <?php else: ?>
                                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700"><i data-lucide="x" class="w-3.5 h-3.5 text-gray-400"></i></span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
