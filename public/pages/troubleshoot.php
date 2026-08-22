<?php
/**
 * Troubleshooting - Category Selection & Issue Browser
 */
$page_title = 'Troubleshoot';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';

$query = $_GET['q'] ?? '';

?>

<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Troubleshooting</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a category or describe your problem to start guided troubleshooting</p>
    </div>
    
    <!-- Search Bar -->
    <div class="mb-8 max-w-2xl">
        <div class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
            <input type="text" id="troubleshoot-search"
                   value="<?= e($query) ?>"
                   placeholder='Describe the issue — e.g. "No Display", "Cannot print", "WiFi not working"...'
                   class="w-full pl-12 pr-4 py-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-base shadow-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                   onkeyup="filterIssues(this.value)">
        </div>
    </div>
    
    <!-- Category Cards -->
    <div class="mb-10">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Category</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="category-grid">
            <?php
            $troubleshootCategories = [
                [
                    'icon' => 'monitor',
                    'title' => 'No Display',
                    'desc' => 'Black screen, no signal, monitor not working',
                    'severity' => 'high',
                    'url' => '/troubleshoot/wizard.php?issue=no-display',
                    'tags' => ['display', 'monitor', 'screen', 'black screen', 'no signal', 'hdmi', 'displayport', 'vga'],
                ],
                [
                    'icon' => 'power',
                    'title' => 'No Power',
                    'desc' => 'Computer won\'t turn on, dead, no lights',
                    'severity' => 'high',
                    'url' => '/troubleshoot/wizard.php?issue=no-power',
                    'tags' => ['power', 'turn on', 'dead', 'psu', 'adapter', 'battery', 'charger'],
                ],
                [
                    'icon' => 'volume-x',
                    'title' => 'No Sound',
                    'desc' => 'No audio, speakers not working, muted',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=no-sound',
                    'tags' => ['sound', 'audio', 'speakers', 'headphones', 'mute', 'volume'],
                ],
                [
                    'icon' => 'wifi',
                    'title' => 'Network Issues',
                    'desc' => 'No internet, slow connection, WiFi problems',
                    'severity' => 'high',
                    'url' => '/troubleshoot/wizard.php?issue=network',
                    'tags' => ['network', 'internet', 'wifi', 'ethernet', 'lan', 'connection', 'slow'],
                ],
                [
                    'icon' => 'printer',
                    'title' => 'Printer Issues',
                    'desc' => 'Offline, paper jam, poor quality, not detected',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=printer',
                    'tags' => ['printer', 'print', 'paper jam', 'offline', 'toner', 'ink', 'spooler'],
                ],
                [
                    'icon' => 'camera',
                    'title' => 'CCTV Issues',
                    'desc' => 'Camera offline, no recording, DVR/NVR problems',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=cctv',
                    'tags' => ['cctv', 'camera', 'dvr', 'nvr', 'recording', 'surveillance', 'poe'],
                ],
                [
                    'icon' => 'alert-triangle',
                    'title' => 'Blue Screen (BSOD)',
                    'desc' => 'Windows crash, blue screen errors',
                    'severity' => 'high',
                    'url' => '/troubleshoot/wizard.php?issue=bsod',
                    'tags' => ['blue screen', 'bsod', 'crash', 'windows', 'stop error'],
                ],
                [
                    'icon' => 'gauge',
                    'title' => 'Slow Performance',
                    'desc' => 'Laggy, slow boot, high resource usage',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=slow',
                    'tags' => ['slow', 'lag', 'performance', 'cpu', 'ram', 'disk', 'boot'],
                ],
                [
                    'icon' => 'app-window',
                    'title' => 'Application Issues',
                    'desc' => 'App crash, won\'t open, not responding',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=app-issue',
                    'tags' => ['application', 'app', 'crash', 'not responding', 'hang', 'freeze'],
                ],
                [
                    'icon' => 'key',
                    'title' => 'Login Problems',
                    'desc' => 'Cannot log in, password reset, account locked',
                    'severity' => 'medium',
                    'url' => '/troubleshoot/wizard.php?issue=login',
                    'tags' => ['login', 'password', 'locked', 'account', 'domain', 'authentication'],
                ],
                [
                    'icon' => 'hard-drive',
                    'title' => 'Disk / Storage',
                    'desc' => 'Disk errors, full drive, corruption',
                    'severity' => 'high',
                    'url' => '/troubleshoot/wizard.php?issue=disk',
                    'tags' => ['disk', 'storage', 'drive', 'ssd', 'hdd', 'corruption', 'full'],
                ],
                [
                    'icon' => 'help-circle',
                    'title' => 'Help Me Diagnose',
                    'desc' => 'Not sure what\'s wrong? Let us help you figure it out',
                    'severity' => 'info',
                    'url' => '/troubleshoot/wizard.php?issue=diagnose',
                    'tags' => ['help', 'diagnose', 'unknown', 'not sure', 'problem'],
                ],
            ];
            
            foreach ($troubleshootCategories as $cat): ?>
                <a href="<?= $cat['url'] ?>" 
                   class="card-hover group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-brand-300 dark:hover:border-brand-700 transition"
                   data-tags="<?= e(implode(',', $cat['tags'])) ?>"
                   data-title="<?= e(strtolower($cat['title'])) ?>"
                   data-desc="<?= e(strtolower($cat['desc'])) ?>">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                            <i data-lucide="<?= $cat['icon'] ?>" class="w-6 h-6 text-brand-600 dark:text-brand-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition"><?= $cat['title'] ?></h3>
                                <?php if ($cat['severity'] === 'high'): ?>
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <?php elseif ($cat['severity'] === 'medium'): ?>
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= $cat['desc'] ?></p>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 dark:text-gray-600 shrink-0 group-hover:text-brand-500 transition mt-1"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- I Don't Know Section -->
    <div class="bg-gradient-to-r from-brand-50 to-blue-50 dark:from-brand-900/10 dark:to-blue-900/10 rounded-xl border border-brand-200 dark:border-brand-800 p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-100 dark:bg-brand-800/50 flex items-center justify-center shrink-0">
                <i data-lucide="bot" class="w-6 h-6 text-brand-600 dark:text-brand-400"></i>
            </div>
            <div>
                <h3 class="font-semibold text-brand-900 dark:text-brand-200 mb-1">Not sure what's wrong?</h3>
                <p class="text-sm text-brand-700 dark:text-brand-300 mb-3">
                    Our IT Support AI can help you diagnose the issue step by step. Just describe what you're seeing.
                </p>
                <a href="/ai.php" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                    <i data-lucide="bot" class="w-4 h-4"></i>
                    Ask IT Support AI
                </a>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
