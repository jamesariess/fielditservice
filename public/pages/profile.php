<?php
$page_title = 'Profile';
$active_menu = 'profile';
require APP_ROOT . '/includes/layout_header.php';
?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h1>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                <span class="text-xl font-bold text-brand-700 dark:text-brand-300"><?= strtoupper(substr(Auth::userName(), 0, 2)) ?></span>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?= e(Auth::userName()) ?></h2>
                <p class="text-sm text-gray-500"><?= e($_SESSION['role_name'] ?? 'User') ?></p>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                <input type="text" value="<?= e(Auth::userName()) ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" value="<?= e(Auth::userEmail() ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500" disabled>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                <input type="text" value="<?= e($_SESSION['department_name'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-700" disabled>
            </div>
        </div>
        <button class="mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">Save Changes</button>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
