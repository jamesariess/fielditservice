<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Troubleshoot';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';

$query = $_GET['q'] ?? '';
$selectedDevice = $_GET['device'] ?? '';

$devices = [
    [
        'id' => 'desktop', 'icon' => 'monitor', 'label' => 'Desktop PC',
        'desc' => 'Desktop computers, workstations, towers',
        'bg' => '#eff6ff', 'fg' => '#2563eb', 'count' => 8,
        'issues' => [
            ['title'=>'No Display','desc'=>'Black screen, no signal','severity'=>'high','slug'=>'no-display'],
            ['title'=>'No Power','desc'=>'Won\'t turn on, dead, no lights','severity'=>'high','slug'=>'no-power'],
            ['title'=>'No Sound','desc'=>'No audio, speakers muted','severity'=>'medium','slug'=>'no-sound'],
            ['title'=>'Blue Screen (BSOD)','desc'=>'Windows crash, blue screen','severity'=>'high','slug'=>'bsod'],
            ['title'=>'Slow Performance','desc'=>'Laggy, slow boot','severity'=>'medium','slug'=>'slow-performance'],
            ['title'=>'Application Crash','desc'=>'App crash, not responding','severity'=>'medium','slug'=>'application-crash'],
            ['title'=>'Random Shutdowns','desc'=>'Unexpected shutdowns','severity'=>'high','slug'=>'random-shutdowns'],
            ['title'=>'Windows Update Fails','desc'=>'Updates fail to install','severity'=>'medium','slug'=>'windows-update-fails'],
        ],
    ],
    [
        'id' => 'laptop', 'icon' => 'laptop', 'label' => 'Laptop',
        'desc' => 'Laptops, notebooks, ultrabooks',
        'bg' => '#f0fdf4', 'fg' => '#16a34a', 'count' => 8,
        'issues' => [
            ['title'=>'No Display','desc'=>'Black screen, external monitor','severity'=>'high','slug'=>'no-display'],
            ['title'=>'No Power','desc'=>'Battery dead, charger issue','severity'=>'high','slug'=>'no-power'],
            ['title'=>'No Sound','desc'=>'No audio, speakers not working','severity'=>'medium','slug'=>'no-sound'],
            ['title'=>'WiFi Not Connecting','desc'=>'Can\'t connect, intermittent','severity'=>'high','slug'=>'wifi-not-connecting'],
            ['title'=>'Blue Screen (BSOD)','desc'=>'Windows crash, blue screen','severity'=>'high','slug'=>'bsod'],
            ['title'=>'Slow Performance','desc'=>'Laggy, slow boot','severity'=>'medium','slug'=>'slow-performance'],
            ['title'=>'Application Crash','desc'=>'App crash, not responding','severity'=>'medium','slug'=>'application-crash'],
            ['title'=>'Overheating','desc'=>'Fan loud, hot to touch','severity'=>'high','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'printer', 'icon' => 'printer', 'label' => 'Printer',
        'desc' => 'Printers, MFPs, plotters',
        'bg' => '#fff7ed', 'fg' => '#ea580c', 'count' => 3,
        'issues' => [
            ['title'=>'Printer Offline','desc'=>'Offline in Windows, not detected','severity'=>'high','slug'=>'printer-offline'],
            ['title'=>'Paper Jam','desc'=>'Paper stuck, repeated jams','severity'=>'medium','slug'=>'paper-jam'],
            ['title'=>'Overheating','desc'=>'Printer overheating','severity'=>'medium','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'network', 'icon' => 'router', 'label' => 'Router / Switch',
        'desc' => 'Routers, switches, access points',
        'bg' => '#ecfdf5', 'fg' => '#059669', 'count' => 4,
        'issues' => [
            ['title'=>'No Internet','desc'=>'All devices lost internet','severity'=>'high','slug'=>'no-internet'],
            ['title'=>'Network Slow','desc'=>'Slow speeds, high latency','severity'=>'medium','slug'=>'network-slow'],
            ['title'=>'WiFi Not Connecting','desc'=>'Can\'t connect, auth fails','severity'=>'medium','slug'=>'wifi-not-connecting'],
            ['title'=>'DNS Issues','desc'=>'Can ping IP but not domains','severity'=>'high','slug'=>'dns-issues'],
        ],
    ],
    [
        'id' => 'server', 'icon' => 'server', 'label' => 'Server',
        'desc' => 'Physical servers, virtual machines',
        'bg' => '#eef2ff', 'fg' => '#4f46e5', 'count' => 5,
        'issues' => [
            ['title'=>'No Power','desc'=>'Server not responding','severity'=>'high','slug'=>'no-power'],
            ['title'=>'No Display','desc'=>'Console blank, IPMI issues','severity'=>'high','slug'=>'no-display'],
            ['title'=>'No Internet','desc'=>'Network adapter down','severity'=>'high','slug'=>'no-internet'],
            ['title'=>'Random Shutdowns','desc'=>'Unexpected restarts','severity'=>'high','slug'=>'random-shutdowns'],
            ['title'=>'Slow Performance','desc'=>'CPU spike, memory leak','severity'=>'medium','slug'=>'slow-performance'],
        ],
    ],
    [
        'id' => 'monitor', 'icon' => 'monitor', 'label' => 'Monitor',
        'desc' => 'Displays, projectors, signage',
        'bg' => '#f5f3ff', 'fg' => '#7c3aed', 'count' => 3,
        'issues' => [
            ['title'=>'No Display','desc'=>'No signal, check cable','severity'=>'high','slug'=>'no-display'],
            ['title'=>'Flickering Display','desc'=>'Screen flickers, brightness','severity'=>'medium','slug'=>'flickering-display'],
            ['title'=>'No Display and Power','desc'=>'Monitor completely dead','severity'=>'high','slug'=>'no-display-and-no-power'],
        ],
    ],
    [
        'id' => 'cctv', 'icon' => 'camera', 'label' => 'CCTV / NVR',
        'desc' => 'IP cameras, DVR, NVR systems',
        'bg' => '#f8fafc', 'fg' => '#475569', 'count' => 3,
        'issues' => [
            ['title'=>'Camera Offline','desc'=>'Camera not accessible','severity'=>'high','slug'=>'camera-offline'],
            ['title'=>'No Recording','desc'=>'NVR not recording','severity'=>'high','slug'=>'no-recording'],
            ['title'=>'Overheating','desc'=>'NVR overheating','severity'=>'medium','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'pos', 'icon' => 'credit-card', 'label' => 'POS System',
        'desc' => 'Point of Sale terminals',
        'bg' => '#fdf2f8', 'fg' => '#db2777', 'count' => 4,
        'issues' => [
            ['title'=>'No Power','desc'=>'Terminal dead, power issue','severity'=>'high','slug'=>'no-power'],
            ['title'=>'No Internet','desc'=>'Can\'t connect to server','severity'=>'high','slug'=>'no-internet'],
            ['title'=>'Application Crash','desc'=>'POS app freezing','severity'=>'high','slug'=>'application-crash'],
            ['title'=>'Printer Offline','desc'=>'Receipt printer issue','severity'=>'medium','slug'=>'printer-offline'],
        ],
    ],
    [
        'id' => 'other', 'icon' => 'circle-help', 'label' => 'Other Device',
        'desc' => 'Scanners, projectors, IoT',
        'bg' => '#f1f5f9', 'fg' => '#64748b', 'count' => 2,
        'issues' => [
            ['title'=>'Slow Performance','desc'=>'General performance issue','severity'=>'medium','slug'=>'slow-performance'],
            ['title'=>'Application Crash','desc'=>'General software issue','severity'=>'medium','slug'=>'application-crash'],
        ],
    ],
];

$severityColors = ['high'=>['bg'=>'#fef2f2','fg'=>'#dc2626'],'medium'=>['bg'=>'#fffbeb','fg'=>'#d97706'],'low'=>['bg'=>'#f0fdf4','fg'=>'#16a34a'],'info'=>['bg'=>'#eff6ff','fg'=>'#2563eb']];
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ===== HERO ===== */
.th-hero { text-align: center; padding: 32px 0 8px; }
.th-hero h1 { font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -0.04em; margin-bottom: 8px; }
.dark .th-hero h1 { color: #f1f5f9; }
.th-hero-sub { font-size: 15px; color: #64748b; margin-bottom: 28px; }
.dark .th-hero-sub { color: #94a3b8; }

/* Search */
.th-search-wrap { max-width: 700px; margin: 0 auto 12px; position: relative; }
.th-search-glow { position: absolute; inset: -3px; background: linear-gradient(135deg, #3b82f6, #8b5cf6, #3b82f6); border-radius: 20px; opacity: 0.25; filter: blur(10px); z-index: 0; transition: opacity 0.3s; }
.th-search-wrap:focus-within .th-search-glow { opacity: 0.45; }
.th-search-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 10px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 6px 6px 6px 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); transition: all 0.25s; }
.th-search-inner:focus-within { border-color: #3b82f6; box-shadow: 0 4px 30px rgba(37,99,235,0.12); }
.dark .th-search-inner { background: #1e293b; border-color: #334155; }
.th-search-inner input { flex: 1; border: none; background: transparent; font-size: 15px; color: #0f172a; outline: none; padding: 14px 8px; font-family: 'Inter', sans-serif; }
.th-search-inner input::placeholder { color: #94a3b8; }
.dark .th-search-inner input { color: #f1f5f9; }
.th-search-inner svg, .th-search-inner i { width: 20px; height: 20px; color: #94a3b8; flex-shrink: 0; }
.th-ai-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s; text-decoration: none; box-shadow: 0 2px 10px rgba(37,99,235,0.3); font-family: 'Inter', sans-serif; }
.th-ai-btn:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); box-shadow: 0 4px 16px rgba(37,99,235,0.4); transform: translateY(-1px); }
.th-ai-btn i { width: 16px; height: 16px; }
.th-ai-hint { font-size: 12px; color: #94a3b8; margin-top: 8px; }

/* ===== ERROR CODE PILLS ===== */
.th-err-pills { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 20px 0 28px; flex-wrap: wrap; }
.th-err-pill {
    display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px;
    font-size: 12px; font-weight: 600; color: #475569; cursor: pointer;
    transition: all 0.2s; text-decoration: none;
}
.th-err-pill:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; transform: translateY(-1px); }
.th-err-pill i { width: 14px; height: 14px; }
.th-err-pill.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.th-err-pill.active:hover { background: #1d4ed8; }

/* ===== ERROR CODE RESULTS ===== */
.th-err-results { max-width: 800px; margin: 0 auto 32px; }
.th-err-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 20px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s;
}
.th-err-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); border-color: #bfdbfe; }
.dark .th-err-card { background: #1e293b; border-color: #334155; }
.th-err-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.th-err-code { font-family: 'Fira Code', 'Courier New', monospace; font-size: 14px; font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; }
.dark .th-err-code { background: #334155; color: #e2e8f0; }
.th-err-title { font-size: 15px; font-weight: 700; color: #0f172a; }
.dark .th-err-title { color: #f1f5f9; }
.th-err-desc { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 12px; }
.th-err-causes { font-size: 12px; color: #475569; line-height: 1.7; white-space: pre-line; }
.th-err-fix { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px; margin-top: 12px; font-size: 12px; color: #166534; line-height: 1.7; white-space: pre-line; }

/* ===== SECTION HEADER ===== */
.th-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.th-section-title { font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
.dark .th-section-title { color: #f1f5f9; }
.th-section-sub { font-size: 14px; color: #64748b; margin-top: 4px; }
.dark .th-section-sub { color: #94a3b8; }
.th-back-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px;
    font-size: 13px; font-weight: 600; color: #475569; cursor: pointer;
    transition: all 0.15s; text-decoration: none; font-family: 'Inter', sans-serif;
}
.th-back-btn:hover { background: #e2e8f0; color: #0f172a; }
.dark .th-back-btn { background: #1e293b; border-color: #334155; color: #94a3b8; }

/* ===== DEVICE GRID ===== */
.th-device-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
@media (max-width: 900px) { .th-device-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .th-device-grid { grid-template-columns: 1fr; } }

.th-device-card {
    position: relative; background: #fff; border: 1.5px solid #f1f5f9;
    border-radius: 16px; padding: 28px 20px 24px; cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1); overflow: hidden;
    text-decoration: none; display: flex; flex-direction: column; align-items: center; text-align: center;
}
.th-device-card::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 3px; background: var(--dev-color); opacity: 0; transition: opacity 0.2s;
}
.th-device-card:hover {
    border-color: #bfdbfe; transform: translateY(-6px);
    box-shadow: 0 16px 40px -8px rgba(0,0,0,0.08), 0 6px 16px -4px rgba(0,0,0,0.04);
}
.th-device-card:hover::after { opacity: 1; }
.dark .th-device-card { background: #1e293b; border-color: #334155; }
.dark .th-device-card:hover { border-color: #3b82f6; }

.th-dev-badge {
    position: absolute; top: 14px; right: 14px;
    font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    background: rgba(37,99,235,0.08); color: #2563eb;
}
.dark .th-dev-badge { background: rgba(96,165,250,0.15); color: #60a5fa; }

.th-dev-icon {
    width: 64px; height: 64px; border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px; transition: transform 0.3s;
}
.th-device-card:hover .th-dev-icon { transform: scale(1.08) rotate(-3deg); }
.th-dev-icon i { width: 30px; height: 30px; }
.th-dev-label { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.dark .th-dev-label { color: #f1f5f9; }
.th-dev-desc { font-size: 13px; color: #94a3b8; line-height: 1.4; margin-bottom: 12px; }
.th-dev-view {
    display: inline-flex; align-items: center; gap: 4px; font-size: 12px;
    font-weight: 600; color: #2563eb; opacity: 0; transform: translateY(4px);
    transition: all 0.2s;
}
.th-device-card:hover .th-dev-view { opacity: 1; transform: translateY(0); }
.th-dev-view i { width: 14px; height: 14px; }

/* ===== ISSUE CARDS (when device selected) ===== */
.th-issue-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 32px; }
@media (max-width: 900px) { .th-issue-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .th-issue-grid { grid-template-columns: 1fr; } }

.th-issue-card {
    background: #fff; border: 1.5px solid #f1f5f9; border-radius: 14px;
    padding: 20px; cursor: pointer; transition: all 0.25s; text-decoration: none;
    position: relative;
}
.th-issue-card:hover {
    border-color: #bfdbfe; transform: translateY(-3px);
    box-shadow: 0 8px 24px -6px rgba(0,0,0,0.06);
}
.dark .th-issue-card { background: #1e293b; border-color: #334155; }
.th-issue-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.th-issue-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.th-issue-icon i { width: 20px; height: 20px; }
.th-issue-sev {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.th-issue-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.dark .th-issue-title { color: #f1f5f9; }
.th-issue-desc { font-size: 12px; color: #64748b; line-height: 1.5; }
.th-issue-arrow {
    position: absolute; top: 20px; right: 16px; width: 28px; height: 28px;
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    background: #f1f5f9; color: #94a3b8; transition: all 0.2s;
}
.th-issue-card:hover .th-issue-arrow { background: #2563eb; color: #fff; }
.th-issue-arrow i { width: 14px; height: 14px; }

/* ===== AI BANNER ===== */
.th-ai-banner {
    display: flex; align-items: center; gap: 16px; padding: 20px 24px;
    background: linear-gradient(135deg, #eff6ff, #f0fdf4, #fefce8);
    border: 1px solid #bfdbfe; border-radius: 16px; margin-top: 8px;
}
.dark .th-ai-banner { background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(22,163,74,0.08)); border-color: rgba(37,99,235,0.2); }
.th-ai-icon-wrap {
    width: 48px; height: 48px; border-radius: 14px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.th-ai-icon-wrap i { width: 24px; height: 24px; color: #fff; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .th-search-inner { flex-direction: column; padding: 12px; }
    .th-ai-btn { width: 100%; justify-content: center; }
    .th-hero h1 { font-size: 24px; }
}
</style>

<div style="max-width:1200px;margin:0 auto;">
    <!-- ===== HERO ===== -->
    <div class="th-hero">
        <h1>Troubleshooting</h1>
        <p class="th-hero-sub" id="th-subtitle">Select your device to find the right troubleshooting guide</p>

        <!-- Search Bar -->
        <div class="th-search-wrap">
            <div class="th-search-glow"></div>
            <div class="th-search-inner">
                <i data-lucide="search"></i>
                <input type="text" id="th-search"
                       placeholder='Describe your issue (e.g., "Screen flickering", "WiFi not working")...'
                       oninput="handleSearch(this.value)">
                <a href="<?= $urlBase ?>ai" class="th-ai-btn">
                    <i data-lucide="sparkles"></i>
                    Ask AI
                </a>
            </div>
        </div>
        <div class="th-ai-hint">Not sure? Let our AI diagnose the issue step by step.</div>

        <!-- Error Code Quick Lookup -->
        <div class="th-err-pills" id="th-err-pills">
            <span style="font-size:12px;color:#94a3b8;font-weight:600;margin-right:4px;">Quick Lookup:</span>
            <button class="th-err-pill" onclick="searchErrors('BSOD')"><i data-lucide="alert-triangle"></i> BSOD</button>
            <button class="th-err-pill" onclick="searchErrors('network')"><i data-lucide="wifi"></i> Network</button>
            <button class="th-err-pill" onclick="searchErrors('hardware')"><i data-lucide="cpu"></i> Hardware</button>
            <button class="th-err-pill" onclick="searchErrors('printer')"><i data-lucide="printer"></i> Printer</button>
            <button class="th-err-pill" onclick="searchErrors('driver')"><i data-lucide="hard-drive"></i> Driver</button>
            <button class="th-err-pill" onclick="searchErrors('update')"><i data-lucide="download"></i> Update</button>
        </div>
    </div>

    <!-- Error Code Results (hidden by default) -->
    <div class="th-err-results" id="th-err-results" style="display:none;"></div>

    <!-- ===== VIEW 1: DEVICE GRID ===== -->
    <div id="view-devices">
        <div class="th-section-head">
            <div>
                <div class="th-section-title">Select Device</div>
                <div class="th-section-sub">Choose the type of device you're troubleshooting</div>
            </div>
        </div>

        <div class="th-device-grid">
            <?php foreach ($devices as $d): ?>
                <div class="th-device-card" style="--dev-color:<?= $d['fg'] ?>;" onclick="selectDevice('<?= $d['id'] ?>')">
                    <span class="th-dev-badge"><?= $d['count'] ?> guides</span>
                    <div class="th-dev-icon" style="background:<?= $d['bg'] ?>;color:<?= $d['fg'] ?>;">
                        <i data-lucide="<?= $d['icon'] ?>"></i>
                    </div>
                    <div class="th-dev-label"><?= $d['label'] ?></div>
                    <div class="th-dev-desc"><?= $d['desc'] ?></div>
                    <div class="th-dev-view"><span>View Guides</span><i data-lucide="arrow-right"></i></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- AI Banner -->
        <div class="th-ai-banner">
            <div class="th-ai-icon-wrap"><i data-lucide="bot"></i></div>
            <div style="flex:1;">
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:2px;" class="dark:text-white">Can't find your issue?</h3>
                <p style="font-size:13px;color:#475569;" class="dark:text-gray-300">Our IT Support AI can help you diagnose any problem step by step.</p>
            </div>
            <a href="<?= $urlBase ?>ai" class="th-ai-btn" style="flex-shrink:0;"><i data-lucide="bot"></i> Ask AI</a>
        </div>
    </div>

    <!-- ===== VIEW 2: DEVICE ISSUES (hidden) ===== -->
    <div id="view-issues" style="display:none;">
        <div class="th-section-head">
            <div>
                <button class="th-back-btn" onclick="showDevices()"><i data-lucide="arrow-left"></i> Back</button>
            </div>
            <div style="text-align:right;">
                <div class="th-section-title" id="th-device-title">Desktop PC</div>
                <div class="th-section-sub" id="th-device-sub">Showing troubleshooting guides for Desktop PC</div>
            </div>
        </div>
        <div class="th-issue-grid" id="th-issue-grid"></div>
        <div class="th-ai-banner" style="margin-top:8px;">
            <div class="th-ai-icon-wrap"><i data-lucide="bot"></i></div>
            <div style="flex:1;">
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:2px;" class="dark:text-white">Can't find your issue?</h3>
                <p style="font-size:13px;color:#475569;" class="dark:text-gray-300">Let our AI help you figure out what's wrong.</p>
            </div>
            <a href="<?= $urlBase ?>ai" class="th-ai-btn" style="flex-shrink:0;"><i data-lucide="bot"></i> Ask AI</a>
        </div>
    </div>
</div>

<script>
var deviceData = <?= json_encode($devices) ?>;
var sevColors = <?= json_encode($severityColors) ?>;
var currentView = 'devices';
var currentDevice = null;
var errSearchTimer = null;

// ===== SEARCH =====
function handleSearch(q) {
    if (q.length >= 2) { searchErrors(q); }
    else { document.getElementById('th-err-results').style.display = 'none'; document.getElementById('th-err-pills').style.display = ''; }
}

function searchErrors(q) {
    document.getElementById('th-err-pills').style.display = 'none';
    var el = document.getElementById('th-err-results');
    el.style.display = '';
    el.innerHTML = '<div style="text-align:center;padding:20px;color:#94a3b8;">Searching...</div>';
    fetch(APP_BASE + 'api/troubleshooting/errors.php?q=' + encodeURIComponent(q))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.length) {
                el.innerHTML = '<div style="text-align:center;padding:24px;background:#f8fafc;border-radius:14px;"><p style="font-size:14px;color:#64748b;">No error codes found for "'+esc(q)+'"</p><p style="font-size:12px;color:#94a3b8;margin-top:4px;">Try a different search or click a category above.</p></div>';
                return;
            }
            var sevC = {critical:'#dc2626',high:'#ea580c',medium:'#d97706',low:'#16a34a'};
            var html = '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;"><h3 style="font-size:18px;font-weight:700;color:#0f172a;">Error Code Results</h3><button onclick="closeErrors()" style="padding:6px 14px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Clear</button></div>';
            data.forEach(function(ec) {
                var desc = (ec.description||'').replace(/\n/g,' ').substring(0,150);
                var causes = (ec.common_causes||'').split(/[;\n]+/).filter(function(c){return c.trim();});
                var fixSteps = (ec.fix_steps||'').split(/\n/).map(function(s){return s.replace(/^\d+\.\s*/, '').trim();}).filter(function(s){return s;});
                if (fixSteps.length <= 1) {
                    fixSteps = (ec.fix_steps||'').split(/\.\s+(?=\d)/).map(function(s){return s.replace(/^\d+\.\s*/, '').trim();}).filter(function(s){return s;});
                }
                
                // Build copy report text
                var reportLines = [];
                reportLines.push('ERROR CODE: ' + ec.code);
                reportLines.push('TITLE: ' + (ec.title||''));
                reportLines.push('SEVERITY: ' + (ec.severity||'').toUpperCase());
                reportLines.push('DESCRIPTION: ' + (ec.description||'').replace(/\n/g,' '));
                reportLines.push('CAUSES: ' + (ec.common_causes||'').replace(/\n/g,' '));
                reportLines.push('FIX STEPS:');
                fixSteps.forEach(function(s,i) { reportLines.push('  '+(i+1)+'. '+s.trim()); });
                var reportText = reportLines.join('\n');
                
                html += '<div class="th-err-card">';
                html += '<div class="th-err-card-head"><span class="th-err-code">'+esc(ec.code)+'</span><span style="font-size:11px;font-weight:700;color:'+(sevC[ec.severity]||'#64748b')+';text-transform:uppercase;">'+ec.severity+'</span></div>';
                html += '<div class="th-err-title">'+esc(ec.title)+'</div>';
                html += '<div class="th-err-desc">'+esc(desc)+'...</div>';
                
                // Causes list
                html += '<div style="margin-top:10px;"><strong style="color:#dc2626;font-size:12px;">Causes:</strong></div>';
                html += '<ul style="margin:4px 0 0 18px;font-size:12px;color:#475569;line-height:1.7;">';
                causes.forEach(function(c) { html += '<li>'+esc(c.trim())+'</li>'; });
                html += '</ul>';
                
                // Fix steps (always visible, formatted as numbered list)
                html += '<div style="margin-top:12px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">';
                html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
                html += '<strong style="font-size:12px;color:#166534;">✅ Fix Steps:</strong>';
                html += '<button onclick="event.stopPropagation();copyErrorCode(\''+esc(ec.code)+'\')" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">📋 Copy Report</button>';
                html += '</div>';
                html += '<ol style="margin:0;padding-left:18px;font-size:12px;color:#166534;line-height:1.8;">';
                fixSteps.forEach(function(s) { html += '<li>'+esc(s.trim())+'</li>'; });
                html += '</ol>';
                html += '</div>';
                
                // Hidden report data for copy
                html += '<textarea class="th-err-report" style="display:none;">'+esc(reportText)+'</textarea>';
                html += '</div>';
            });
            el.innerHTML = html;
        })
        .catch(function() { el.innerHTML = '<div style="padding:12px;color:#dc2626;">Search failed.</div>'; });
}

function toggleErrDetail(card) {
    var fix = card.querySelector('.th-err-fix');
    if (fix) fix.style.display = fix.style.display === 'none' ? '' : 'none';
}

function copyErrorCode(code) {
    var cards = document.querySelectorAll('.th-err-card');
    cards.forEach(function(card) {
        var codeEl = card.querySelector('.th-err-code');
        if (codeEl && codeEl.textContent === code) {
            var report = card.querySelector('.th-err-report');
            if (report) {
                navigator.clipboard.writeText(report.value).then(function() {
                    showToast('Report copied to clipboard!', 'success');
                }).catch(function() {
                    var t = document.createElement('textarea');
                    t.value = report.value;
                    document.body.appendChild(t);
                    t.select();
                    document.execCommand('copy');
                    t.remove();
                    showToast('Report copied!', 'success');
                });
            }
        }
    });
}

function closeErrors() {
    document.getElementById('th-err-results').style.display = 'none';
    document.getElementById('th-err-results').innerHTML = '';
    document.getElementById('th-err-pills').style.display = '';
}

// ===== DEVICE SELECTION =====
function selectDevice(id) {
    var dev = deviceData.find(function(d) { return d.id === id; });
    if (!dev) return;
    currentDevice = dev;
    currentView = 'issues';
    document.getElementById('th-device-title').textContent = dev.label;
    document.getElementById('th-device-sub').textContent = 'Showing troubleshooting guides for ' + dev.label;
    document.getElementById('view-devices').style.display = 'none';
    document.getElementById('view-issues').style.display = '';
    renderIssues(dev.issues);
}

function showDevices() {
    currentView = 'devices';
    currentDevice = null;
    document.getElementById('view-devices').style.display = '';
    document.getElementById('view-issues').style.display = 'none';
    document.getElementById('th-subtitle').textContent = 'Select your device to find the right troubleshooting guide';
}

function renderIssues(issues) {
    var iconMap = {'no-display':'monitor','no-power':'power','no-sound':'volume-x','wifi-not-connecting':'wifi','bsod':'triangle-alert','slow-performance':'gauge','application-crash':'app-window','random-shutdowns':'power-off','windows-update-fails':'download','printer-offline':'printer','paper-jam':'file-warning','no-internet':'wifi-off','network-slow':'gauge','dns-issues':'globe','no-recording':'video-off','camera-offline':'camera-off','flickering-display':'sun','overheating':'thermometer','no-display-and-no-power':'monitor-off'};
    var html = '';
    issues.forEach(function(iss) {
        var sev = sevColors[iss.severity] || sevColors.info;
        var icon = iconMap[iss.slug] || 'help-circle';
        html += '<a class="th-issue-card" href="<?= $urlBase ?>troubleshoot/wizard?issue='+iss.slug+'&device='+(currentDevice?currentDevice.id:'')+'">';
        html += '<div class="th-issue-head">';
        html += '<div class="th-issue-icon" style="background:'+sev.bg+';color:'+sev.fg+';"><i data-lucide="'+icon+'"></i></div>';
        html += '<div class="th-issue-sev" style="background:'+sev.fg+';"></div>';
        html += '<div class="th-issue-title">'+esc(iss.title)+'</div>';
        html += '</div>';
        html += '<div class="th-issue-desc">'+esc(iss.desc)+'</div>';
        html += '<div class="th-issue-arrow"><i data-lucide="chevron-right"></i></div>';
        html += '</a>';
    });
    document.getElementById('th-issue-grid').innerHTML = html;
    lucide.createIcons();
}

// ===== AUTO-SELECT FROM URL =====
<?php if ($selectedDevice): ?>
selectDevice('<?= e($selectedDevice) ?>');
<?php endif; ?>

function esc(s) { if (!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

lucide.createIcons();
</script>
