<?php
$page_title = 'Dashboard';
$active_menu = 'dashboard';
require APP_ROOT . '/includes/layout_header.php';

// Fetch real stats from database
$totalSessions = 0; $solvedToday = 0; $pendingTickets = 0; $kbArticles = 0;
$recentTickets = []; $topIssues = []; $notifications = [];
$pendingCount = 0; $escalatedCount = 0;

if (defined('DEMO_MODE') && DEMO_MODE) {
    // Use demo values
    $totalSessions = 127; $solvedToday = 8; $pendingTickets = 5; $kbArticles = 15;
} else {
    try {
        $totalSessions = Database::count('troubleshooting_sessions');
        $solvedToday = Database::count('troubleshooting_sessions', "result = 'solved' AND DATE(created_at) = CURDATE()");
        $pendingTickets = Database::count('troubleshooting_sessions', "result IS NULL OR result = 'in_progress'");
        $kbArticles = Database::count('knowledge_articles');
        $pendingCount = Database::count('troubleshooting_sessions', "result IS NULL");
        $escalatedCount = Database::count('troubleshooting_sessions', "result = 'escalated'");

        $recentTickets = Database::fetchAll(
            "SELECT ts.*, ti.title as issue_title, ti.category_id, tc.name as category_name
             FROM troubleshooting_sessions ts
             LEFT JOIN troubleshooting_issues ti ON ts.issue_id = ti.id
             LEFT JOIN troubleshooting_categories tc ON ti.category_id = tc.id
             ORDER BY ts.created_at DESC LIMIT 6"
        );

        $topIssues = Database::fetchAll(
            "SELECT ti.title, COUNT(*) as cnt
             FROM troubleshooting_sessions ts
             JOIN troubleshooting_issues ti ON ts.issue_id = ti.id
             GROUP BY ts.issue_id ORDER BY cnt DESC LIMIT 5"
        );
        if (empty($topIssues)) {
            $topIssues = [
                ['title' => 'No Display', 'cnt' => 18],
                ['title' => 'Printer Offline', 'cnt' => 14],
                ['title' => 'Network Issues', 'cnt' => 11],
                ['title' => 'Application Crash', 'cnt' => 9],
                ['title' => 'Slow Performance', 'cnt' => 7],
            ];
        }

        $notifications = Database::fetchAll(
            "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 8"
        );
    } catch (Exception $e) {
        $totalSessions = 127; $solvedToday = 8; $pendingTickets = 5; $kbArticles = 15;
    }
}

if (empty($recentTickets)) {
    $recentTickets = [
        ['issue_title' => 'No Display', 'category_name' => 'Display', 'result' => 'solved', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'device_type' => 'Desktop', 'device_model' => 'Dell OptiPlex 7090'],
        ['issue_title' => 'Network Slow', 'category_name' => 'Network', 'result' => 'in_progress', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')), 'device_type' => 'Laptop', 'device_model' => 'HP ProBook 450'],
        ['issue_title' => 'Printer Offline', 'category_name' => 'Printer', 'result' => 'escalated', 'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours')), 'device_type' => 'Printer', 'device_model' => 'HP LaserJet Pro M404'],
        ['issue_title' => 'No Sound', 'category_name' => 'Sound', 'result' => 'solved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'device_type' => 'Desktop', 'device_model' => 'Lenovo ThinkCentre M70s'],
        ['issue_title' => 'WiFi Not Connecting', 'category_name' => 'Network', 'result' => 'in_progress', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'device_type' => 'Laptop', 'device_model' => 'Dell Latitude 5520'],
    ];
}

