<?php
/**
 * Admin - Users Management
 */
$page_title = 'User Management';
$active_menu = 'admin-users';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('users.manage');

?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage users, roles, and permissions</p>
        </div>
        <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Invite User
        </button>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-gray-900 dark:text-white">4</div>
            <div class="text-xs text-gray-500">Total Users</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-green-600">4</div>
            <div class="text-xs text-gray-500">Active</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-brand-600">5</div>
            <div class="text-xs text-gray-500">Roles</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-xl font-bold text-yellow-600">0</div>
            <div class="text-xs text-gray-500">Pending Invites</div>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="relative flex-1 max-w-sm">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" placeholder="Search users..." class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
            </div>
            <div class="flex gap-2">
                <select class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option>All Roles</option><option>Super Admin</option><option>Admin</option><option>Supervisor</option><option>Field IT</option><option>Standard User</option>
                </select>
                <select class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option>All Departments</option><option>IT</option><option>Finance</option><option>Marketing</option>
                </select>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Role</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Department</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Last Login</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                $users = [
                    ['name' => 'System Admin', 'email' => 'admin@fieldit.local', 'role' => 'Super Admin', 'dept' => 'IT', 'status' => 'active', 'lastLogin' => 'Just now'],
                    ['name' => 'Juan Dela Cruz', 'email' => 'fieldit@fieldit.local', 'role' => 'Field IT', 'dept' => 'IT', 'status' => 'active', 'lastLogin' => '2h ago'],
                    ['name' => 'Maria Santos', 'email' => 'supervisor@fieldit.local', 'role' => 'Supervisor', 'dept' => 'IT', 'status' => 'active', 'lastLogin' => '1h ago'],
                    ['name' => 'Carlo Reyes', 'email' => 'user@fieldit.local', 'role' => 'Standard User', 'dept' => 'Finance', 'status' => 'active', 'lastLogin' => '3d ago'],
                ];
                foreach ($users as $u): ?>
                    <tr class="table-row">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-brand-700 dark:text-brand-300"><?= strtoupper(substr($u['name'], 0, 2)) ?></span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white"><?= $u['name'] ?></div>
                                    <div class="text-xs text-gray-500"><?= $u['email'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-sm text-gray-700 dark:text-gray-300"><?= $u['role'] ?></span></td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-sm text-gray-500"><?= $u['dept'] ?></span></td>
                        <td class="px-5 py-3"><?= status_badge($u['status']) ?></td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-xs text-gray-500"><?= $u['lastLogin'] ?></span></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button class="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded transition" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition" title="View Permissions">
                                    <i data-lucide="shield" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
