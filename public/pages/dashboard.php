<?php
$page_title = 'Dashboard';
$active_menu = 'dashboard';
require APP_ROOT . '/includes/layout_header.php';
?>

<!-- Hero Search Section -->
<div class="mb-10">
    <h1 class="text-[28px] font-extrabold text-gray-900 dark:text-white tracking-tight mb-1">
        Welcome back, <?= e(Auth::userName()) ?>
        <span class="inline-block ml-1">👋</span>
    </h1>
    <p class="text-[15px] text-gray-500 dark:text-gray-400 mb-6">What can we help you with today?</p>

    <div class="max-w-2xl">
        <div class="relative">
            <i data-lucide="search" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);width:20px;height:20px;color:#94a3b8;"></i>
            <input type="text" id="hero-search"
                   placeholder='Describe your problem — e.g. "No Display", "Cannot print", "Network slow"...'
                   style="width:100%;padding:14px 140px 14px 48px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;font-size:15px;box-shadow:0 1px 3px rgba(0,0,0,0.06);transition:all 0.15s;"
                   class="dark-input"
                   onfocus="this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)';this.style.borderColor='#2563eb'"
                   onblur="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.06)';this.style.borderColor='#e5e7eb'"
                   onkeydown="if(event.key==='Enter') window.location.href=APP_BASE+'troubleshoot?q='+encodeURIComponent(this.value)">
            <button onclick="window.location.href=APP_BASE+'troubleshoot?q='+encodeURIComponent(document.getElementById('hero-search').value)"
                    style="position:absolute;right:6px;top:50%;transform:translateY(-50%);padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background 0.15s;"
                    onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                <i data-lucide="zap" style="width:14px;height:14px;"></i>
                Troubleshoot
            </button>
        </div>
    </div>
</div>

