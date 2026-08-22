<?php
$page_title = 'Equipment Management';
$active_menu = 'admin-equipment';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('equipment.manage');
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Equipment Management</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage device types, manufacturers, models, and parts</p></div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> Add Model</button>
        </div>
    </div>
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">10</div><div class="text-xs text-gray-500">Manufacturers</div></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">14</div><div class="text-xs text-gray-500">Device Types</div></div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">48</div><div class="text-xs text-gray-500">Models</div></div>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">Equipment management interface — add, edit, and organize device models, parts, and repair guides.</p>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
