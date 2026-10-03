<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'ThinkPad T14 Gen 3';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';
?>
<div class="max-w-5xl mx-auto">
    <div class="fx-reveal">
        <a href="<?= $urlBase ?>equipment" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 mb-6 transition"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Equipment</a>
    </div>
    
    <!-- Model Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6 fx-reveal" style="--fx-delay:50ms;">
        <div class="flex items-start gap-6">
            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="laptop" class="w-12 h-12 text-gray-400"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-xs rounded-full">Laptop</span>
                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs rounded-full">3 Known Issues</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lenovo ThinkPad T14 Gen 3</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">AMD Ryzen 5/7 Pro | 14" FHD IPS | DDR4 | NVMe SSD</p>
                
                <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <span>📋 5 Repair Guides</span>
                    <span>🔧 8 Compatible Parts</span>
                    <span>📖 3 Service Manuals</span>
                    <span>🎬 2 Videos</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg whitespace-nowrap">Specifications</button>
        <button class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg whitespace-nowrap">Parts</button>
        <button class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg whitespace-nowrap">Repair Guides</button>
        <button class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg whitespace-nowrap">Known Issues</button>
        <button class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg whitespace-nowrap">Videos</button>
    </div>
    
    <!-- Specifications -->
    <div class="grid sm:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3">General</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Manufacturer</dt><dd class="text-gray-900 dark:text-white font-medium">Lenovo</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd class="text-gray-900 dark:text-white font-medium">ThinkPad T14 Gen 3</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="text-gray-900 dark:text-white font-medium">Business Laptop</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Year</dt><dd class="text-gray-900 dark:text-white font-medium">2022</dd></div>
            </dl>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Display</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Size</dt><dd class="text-gray-900 dark:text-white font-medium">14 inches</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Resolution</dt><dd class="text-gray-900 dark:text-white font-medium">1920 × 1080 (FHD)</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Panel</dt><dd class="text-gray-900 dark:text-white font-medium">IPS, Anti-glare</dd></div>
            </dl>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Internal</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">CPU</dt><dd class="text-gray-900 dark:text-white font-medium">AMD Ryzen 5 Pro 6650U / Ryzen 7 Pro 6850U</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">RAM</dt><dd class="text-gray-900 dark:text-white font-medium">DDR4-3200, 16/32 GB (soldered)</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Storage</dt><dd class="text-gray-900 dark:text-white font-medium">M.2 2280 NVMe SSD</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">GPU</dt><dd class="text-gray-900 dark:text-white font-medium">Integrated AMD Radeon 660M/680M</dd></div>
            </dl>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Ports</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">USB-A</dt><dd class="text-gray-900 dark:text-white font-medium">2 × USB 3.2 Gen 1</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">USB-C</dt><dd class="text-gray-900 dark:text-white font-medium">2 × USB-C 3.2 (PD + DP)</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">HDMI</dt><dd class="text-gray-900 dark:text-white font-medium">1 × HDMI 2.0b</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Audio</dt><dd class="text-gray-900 dark:text-white font-medium">3.5mm combo jack</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ethernet</dt><dd class="text-gray-900 dark:text-white font-medium">RJ-45 (with extendable port)</dd></div>
            </dl>
        </div>
    </div>
    
    <!-- Required Tools -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Required Tools for Repair</h2>
        <div class="flex flex-wrap gap-2">
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full">Phillips PH0 Screwdriver</span>
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full">Plastic Pry Tool</span>
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full">ESD Wrist Strap</span>
            <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-full">Tweezers</span>
        </div>
    </div>
    
    <!-- Known Issues -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Known Issues</h2>
        <div class="space-y-3">
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">Battery Swelling</h3>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Some units may experience battery swelling. Check battery condition during any service. Use approved replacement battery only.</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">WiFi Disconnects</h3>
                <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">Update WiFi driver from Lenovo support site. Some units shipped with driver versions that cause intermittent disconnects.</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Thunderbolt Firmware Update</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Apply latest Thunderbolt firmware update to prevent potential dock connectivity issues.</p>
            </div>
        </div>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