<!-- Quick Troubleshooting -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-[17px] font-bold text-gray-900 dark:text-white">Quick Troubleshooting</h2>
        <a href="<?= $urlBase ?>troubleshoot" class="text-[13px] font-semibold text-brand-600 hover:text-brand-700 transition">View all <i data-lucide="arrow-right" style="width:13px;height:13px;display:inline;"></i></a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <?php
        $categories = [
            ['icon' => 'monitor', 'label' => 'Display', 'query' => 'no display', 'bg' => '#eff6ff', 'fg' => '#2563eb'],
            ['icon' => 'power', 'label' => 'Power', 'query' => 'no power', 'bg' => '#fef2f2', 'fg' => '#dc2626'],
            ['icon' => 'volume-2', 'label' => 'Sound', 'query' => 'no sound', 'bg' => '#faf5ff', 'fg' => '#9333ea'],
            ['icon' => 'wifi', 'label' => 'Network', 'query' => 'network issue', 'bg' => '#f0fdf4', 'fg' => '#16a34a'],
            ['icon' => 'printer', 'label' => 'Printer', 'query' => 'printer', 'bg' => '#fff7ed', 'fg' => '#ea580c'],
            ['icon' => 'camera', 'label' => 'CCTV', 'query' => 'cctv', 'bg' => '#f8fafc', 'fg' => '#475569'],
            ['icon' => 'server', 'label' => 'Server', 'query' => 'server', 'bg' => '#eef2ff', 'fg' => '#4f46e5'],
            ['icon' => 'laptop', 'label' => 'Laptop', 'query' => 'laptop', 'bg' => '#ecfdf5', 'fg' => '#059669'],
            ['icon' => 'desktop', 'label' => 'Desktop', 'query' => 'desktop', 'bg' => '#f0f9ff', 'fg' => '#0284c7'],
            ['icon' => 'credit-card', 'label' => 'POS', 'query' => 'pos', 'bg' => '#fdf2f8', 'fg' => '#db2777'],
            ['icon' => 'monitor-speaker', 'label' => 'Monitor', 'query' => 'monitor', 'bg' => '#f5f3ff', 'fg' => '#7c3aed'],
            ['icon' => 'smartphone', 'label' => 'Other', 'query' => 'other device', 'bg' => '#f1f5f9', 'fg' => '#64748b'],
        ];
        foreach ($categories as $cat): ?>
            <a href="<?= $urlBase ?>troubleshoot?q=<?= urlencode($cat['query']) ?>" class="quick-card" style="border-color:#e5e7eb;">
                <div class="quick-card-icon" style="background:<?= $cat['bg'] ?>;color:<?= $cat['fg'] ?>;">
                    <i data-lucide="<?= $cat['icon'] ?>"></i>
                </div>
                <span class="quick-card-label"><?= $cat['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <?php
    $stats = [
        ['icon' => 'activity', 'label' => 'Total Sessions', 'value' => '127', 'change' => '+12', 'up' => true, 'color' => 'blue'],
        ['icon' => 'check-circle-2', 'label' => 'Solved Today', 'value' => '8', 'change' => '+3', 'up' => true, 'color' => 'green'],
        ['icon' => 'clock-3', 'label' => 'Pending Tickets', 'value' => '5', 'change' => '-2', 'up' => false, 'color' => 'yellow'],
        ['icon' => 'book-open', 'label' => 'KB Articles', 'value' => '342', 'change' => '+5', 'up' => true, 'color' => 'purple'],
    ];
    foreach ($stats as $s): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $s['color'] ?>">
                <i data-lucide="<?= $s['icon'] ?>" style="width:24px;height:24px;"></i>
            </div>
            <div style="flex:1;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-value"><?= $s['value'] ?></div>
                        <div class="stat-label"><?= $s['label'] ?></div>
                    </div>
                    <span class="stat-change <?= $s['up'] ? 'up' : 'down' ?>"><?= $s['change'] ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Two Column Layout -->
<div class="grid lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Recent Troubleshooting -->
        <div class="card">
            <div class="card-header flex items-center justify-between pb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#64748b;"></i>
                    <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Recent Troubleshooting</h3>
                </div>
                <a href="<?= $urlBase ?>tickets" class="text-[13px] font-semibold text-brand-600 hover:text-brand-700">View all</a>
            </div>
            <div class="card-body" style="padding-top:0;">
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <?php
                    $recent = [
                        ['title' => 'No Display — Desktop PC', 'status' => 'Solved', 'badge' => 'badge-green', 'user' => 'Juan D.', 'time' => '2h ago', 'device' => 'Dell OptiPlex 7090'],
                        ['title' => 'Network Slow — Floor 3', 'status' => 'In Progress', 'badge' => 'badge-yellow', 'user' => 'Maria S.', 'time' => '4h ago', 'device' => 'HP ProBook 450'],
                        ['title' => 'Printer Offline — Reception', 'status' => 'Escalated', 'badge' => 'badge-red', 'user' => 'You', 'time' => '6h ago', 'device' => 'HP LaserJet Pro M404'],
                        ['title' => 'No Sound — Meeting Room', 'status' => 'Solved', 'badge' => 'badge-green', 'user' => 'Carlos R.', 'time' => '1d ago', 'device' => 'Lenovo ThinkCentre M70s'],
                        ['title' => 'WiFi Not Connecting', 'status' => 'In Progress', 'badge' => 'badge-yellow', 'user' => 'Ana T.', 'time' => '1d ago', 'device' => 'Dell Latitude 5520'],
                    ];
                    foreach ($recent as $i => $r): ?>
                        <a href="<?= $urlBase ?>troubleshoot/wizard?issue=no-display" class="flex items-center gap-4 py-3.5 -mx-1 px-1 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group" style="text-decoration:none;">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                                <i data-lucide="<?= $i === 0 ? 'monitor' : ($i === 1 ? 'wifi' : ($i === 2 ? 'printer' : ($i === 3 ? 'volume-2' : 'wifi'))) ?>" style="width:18px;height:18px;color:#64748b;"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="text-[13px] font-semibold text-gray-900 dark:text-white truncate"><?= $r['title'] ?></div>
                                <div class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5"><?= $r['device'] ?> · <?= $r['user'] ?></div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="badge <?= $r['badge'] ?>"><?= $r['status'] ?></span>
                                <span class="text-[12px] text-gray-400 hide-mobile"><?= $r['time'] ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Issues -->
        <div class="card">
            <div class="card-header flex items-center gap-2 pb-4">
                <i data-lucide="trending-up" style="width:16px;height:16px;color:#64748b;"></i>
                <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Most Common Issues This Week</h3>
            </div>
            <div class="card-body" style="padding-top:0;">
                <div class="space-y-4">
                    <?php
                    $topIssues = [
                        ['issue' => 'No Display', 'count' => 18, 'pct' => 85],
                        ['issue' => 'Printer Offline', 'count' => 14, 'pct' => 78],
                        ['issue' => 'Network Connectivity', 'count' => 11, 'pct' => 72],
                        ['issue' => 'Application Crash', 'count' => 9, 'pct' => 65],
                        ['issue' => 'Slow Performance', 'count' => 7, 'pct' => 60],
                    ];
                    foreach ($topIssues as $i => $iss): ?>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-500"><?= $i + 1 ?></span>
                                    <span class="text-[13px] font-semibold text-gray-800 dark:text-gray-200"><?= $iss['issue'] ?></span>
                                </div>
                                <span class="text-[12px] text-gray-400 font-medium"><?= $iss['count'] ?> cases</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill blue" style="width:<?= $iss['pct'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header flex items-center gap-2 pb-4">
                <i data-lucide="zap" style="width:16px;height:16px;color:#64748b;"></i>
                <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="card-body" style="padding-top:0;">
                <div class="space-y-1">
                    <?php
                    $actions = [
                        ['icon' => 'stethoscope', 'label' => 'Start Troubleshooting', 'url' => '/troubleshoot', 'color' => '#2563eb', 'bg' => '#eff6ff'],
                        ['icon' => 'sparkles', 'label' => 'Ask IT Support AI', 'url' => '/ai', 'color' => '#9333ea', 'bg' => '#faf5ff'],
                        ['icon' => 'book-open', 'label' => 'Search Knowledge Base', 'url' => '/knowledge', 'color' => '#16a34a', 'bg' => '#f0fdf4'],
                        ['icon' => 'package', 'label' => 'Find Device/Model', 'url' => '/equipment', 'color' => '#0284c7', 'bg' => '#f0f9ff'],
                        ['icon' => 'terminal', 'label' => 'CMD Reference', 'url' => '/commands', 'color' => '#475569', 'bg' => '#f1f5f9'],
                        ['icon' => 'file-plus', 'label' => 'Document Solution', 'url' => '/documentation', 'color' => '#ea580c', 'bg' => '#fff7ed'],
                        ['icon' => 'ticket', 'label' => 'My Tickets', 'url' => '/tickets', 'color' => '#d97706', 'bg' => '#fffbeb'],
                        ['icon' => 'arrow-up-right', 'label' => 'Escalate Issue', 'url' => '/tickets?action=escalate', 'color' => '#dc2626', 'bg' => '#fef2f2'],
                    ];
                    foreach ($actions as $a): ?>
                        <a href="<?= $urlBase . ltrim($a['url'], '/') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group" style="text-decoration:none;">
                            <div style="width:32px;height:32px;border-radius:8px;background:<?= $a['bg'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:transform 0.15s;" class="group-hover:scale-110">
                                <i data-lucide="<?= $a['icon'] ?>" style="width:15px;height:15px;color:<?= $a['color'] ?>;"></i>
                            </div>
                            <span class="text-[13px] font-medium text-gray-700 dark:text-gray-300" style="flex:1;"><?= $a['label'] ?></span>
                            <i data-lucide="chevron-right" style="width:14px;height:14px;color:#d1d5db;opacity:0;transition:opacity 0.15s;" class="group-hover:!opacity-100"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Field Checklist -->
        <div class="card">
            <div class="card-header flex items-center gap-2 pb-4">
                <i data-lucide="clipboard-check" style="width:16px;height:16px;color:#64748b;"></i>
                <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Field Checklist</h3>
            </div>
            <div class="card-body" style="padding-top:0;">
                <div class="space-y-1" id="field-checklist">
                    <?php
                    $checklist = [
                        ['icon' => 'circle-check', 'text' => 'Issue confirmed with user'],
                        ['icon' => 'circle-check', 'text' => 'Device serial number recorded'],
                        ['icon' => 'circle-check', 'text' => 'Photos attached if needed'],
                        ['icon' => 'circle-check', 'text' => 'Steps documented'],
                        ['icon' => 'circle-check', 'text' => 'Solution verified'],
                        ['icon' => 'circle-check', 'text' => 'Ticket updated'],
                    ];
                    foreach ($checklist as $cl): ?>
                        <label class="checklist-item flex items-center gap-3 px-2 py-2 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <input type="checkbox" onchange="toggleChecklist(this)" style="width:16px;height:16px;accent-color:#2563eb;border-radius:4px;">
                            <span class="checklist-text text-[13px] text-gray-700 dark:text-gray-300" style="flex:1;"><?= $cl['text'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[12px] text-gray-500 font-medium">Progress</span>
                        <span id="checklist-progress" class="text-[12px] text-gray-500 font-semibold">0/<?= count($checklist) ?></span>
                    </div>
                    <div class="progress-bar">
                        <div id="checklist-bar" class="progress-fill green" style="width:0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tip Card -->
        <div class="card" style="background:linear-gradient(135deg,#eff6ff 0%,#f5f3ff 100%);border-color:#bfdbfe;">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-3">
                    <div style="width:28px;height:28px;border-radius:8px;background:rgba(37,99,235,0.1);display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="lightbulb" style="width:14px;height:14px;color:#2563eb;"></i>
                    </div>
                    <span class="text-[13px] font-bold text-gray-900 dark:text-white">Tip of the Day</span>
                </div>
                <p class="text-[13px] text-gray-600 dark:text-gray-300 leading-relaxed">
                    Before replacing a component, test the suspected component against a known-good component whenever practical. This saves time and prevents unnecessary replacements.
                </p>
                <a href="<?= $urlBase ?>tools" class="inline-flex items-center gap-1 mt-3 text-[12px] font-semibold text-brand-600 hover:text-brand-700" style="text-decoration:none;">
                    More tips <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>


<script>
function toggleChecklist(cb) {
    const item = cb.closest('.checklist-item');
    const text = item.querySelector('.checklist-text');
    if (cb.checked) { item.style.background = '#f0fdf4'; text.style.textDecoration = 'line-through'; text.style.color = '#94a3b8'; }
    else { item.style.background = ''; text.style.textDecoration = ''; text.style.color = ''; }
    const total = document.querySelectorAll('#field-checklist input[type="checkbox"]').length;
    const checked = document.querySelectorAll('#field-checklist input[type="checkbox"]:checked').length;
    document.getElementById('checklist-progress').textContent = checked + '/' + total;
    document.getElementById('checklist-bar').style.width = (checked / total * 100) + '%';
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
