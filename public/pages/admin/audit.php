<?php
$page_title = 'Audit Logs';
$active_menu = 'audit';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('audit.view');

?>
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System activity and security audit trail</p>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" placeholder="Search audit logs..." class="w-full pl-9 pr-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
        </div>
        <select class="px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
            <option>All Actions</option><option>LOGIN</option><option>LOGOUT</option><option>CREATE</option><option>EDIT</option><option>DELETE</option><option>APPROVE</option><option>PUBLISH</option>
        </select>
        <select class="px-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
            <option>All Users</option>
        </select>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Timestamp</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Resource</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                $logs = [
                    ['time' => '2026-08-22 14:32:01', 'user' => 'System Admin', 'action' => 'LOGIN', 'resource' => 'auth', 'ip' => '192.168.1.100', 'color' => 'green'],
                    ['time' => '2026-08-22 14:28:45', 'user' => 'Juan Dela Cruz', 'action' => 'LOGIN', 'resource' => 'auth', 'ip' => '192.168.1.105', 'color' => 'green'],
                    ['time' => '2026-08-22 13:15:22', 'user' => 'System Admin', 'action' => 'PUBLISH', 'resource' => 'Knowledge #182', 'ip' => '192.168.1.100', 'color' => 'blue'],
                    ['time' => '2026-08-22 12:45:00', 'user' => 'Maria Santos', 'action' => 'APPROVE', 'resource' => 'Knowledge #179', 'ip' => '192.168.1.110', 'color' => 'blue'],
                    ['time' => '2026-08-22 11:30:15', 'user' => 'Juan Dela Cruz', 'action' => 'CREATE', 'resource' => 'Session #TK-0127', 'ip' => '192.168.1.105', 'color' => 'purple'],
                    ['time' => '2026-08-22 10:15:00', 'user' => 'Unknown', 'action' => 'LOGIN_FAILED', 'resource' => 'auth', 'ip' => '10.0.0.55', 'color' => 'red'],
                    ['time' => '2026-08-22 09:00:00', 'user' => 'System Admin', 'action' => 'CREATE', 'resource' => 'User invitation', 'ip' => '192.168.1.100', 'color' => 'purple'],
                ];
                foreach ($logs as $log): ?>
                    <tr class="table-row">
                        <td class="px-5 py-3 text-xs text-gray-500 font-mono"><?= $log['time'] ?></td>
                        <td class="px-5 py-3 text-sm text-gray-900 dark:text-white"><?= $log['user'] ?></td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $log['color'] ?>-100 dark:bg-<?= $log['color'] ?>-900/20 text-<?= $log['color'] ?>-700 dark:text-<?= $log['color'] ?>-400">
                                <?= $log['action'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-500 hide-mobile"><?= $log['resource'] ?></td>
                        <td class="px-5 py-3 text-xs text-gray-400 font-mono hide-mobile"><?= $log['ip'] ?></td>
                    </tr>
                <?php endforeach; ?>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