if (empty($notifications)) {
    $notifications = [
        ['title' => 'Ticket TK-1001 escalated', 'message' => 'Supervisor review required for printer issue', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 min')), 'is_read' => 0],
        ['title' => 'KB article approved', 'message' => 'No Display troubleshooting guide published', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'is_read' => 0],
        ['title' => 'New team member', 'message' => 'Ana T. joined the Field IT team', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')), 'is_read' => 1],
    ];
}

$unreadNotif = 0;
foreach ($notifications as $n) { if (empty($n['is_read'])) $unreadNotif++; }
$notifCount = count($notifications);
?>

<style>
/* Dashboard Dark Mode Overrides */
.dark .dash-text { color: #f1f5f9 !important; }
.dark .dash-text-muted { color: #94a3b8 !important; }
.dark .dash-bg { background: #1e293b !important; }
.dark .dash-border { border-color: #334155 !important; }
.dark .glass-card { background: rgba(30,41,59,0.75) !important; border-color: rgba(51,65,85,0.6) !important; }
.dark .glass-card .dash-text { color: #f1f5f9 !important; }
.dark .glass-card .dash-text-sub { color: #94a3b8 !important; }
.dark .tc-card { background: #1e293b !important; border-color: #334155 !important; }
.dark .tc-card:hover { border-color: #3b82f6 !important; }
.dark .tc-label { color: #e2e8f0 !important; }
.dark .hero-search-wrap input { background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9 !important; }
.dark .stat-card-premium { background: #1e293b !important; border-color: #334155 !important; }
.dark .bar-chart-label { color: #94a3b8 !important; }
.dark .bar-chart-track { background: #334155 !important; }
.dark .ticket-row { border-color: #1e293b !important; }
.dark .ticket-row:hover { background: rgba(30,41,59,0.5) !important; }
.dark .pill-action { background: #1e293b !important; border-color: #334155 !important; color: #94a3b8 !important; }
.dark .pill-action:hover { background: #2563eb !important; color: #fff !important; }
.dark .tip-card { background: linear-gradient(135deg, rgba(30,41,59,0.9), rgba(30,41,59,0.9)) !important; border-color: #334155 !important; }
.dark .cl-text { color: #d1d5db !important; }
.dark label:hover { background: rgba(30,41,59,0.5) !important; }
.dark #checklist-progress { color: #94a3b8 !important; }
.dark .notif-dropdown { background: #1e293b !important; border-color: #334155 !important; }
.dark .notif-item { border-color: #1e293b !important; }
.dark .notif-item:hover { background: rgba(30,41,59,0.5) !important; }
.dark .notif-item div[style*="font-weight:600"] { color: #f1f5f9 !important; }

/* Dashboard Premium Styles */
.dash-hero {
    position: relative;
    padding: 32px 0 28px;
    margin-bottom: 8px;
}
.dash-hero::before {
    content: '';
    position: absolute;
    top: -60px; left: -40px; right: -40px;
    height: 280px;
    background: linear-gradient(135deg, rgba(37,99,235,0.06) 0%, rgba(124,58,237,0.04) 50%, rgba(236,72,153,0.03) 100%);
    border-radius: 0 0 24px 24px;
    z-index: 0;
    pointer-events: none;
}
.dark .dash-hero::before {
    background: linear-gradient(135deg, rgba(37,99,235,0.1) 0%, rgba(124,58,237,0.06) 50%, rgba(236,72,153,0.04) 100%);
}
.dash-hero > * { position: relative; z-index: 1; }

/* Stat Cards Premium */
.stat-card-premium {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}
.stat-card-premium:hover {
    box-shadow: 0 8px 25px -5px rgba(0,0,0,0.08), 0 4px 10px -5px rgba(0,0,0,0.04);
    transform: translateY(-2px);
}
.dark .stat-card-premium { background: #1e293b; border-color: #334155; }
.stat-card-premium .stat-sparkline {
    position: absolute;
    bottom: 0; right: 0;
    width: 120px; height: 50px;
    opacity: 0.15;
}
.stat-card-premium .stat-glow {
    position: absolute;
    top: -30px; right: -30px;
    width: 100px; height: 100px;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.12;
}

/* Glassmorphism Card */
.glass-card {
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(12px) saturate(180%);
    border: 1px solid rgba(255,255,255,0.8);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
}
.glass-card:hover {
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
}
.dark .glass-card {
    background: rgba(30,41,59,0.7);
    border-color: rgba(51,65,85,0.8);
}

/* Troubleshoot Category Cards */
.tc-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 18px 10px;
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    cursor: pointer;
}
.tc-card:hover {
    border-color: #bfdbfe;
    box-shadow: 0 4px 12px -2px rgba(37,99,235,0.12);
    transform: translateY(-3px);
}
.dark .tc-card { background: #1e293b; border-color: #334155; }
.dark .tc-card:hover { border-color: #3b82f6; }
.tc-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.2s;
}
.tc-card:hover .tc-icon { transform: scale(1.12) rotate(-3deg); }
.tc-icon i { width: 20px; height: 20px; }
.tc-label { font-size: 12px; font-weight: 600; color: #374151; }
.dark .tc-label { color: #e2e8f0; }

/* Pill Quick Actions */
.pill-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}
.pill-action:hover {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
    box-shadow: 0 2px 8px rgba(37,99,235,0.25);
    transform: translateY(-1px);
}
.pill-action i { width: 14px; height: 14px; flex-shrink: 0; }
.dark .pill-action { background: #1e293b; border-color: #334155; color: #94a3b8; }
.dark .pill-action:hover { background: #2563eb; color: #fff; }

/* Bar Chart */
.bar-chart-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
}
.bar-chart-label {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    min-width: 130px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dark .bar-chart-label { color: #94a3b8; }
.bar-chart-track {
    flex: 1;
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}
.dark .bar-chart-track { background: #334155; }
.bar-chart-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}
.bar-chart-count {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    min-width: 32px;
    text-align: right;
}

/* Ticket Row */
.ticket-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.ticket-row:last-child { border-bottom: none; }
.ticket-row:hover { background: #f8fafc; border-radius: 10px; margin: 0 -8px; padding: 14px 8px; }
.dark .ticket-row { border-bottom-color: #1e293b; }
.dark .ticket-row:hover { background: rgba(30,41,59,0.5); }

/* Notification Dropdown */
.notif-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 360px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);
    z-index: 50;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-8px);
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
}
.notif-dropdown.open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}
.dark .notif-dropdown { background: #1e293b; border-color: #334155; }
.notif-item {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
    cursor: pointer;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f8fafc; }
.dark .notif-item { border-bottom-color: #1e293b; }
.dark .notif-item:hover { background: rgba(30,41,59,0.5); }
.notif-dot-unread {
    width: 8px; height: 8px;
    background: #2563eb;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
}
.notif-dot-read {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
    background: transparent;
}

/* Search Bar Hero */
.hero-search-wrap {
    max-width: 620px;
    position: relative;
}
.hero-search-wrap input {
    width: 100%;
    padding: 16px 150px 16px 50px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    font-size: 15px;
    color: #1e293b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.2s;
}
.hero-search-wrap input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1), 0 2px 8px rgba(0,0,0,0.06);
}
.dark .hero-search-wrap input { background: #1e293b; border-color: #334155; color: #f1f5f9; }
.dark .hero-search-wrap input:focus { border-color: #3b82f6; }

/* Trend Arrow */
.trend-up { color: #16a34a; }
.trend-down { color: #dc2626; }
.trend-badge {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}
.trend-badge.up { background: #f0fdf4; color: #16a34a; }
.trend-badge.down { background: #fef2f2; color: #dc2626; }
.dark .trend-badge.up { background: rgba(22,163,74,0.15); color: #4ade80; }
.dark .trend-badge.down { background: rgba(220,38,38,0.15); color: #f87171; }
</style>

<!-- Hero Section -->
<div class="dash-hero">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:26px;font-weight:800;color:#111827;letter-spacing:-0.03em;line-height:1.2;" class="dash-text">
                Welcome back, <?= e(Auth::userName()) ?> 👋
            </h1>
            <p style="font-size:14px;color:#64748b;margin-top:4px;" class="dash-text-muted">Here's what's happening with your IT operations today.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="<?= $urlBase ?>troubleshoot" class="btn btn-primary" style="border-radius:100px;padding:10px 20px;">
                <i data-lucide="stethoscope" style="width:15px;height:15px;"></i> New Session
            </a>
            <a href="<?= $urlBase ?>ai" class="btn btn-secondary" style="border-radius:100px;padding:10px 20px;">
                <i data-lucide="sparkles" style="width:15px;height:15px;"></i> Ask AI
            </a>
        </div>
    </div>

    <!-- Hero Search -->
    <div class="hero-search-wrap">
        <i data-lucide="search" style="position:absolute;left:18px;top:50%;transform:translateY(-50%);width:20px;height:20px;color:#94a3b8;pointer-events:none;"></i>
        <input type="text" id="hero-search" class="dark-input"
               placeholder="Search devices, tickets, or commands..."
               onkeydown="if(event.key==='Enter') window.location.href=APP_BASE+'troubleshoot?q='+encodeURIComponent(this.value)">
        <button onclick="window.location.href=APP_BASE+'troubleshoot?q='+encodeURIComponent(document.getElementById('hero-search').value)"
                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);padding:10px 22px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(37,99,235,0.3);transition:all 0.2s;"
                onmouseover="this.style.transform='translateY(-50%) scale(1.02)';this.style.boxShadow='0 4px 12px rgba(37,99,235,0.4)'"
                onmouseout="this.style.transform='translateY(-50%) scale(1)';this.style.boxShadow='0 2px 8px rgba(37,99,235,0.3)'">
            <i data-lucide="zap" style="width:14px;height:14px;"></i>
            Troubleshoot
        </button>

    </div>
</div>

<!-- Stats Row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;" class="stats-grid">
    <?php
    $statCards = [
        ['icon' => 'activity', 'label' => 'Total Sessions', 'value' => $totalSessions, 'change' => '+12', 'up' => true, 'color' => '#2563eb', 'glow' => 'rgba(37,99,235,0.15)', 'spark' => [3,5,4,7,6,8,9,7,11,10,12,14,13,15,14,16,17,15,18,17]],
        ['icon' => 'check-circle-2', 'label' => 'Solved Today', 'value' => $solvedToday, 'change' => '+3', 'up' => true, 'color' => '#16a34a', 'glow' => 'rgba(22,163,74,0.15)', 'spark' => [2,3,2,4,5,4,6,5,7,6,8,7,8,7,9,8,8,7,8,8]],
        ['icon' => 'clock-3', 'label' => 'Pending Tickets', 'value' => $pendingTickets, 'change' => '-2', 'up' => false, 'color' => '#d97706', 'glow' => 'rgba(217,119,6,0.15)', 'spark' => [8,9,7,8,6,7,5,6,5,4,5,4,6,5,4,5,4,3,4,3]],
        ['icon' => 'book-open', 'label' => 'KB Articles', 'value' => $kbArticles, 'change' => '+5', 'up' => true, 'color' => '#9333ea', 'glow' => 'rgba(147,51,234,0.15)', 'spark' => [5,6,7,6,8,9,8,10,11,10,12,13,12,14,13,15,14,16,15,17]],
    ];
    foreach ($statCards as $s):
        // Generate SVG sparkline
        $pts = $s['spark'];
        $max = max($pts); $min = min($pts);
        $range = max($max - $min, 1);
        $w = 120; $h = 50;
        $coords = [];
        foreach ($pts as $i => $v) {
            $x = ($i / (count($pts) - 1)) * $w;
            $y = $h - (($v - $min) / $range) * ($h - 8) - 4;
            $coords[] = sprintf('%.1f,%.1f', $x, $y);
        }
        $pathD = 'M' . implode(' L', $coords);
        $areaD = $pathD . " L{$w},{$h} L0,{$h} Z";
    ?>
        <div class="stat-card-premium">
            <div class="stat-glow" style="background:<?= $s['color'] ?>;"></div>
            <svg class="stat-sparkline" viewBox="0 0 <?= $w ?> <?= $h ?>" preserveAspectRatio="none">
                <path d="<?= $areaD ?>" fill="<?= $s['color'] ?>" />
                <path d="<?= $pathD ?>" fill="none" stroke="<?= $s['color'] ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div style="display:flex;align-items:center;gap:14px;position:relative;z-index:1;">
                <div style="width:48px;height:48px;border-radius:12px;background:<?= $s['glow'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="<?= $s['icon'] ?>" style="width:22px;height:22px;color:<?= $s['color'] ?>;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:baseline;gap:8px;">
                        <span style="font-size:28px;font-weight:800;color:#111827;letter-spacing:-0.03em;line-height:1;" class="dash-text"><?= $s['value'] ?></span>
                        <span class="trend-badge <?= $s['up'] ? 'up' : 'down' ?>">
                            <i data-lucide="<?= $s['up'] ? 'trending-up' : 'trending-down' ?>" style="width:10px;height:10px;"></i>
                            <?= $s['change'] ?>
                        </span>
                    </div>
                    <div style="font-size:13px;color:#64748b;font-weight:500;margin-top:2px;"><?= $s['label'] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Quick Troubleshooting Grid -->
<div style="margin-bottom:28px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <h2 style="font-size:16px;font-weight:700;color:#111827;" class="dash-text">Quick Troubleshooting</h2>
        <a href="<?= $urlBase ?>troubleshoot" style="font-size:13px;font-weight:600;color:#2563eb;text-decoration:none;display:flex;align-items:center;gap:4px;">View all <i data-lucide="arrow-right" style="width:13px;height:13px;"></i></a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;" class="tc-grid">
        <?php
        $cats = [
            ['icon'=>'monitor','label'=>'Display','q'=>'no display','bg'=>'#eff6ff','fg'=>'#2563eb'],
            ['icon'=>'power','label'=>'Power','q'=>'no power','bg'=>'#fef2f2','fg'=>'#dc2626'],
            ['icon'=>'volume-2','label'=>'Sound','q'=>'no sound','bg'=>'#faf5ff','fg'=>'#9333ea'],
            ['icon'=>'wifi','label'=>'Network','q'=>'network issue','bg'=>'#f0fdf4','fg'=>'#16a34a'],
            ['icon'=>'printer','label'=>'Printer','q'=>'printer','bg'=>'#fff7ed','fg'=>'#ea580c'],
            ['icon'=>'camera','label'=>'CCTV','q'=>'cctv','bg'=>'#f8fafc','fg'=>'#475569'],
            ['icon'=>'server','label'=>'Server','q'=>'server','bg'=>'#eef2ff','fg'=>'#4f46e5'],
            ['icon'=>'laptop','label'=>'Laptop','q'=>'laptop','bg'=>'#ecfdf5','fg'=>'#059669'],
            ['icon'=>'desktop','label'=>'Desktop','q'=>'desktop','bg'=>'#f0f9ff','fg'=>'#0284c7'],
            ['icon'=>'credit-card','label'=>'POS','q'=>'pos','bg'=>'#fdf2f8','fg'=>'#db2777'],
            ['icon'=>'monitor-speaker','label'=>'Monitor','q'=>'monitor','bg'=>'#f5f3ff','fg'=>'#7c3aed'],
            ['icon'=>'smartphone','label'=>'Other','q'=>'other device','bg'=>'#f1f5f9','fg'=>'#64748b'],
        ];
        foreach ($cats as $c): ?>
            <a href="<?= $urlBase ?>troubleshoot?q=<?= urlencode($c['q']) ?>" class="tc-card">
                <div class="tc-icon" style="background:<?= $c['bg'] ?>;color:<?= $c['fg'] ?>;">
                    <i data-lucide="<?= $c['icon'] ?>"></i>
                </div>
                <span class="tc-label"><?= $c['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;" class="dash-main-grid">

    <!-- Left Column -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Active Work / Recent Tickets -->
        <div class="glass-card">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;" class="dark:border-gray-700/50">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="list-checks" style="width:16px;height:16px;color:#2563eb;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:700;color:#111827;" class="dash-text">Active Work</h3>
                        <p style="font-size:11px;color:#94a3b8;">Recent troubleshooting sessions</p>
                    </div>
                </div>
                <div style="display:flex;gap:6px;">
                    <span class="badge badge-yellow" style="font-size:11px;"><?= $pendingCount ?> pending</span>
                    <span class="badge badge-red" style="font-size:11px;"><?= $escalatedCount ?> escalated</span>
                </div>
            </div>
            <div style="padding:4px 24px 8px;">
                <?php foreach ($recentTickets as $t):
                    $result = $t['result'] ?? 'pending';
                    if ($result === 'solved') { $statusBadge = 'badge-green'; $statusText = 'Solved'; }
                    elseif ($result === 'escalated') { $statusBadge = 'badge-red'; $statusText = 'Escalated'; }
                    else { $statusBadge = 'badge-blue'; $statusText = 'In Progress'; }

                    $timeAgo = '';
                    if (!empty($t['created_at'])) {
                        $diff = time() - strtotime($t['created_at']);
                        if ($diff < 3600) $timeAgo = round($diff/60) . 'm ago';
                        elseif ($diff < 86400) $timeAgo = round($diff/3600) . 'h ago';
                        else $timeAgo = round($diff/86400) . 'd ago';
                    }

                    $icons = ['Display'=>'monitor','Network'=>'wifi','Printer'=>'printer','Sound'=>'volume-2','Hardware'=>'cpu','Software'=>'app-window'];
                    $icon = $icons[$t['category_name'] ?? ''] ?? 'wrench';
                ?>
                    <div class="ticket-row" style="text-decoration:none;cursor:pointer;" onclick="window.location.href=APP_BASE+'troubleshoot'">
                        <div style="width:38px;height:38px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;" class="dark:bg-gray-700">
                            <i data-lucide="<?= $icon ?>" style="width:16px;height:16px;color:#64748b;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="dash-text"><?= e($t['issue_title'] ?? 'Unknown Issue') ?></div>
                            <div style="font-size:12px;color:#94a3b8;margin-top:1px;"><?= e($t['device_type'] ?? '') ?> <?= e($t['device_model'] ?? '') ?></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                            <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                            <span style="font-size:11px;color:#94a3b8;white-space:nowrap;" class="hide-mobile"><?= $timeAgo ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="padding:12px 24px;border-top:1px solid #f1f5f9;text-align:center;" class="dark:border-gray-700/50">
                <a href="<?= $urlBase ?>tickets" style="font-size:13px;font-weight:600;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">View all tickets <i data-lucide="arrow-right" style="width:12px;height:12px;"></i></a>
            </div>
        </div>

        <!-- Most Common Issues Bar Chart -->
        <div class="glass-card">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;" class="dark:border-gray-700/50">
                <div style="width:32px;height:32px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#dc2626;"></i>
                </div>
                <div>
                    <h3 style="font-size:15px;font-weight:700;color:#111827;" class="dash-text">Most Common Issues</h3>
                    <p style="font-size:11px;color:#94a3b8;">This week's top troubleshooting categories</p>
                </div>
            </div>
            <div style="padding:16px 24px 20px;">
                <?php
                $maxCnt = max(array_column($topIssues, 'cnt'));
                $barColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444'];
                foreach ($topIssues as $i => $iss):
                    $pct = $maxCnt > 0 ? ($iss['cnt'] / $maxCnt) * 100 : 0;
                ?>
                    <div class="bar-chart-row">
                        <div class="bar-chart-label">
                            <span style="width:20px;height:20px;border-radius:6px;background:<?= $barColors[$i % 5] ?>15;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:<?= $barColors[$i % 5] ?>;flex-shrink:0;"><?= $i + 1 ?></span>
                            <?= e($iss['title']) ?>
                        </div>
                        <div class="bar-chart-track">
                            <div class="bar-chart-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,<?= $barColors[$i % 5] ?>,<?= $barColors[$i % 5] ?>cc);"></div>
                        </div>
                        <div class="bar-chart-count"><?= $iss['cnt'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Quick Actions -->
        <div class="glass-card">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;" class="dark:border-gray-700/50">
                <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="zap" style="width:16px;height:16px;color:#16a34a;"></i>
                </div>
                <h3 style="font-size:15px;font-weight:700;color:#111827;" class="dash-text">Quick Actions</h3>
            </div>
            <div style="padding:16px 24px 20px;display:flex;flex-wrap:wrap;gap:8px;">
                <?php
                $actions = [
                    ['icon'=>'stethoscope','label'=>'Start Troubleshooting','url'=>'/troubleshoot'],
                    ['icon'=>'sparkles','label'=>'Ask IT Support AI','url'=>'/ai'],
                    ['icon'=>'book-open','label'=>'Knowledge Base','url'=>'/knowledge'],
                    ['icon'=>'package','label'=>'Find Device','url'=>'/equipment'],
                    ['icon'=>'terminal','label'=>'CMD Reference','url'=>'/commands'],
                    ['icon'=>'file-plus','label'=>'Document Solution','url'=>'/documentation'],
                    ['icon'=>'ticket','label'=>'My Tickets','url'=>'/tickets'],
                    ['icon'=>'arrow-up-right','label'=>'Escalate Issue','url'=>'/tickets'],
                ];
                foreach ($actions as $a): ?>
                    <a href="<?= $urlBase . ltrim($a['url'], '/') ?>" class="pill-action">
                        <i data-lucide="<?= $a['icon'] ?>"></i>
                        <?= $a['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Field Checklist -->
        <div class="glass-card">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;" class="dark:border-gray-700/50">
                <div style="width:32px;height:32px;border-radius:8px;background:#faf5ff;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="clipboard-check" style="width:16px;height:16px;color:#9333ea;"></i>
                </div>
                <h3 style="font-size:15px;font-weight:700;color:#111827;" class="dash-text">Field Checklist</h3>
            </div>
            <div style="padding:12px 24px 20px;">
                <div id="field-checklist">
                    <?php
                    $checklist = [
                        'Issue confirmed with user',
                        'Device serial number recorded',
                        'Photos attached if needed',
                        'Steps documented',
                        'Solution verified',
                        'Ticket updated',
                    ];
                    foreach ($checklist as $cl): ?>
                        <label style="display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:8px;cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <input type="checkbox" onchange="toggleChecklist(this)" style="width:16px;height:16px;accent-color:#2563eb;border-radius:4px;flex-shrink:0;">
                            <span class="cl-text" style="font-size:13px;color:#475569;flex:1;" class="dark:text-gray-300"><?= $cl ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;" class="dark:border-gray-700/50">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:12px;color:#94a3b8;font-weight:500;">Progress</span>
                        <span id="checklist-progress" style="font-size:12px;color:#64748b;font-weight:700;">0/<?= count($checklist) ?></span>
                    </div>
                    <div class="progress-bar" style="height:6px;">
                        <div id="checklist-bar" class="progress-fill green" style="width:0%;transition:width 0.4s;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tip of the Day -->
        <div style="background:linear-gradient(135deg,#eff6ff 0%,#faf5ff 50%,#f0fdf4 100%);border:1px solid #bfdbfe;border-radius:16px;padding:20px 24px;position:relative;overflow:hidden;" class="dark:bg-opacity-30 dark:border-blue-900/30">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(37,99,235,0.06);border-radius:50%;"></div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(37,99,235,0.1);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="lightbulb" style="width:15px;height:15px;color:#2563eb;"></i>
                </div>
                <span style="font-size:14px;font-weight:700;color:#111827;" class="dash-text">Tip of the Day</span>
            </div>
            <p style="font-size:13px;color:#475569;line-height:1.65;position:relative;z-index:1;" class="dash-text-muted">
                Before replacing a component, test the suspected component against a known-good component whenever practical. This saves time and prevents unnecessary replacements.
            </p>
            <a href="<?= $urlBase ?>tools" style="display:inline-flex;align-items:center;gap:4px;margin-top:12px;font-size:12px;font-weight:600;color:#2563eb;text-decoration:none;position:relative;z-index:1;">
                More tips <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
            </a>
        </div>

    </div>
</div>

<style>
@media (max-width: 1200px) {
    .dash-main-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .tc-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 640px) {
    .stats-grid { grid-template-columns: 1fr !important; }
    .tc-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>

<script>
function toggleChecklist(cb) {
    const label = cb.closest('label');
    const text = label.querySelector('.cl-text');
    if (cb.checked) {
        label.style.background = '#f0fdf4';
        text.style.textDecoration = 'line-through';
        text.style.color = '#94a3b8';
    } else {
        label.style.background = '';
        text.style.textDecoration = '';
        text.style.color = '';
    }
    const total = document.querySelectorAll('#field-checklist input[type="checkbox"]').length;
    const checked = document.querySelectorAll('#field-checklist input[type="checkbox"]:checked').length;
    document.getElementById('checklist-progress').textContent = checked + '/' + total;
    document.getElementById('checklist-bar').style.width = (checked / total * 100) + '%';
}

// Notification dropdown
function toggleNotifications() {
    var dd = document.getElementById('notif-dropdown');
    dd.classList.toggle('open');
}
// Close on outside click
document.addEventListener('click', function(e) {
    var dd = document.getElementById('notif-dropdown');
    var btn = document.getElementById('notif-btn');
    if (dd && !dd.contains(e.target) && !btn.contains(e.target)) {
        dd.classList.remove('open');
    }
});
</script>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
