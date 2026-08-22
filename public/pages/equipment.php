<?php
/**
 * Equipment Database
 */
$page_title = 'Equipment Database';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Equipment Database</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Browse devices, models, specifications, and repair guides</p>
        </div>
        <?php if (Auth::hasPermission('equipment.create')): ?>
            <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Equipment
            </button>
        <?php endif; ?>
    </div>
    
    <!-- Search -->
    <div class="mb-6">
        <div class="relative max-w-xl">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" placeholder="Search by model, manufacturer, or serial number..." 
                   class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500">
        </div>
    </div>
    
    <!-- Device Categories -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-10">
        <?php
        $deviceTypes = [
            ['icon' => 'laptop', 'label' => 'Laptops', 'count' => 156],
            ['icon' => 'desktop', 'label' => 'Desktops', 'count' => 203],
            ['icon' => 'server', 'label' => 'Servers', 'count' => 24],
            ['icon' => 'monitor', 'label' => 'Monitors', 'count' => 89],
            ['icon' => 'printer', 'label' => 'Printers', 'count' => 67],
            ['icon' => 'router', 'label' => 'Routers', 'count' => 18],
            ['icon' => 'network', 'label' => 'Switches', 'count' => 32],
            ['icon' => 'wifi', 'label' => 'Access Points', 'count' => 28],
            ['icon' => 'camera', 'label' => 'CCTV Cameras', 'count' => 145],
            ['icon' => 'hard-drive', 'label' => 'DVR/NVR', 'count' => 12],
        ];
        foreach ($deviceTypes as $dt): ?>
            <div class="card-hover bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 cursor-pointer hover:border-brand-300 transition text-center">
                <i data-lucide="<?= $dt['icon'] ?>" class="w-8 h-8 text-brand-600 dark:text-brand-400 mx-auto mb-2"></i>
                <div class="text-sm font-semibold text-gray-900 dark:text-white"><?= $dt['label'] ?></div>
                <div class="text-xs text-gray-500 mt-0.5"><?= $dt['count'] ?> models</div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Popular Models -->
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular Models</h2>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Manufacturer</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Model</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Known Issues</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Guides</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                $models = [
                    ['mfg' => 'Lenovo', 'model' => 'ThinkPad T14 Gen 3', 'type' => 'Laptop', 'issues' => 3, 'guides' => 5],
                    ['mfg' => 'Dell', 'model' => 'OptiPlex 7090', 'type' => 'Desktop', 'issues' => 2, 'guides' => 4],
                    ['mfg' => 'HP', 'model' => 'LaserJet Pro M404dn', 'type' => 'Printer', 'issues' => 5, 'guides' => 6],
                    ['mfg' => 'Dell', 'model' => 'Latitude 5520', 'type' => 'Laptop', 'issues' => 4, 'guides' => 5],
                    ['mfg' => 'HP', 'model' => 'ProBook 450 G9', 'type' => 'Laptop', 'issues' => 2, 'guides' => 3],
                    ['mfg' => 'Lenovo', 'model' => 'ThinkCentre M70s', 'type' => 'Desktop', 'issues' => 1, 'guides' => 3],
                    ['mfg' => 'Cisco', 'model' => 'Catalyst 2960', 'type' => 'Switch', 'issues' => 2, 'guides' => 4],
                    ['mfg' => 'Hikvision', 'model' => 'DS-2CD2143G2-I', 'type' => 'CCTV Camera', 'issues' => 1, 'guides' => 2],
                ];
                foreach ($models as $m): ?>
                    <tr class="table-row cursor-pointer" onclick="window.location=APP_BASE+'equipment/model.php'">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400"><?= substr($m['mfg'], 0, 2) ?></span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"><?= $m['mfg'] ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300"><?= $m['model'] ?></td>
                        <td class="px-5 py-3 hide-mobile">
                            <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-full text-gray-600 dark:text-gray-400"><?= $m['type'] ?></span>
                        </td>
                        <td class="px-5 py-3 hide-mobile text-sm text-gray-500"><?= $m['issues'] ?> known</td>
                        <td class="px-5 py-3 text-sm text-brand-600 font-medium"><?= $m['guides'] ?> guides</td>
                    </tr>
                <?php endforeach; ?>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
