<?php
$page_title = 'Departments';
$active_menu = 'admin-departments';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('departments.manage');
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Departments</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage organizational departments and teams</p></div>
        <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> Add Department</button>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
        $depts = [
            ['name' => 'IT Department', 'users' => 3, 'contacts' => 5, 'desc' => 'Information Technology'],
            ['name' => 'Finance', 'users' => 12, 'contacts' => 2, 'desc' => 'Finance and Accounting'],
            ['name' => 'Marketing', 'users' => 8, 'contacts' => 2, 'desc' => 'Marketing and Sales'],
            ['name' => 'HR', 'users' => 5, 'contacts' => 2, 'desc' => 'Human Resources'],
            ['name' => 'Operations', 'users' => 15, 'contacts' => 3, 'desc' => 'Operations'],
            ['name' => 'Security', 'users' => 4, 'contacts' => 2, 'desc' => 'Security and Facilities'],
        ];
        foreach ($depts as $d): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-brand-300 transition">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1"><?= $d['name'] ?></h3>
                <p class="text-sm text-gray-500 mb-3"><?= $d['desc'] ?></p>
                <div class="flex gap-4 text-xs text-gray-500">
                    <span>👥 <?= $d['users'] ?> users</span><span>📞 <?= $d['contacts'] ?> contacts</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
