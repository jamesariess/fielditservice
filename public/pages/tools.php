<?php
$page_title = 'Tools & Equipment Reference';
$active_menu = 'tools';
require APP_ROOT . '/includes/layout_header.php';
$tools = Database::fetchAll('SELECT * FROM tools ORDER BY id');
?>


<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tools & Equipment Reference</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Essential tools for field IT technicians</p>
    </div>
    
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($tools as $tool): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center mb-3">
                    <i data-lucide="<?= e($tool['icon'] ?? 'wrench') ?>" class="w-6 h-6 text-brand-600 dark:text-brand-400"></i>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2"><?= e($tool['name']) ?></h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Purpose:</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-1"><?= e($tool['purpose'] ?? '') ?></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">When to use:</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-1"><?= e($tool['when_to_use'] ?? '') ?></span>
                    </div>
                    <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Safety:</span>
                        <span class="text-xs text-amber-600 dark:text-amber-300 ml-1"><?= e($tool['safety'] ?? '') ?></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                        <span class="text-xs text-brand-600 dark:text-brand-400 ml-1"><?= e($tool['category'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
