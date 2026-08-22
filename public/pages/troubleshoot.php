<?php
$page_title = 'Troubleshoot';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';

$query = $_GET['q'] ?? '';
$selectedDevice = $_GET['device'] ?? '';

// Device types with icons, colors, and mapped issues (slugs must match database)
$devices = [
    [
        'id' => 'desktop',
        'icon' => 'desktop',
        'label' => 'Desktop PC',
        'desc' => 'Desktop computers, workstations, towers',
        'bg' => '#eff6ff', 'fg' => '#2563eb',
        'count' => 8,
        'issues' => [
            ['icon'=>'monitor','title'=>'No Display','desc'=>'Black screen, no signal, monitor not working','severity'=>'high','slug'=>'no-display'],
            ['icon'=>'power','title'=>'No Power','desc'=>'Computer won\'t turn on, dead, no lights','severity'=>'high','slug'=>'no-power'],
            ['icon'=>'volume-x','title'=>'No Sound','desc'=>'No audio, speakers not working, muted','severity'=>'medium','slug'=>'no-sound'],
            ['icon'=>'triangle-alert','title'=>'Blue Screen (BSOD)','desc'=>'Windows crash, blue screen errors','severity'=>'high','slug'=>'bsod'],
            ['icon'=>'gauge','title'=>'Slow Performance','desc'=>'Laggy, slow boot, high resource usage','severity'=>'medium','slug'=>'slow-performance'],
            ['icon'=>'app-window','title'=>'Application Crash','desc'=>'App crash, won\'t open, not responding','severity'=>'medium','slug'=>'application-crash'],
            ['icon'=>'hard-drive','title'=>'Random Shutdowns','desc'=>'Computer shuts down unexpectedly','severity'=>'high','slug'=>'random-shutdowns'],
            ['icon'=>'key-round','title'=>'Windows Update Fails','desc'=>'Updates fail to install, error codes','severity'=>'medium','slug'=>'windows-update-fails'],
        ],
    ],
    [
        'id' => 'laptop',
        'icon' => 'laptop',
        'label' => 'Laptop',
        'desc' => 'Laptops, notebooks, ultrabooks',
        'bg' => '#f0fdf4', 'fg' => '#16a34a',
        'count' => 8,
        'issues' => [
            ['icon'=>'monitor','title'=>'No Display','desc'=>'Black screen, no signal, external monitor','severity'=>'high','slug'=>'no-display'],
            ['icon'=>'power','title'=>'No Power','desc'=>'Won\'t turn on, battery dead, charger issue','severity'=>'high','slug'=>'no-power'],
            ['icon'=>'volume-x','title'=>'No Sound','desc'=>'No audio, speakers not working','severity'=>'medium','slug'=>'no-sound'],
            ['icon'=>'wifi','title'=>'WiFi Not Connecting','desc'=>'Can\'t connect, intermittent, slow WiFi','severity'=>'high','slug'=>'wifi-not-connecting'],
            ['icon'=>'triangle-alert','title'=>'Blue Screen (BSOD)','desc'=>'Windows crash, blue screen errors','severity'=>'high','slug'=>'bsod'],
            ['icon'=>'gauge','title'=>'Slow Performance','desc'=>'Laggy, slow boot, high resource usage','severity'=>'medium','slug'=>'slow-performance'],
            ['icon'=>'app-window','title'=>'Application Crash','desc'=>'App crash, won\'t open, not responding','severity'=>'medium','slug'=>'application-crash'],
            ['icon'=>'thermometer','title'=>'Overheating','desc'=>'Fan loud, hot to touch, thermal throttle','severity'=>'high','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'printer',
        'icon' => 'printer',
        'label' => 'Printer',
        'desc' => 'Printers, MFPs, plotters',
        'bg' => '#fff7ed', 'fg' => '#ea580c',
        'count' => 3,
        'issues' => [
            ['icon'=>'wifi','title'=>'Printer Offline','desc'=>'Printer appears offline in Windows, not detected','severity'=>'high','slug'=>'printer-offline'],
            ['icon'=>'file-warning','title'=>'Paper Jam','desc'=>'Paper jam that won\'t clear, repeated jams','severity'=>'medium','slug'=>'paper-jam'],
            ['icon'=>'image','title'=>'Overheating','desc'=>'Printer overheating, thermal shutdown','severity'=>'medium','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'network',
        'icon' => 'router',
        'label' => 'Router / Switch',
        'desc' => 'Routers, switches, access points, firewalls',
        'bg' => '#ecfdf5', 'fg' => '#059669',
        'count' => 4,
        'issues' => [
            ['icon'=>'wifi-off','title'=>'No Internet','desc'=>'All devices lost internet, WAN down','severity'=>'high','slug'=>'no-internet'],
            ['icon'=>'gauge','title'=>'Network Slow','desc'=>'Slow speeds, high latency, packet loss','severity'=>'medium','slug'=>'network-slow'],
            ['icon'=>'wifi','title'=>'WiFi Not Connecting','desc'=>'Can\'t connect to WiFi, authentication fails','severity'=>'medium','slug'=>'wifi-not-connecting'],
            ['icon'=>'shield','title'=>'DNS Issues','desc'=>'Can ping IP but not domain names','severity'=>'high','slug'=>'dns-issues'],
        ],
    ],
    [
        'id' => 'server',
        'icon' => 'server',
        'label' => 'Server',
        'desc' => 'Physical servers, virtual machines',
        'bg' => '#eef2ff', 'fg' => '#4f46e5',
        'count' => 5,
        'issues' => [
            ['icon'=>'power','title'=>'No Power','desc'=>'Server not responding, needs hard reset','severity'=>'high','slug'=>'no-power'],
            ['icon'=>'monitor','title'=>'No Display','desc'=>'Server console blank, IPMI/iLO issues','severity'=>'high','slug'=>'no-display'],
            ['icon'=>'wifi','title'=>'No Internet','desc'=>'Network adapter down, VLAN misconfig','severity'=>'high','slug'=>'no-internet'],
            ['icon'=>'hard-drive','title'=>'Random Shutdowns','desc'=>'Server crashes, unexpected restarts','severity'=>'high','slug'=>'random-shutdowns'],
            ['icon'=>'gauge','title'=>'Slow Performance','desc'=>'CPU spike, memory leak, disk I/O bottleneck','severity'=>'medium','slug'=>'slow-performance'],
        ],
    ],
    [
        'id' => 'monitor',
        'icon' => 'monitor-speaker',
        'label' => 'Monitor',
        'desc' => 'Displays, projectors, digital signage',
        'bg' => '#f5f3ff', 'fg' => '#7c3aed',
        'count' => 3,
        'issues' => [
            ['icon'=>'monitor','title'=>'No Display','desc'=>'Monitor shows no signal, check cable source','severity'=>'high','slug'=>'no-display'],
            ['icon'=>'sun','title'=>'Flickering Display','desc'=>'Screen flickers, brightness issues','severity'=>'medium','slug'=>'flickering-display'],
            ['icon'=>'scan-eye','title'=>'No Display and Power','desc'=>'Monitor completely dead, no power LED','severity'=>'high','slug'=>'no-display-and-no-power'],
        ],
    ],
    [
        'id' => 'cctv',
        'icon' => 'camera',
        'label' => 'CCTV / NVR',
        'desc' => 'IP cameras, DVR, NVR systems',
        'bg' => '#f8fafc', 'fg' => '#475569',
        'count' => 3,
        'issues' => [
            ['icon'=>'camera-off','title'=>'Camera Offline','desc'=>'Camera not accessible, PoE or network issue','severity'=>'high','slug'=>'camera-offline'],
            ['icon'=>'circle-slash','title'=>'No Recording','desc'=>'NVR/DVR not recording, storage full','severity'=>'high','slug'=>'no-recording'],
            ['icon'=>'scan-eye','title'=>'Overheating','desc'=>'NVR overheating, fans not spinning','severity'=>'medium','slug'=>'overheating'],
        ],
    ],
    [
        'id' => 'pos',
        'icon' => 'credit-card',
        'label' => 'POS System',
        'desc' => 'Point of Sale terminals, registers',
        'bg' => '#fdf2f8', 'fg' => '#db2777',
        'count' => 4,
        'issues' => [
            ['icon'=>'power','title'=>'No Power','desc'=>'POS terminal dead, power supply issue','severity'=>'high','slug'=>'no-power'],
            ['icon'=>'wifi','title'=>'No Internet','desc'=>'Can\'t connect to server, offline mode','severity'=>'high','slug'=>'no-internet'],
            ['icon'=>'app-window','title'=>'Application Crash','desc'=>'POS app freezing, not responding','severity'=>'high','slug'=>'application-crash'],
            ['icon'=>'printer','title'=>'Printer Offline','desc'=>'Receipt printer offline or jammed','severity'=>'medium','slug'=>'printer-offline'],
        ],
    ],
    [
        'id' => 'other',
        'icon' => 'circle-help',
        'label' => 'Other Device',
        'desc' => 'Scanners, projectors, IoT, peripherals',
        'bg' => '#f1f5f9', 'fg' => '#64748b',
        'count' => 2,
        'issues' => [
            ['icon'=>'help-circle','title'=>'Help Me Diagnose','desc'=>'Not sure what\'s wrong? Let us help you figure it out','severity'=>'info','slug'=>'slow-performance'],
            ['icon'=>'wrench','title'=>'Application Crash','desc'=>'General hardware or software issue','severity'=>'medium','slug'=>'application-crash'],
        ],
    ],
];

// Build issue color maps
$severityColors = ['high'=>['bg'=>'#fef2f2','fg'=>'#dc2626','dot'=>'high'], 'medium'=>['bg'=>'#fffbeb','fg'=>'#d97706','dot'=>'medium'], 'info'=>['bg'=>'#eff6ff','fg'=>'#2563eb','dot'=>'info']];
$issueIconBg = [
    'monitor'=>'#eff6ff','power'=>'#fef2f2','volume-x'=>'#faf5ff','wifi'=>'#f0fdf4','wifi-off'=>'#fef2f2',
    'triangle-alert'=>'#fef2f2','gauge'=>'#f0f9ff','app-window'=>'#fdf2f8','hard-drive'=>'#ecfdf5',
    'key-round'=>'#f5f3ff','printer'=>'#fff7ed','camera'=>'#f8fafc','camera-off'=>'#fef2f2',
    'file-warning'=>'#fff7ed','image'=>'#faf5ff','sun'=>'#fffbeb','scan-eye'=>'#f5f3ff',
    'shield'=>'#f0fdf4','circle-slash'=>'#fef2f2','circle-help'=>'#f1f5f9','wrench'=>'#f1f5f9',
    'credit-card'=>'#fdf2f8','router'=>'#ecfdf5','monitor-speaker'=>'#f5f3ff',
];
$issueIconFg = [
    'monitor'=>'#2563eb','power'=>'#dc2626','volume-x'=>'#9333ea','wifi'=>'#16a34a','wifi-off'=>'#dc2626',
    'triangle-alert'=>'#dc2626','gauge'=>'#0284c7','app-window'=>'#db2777','hard-drive'=>'#059669',
    'key-round'=>'#7c3aed','printer'=>'#ea580c','camera'=>'#475569','camera-off'=>'#dc2626',
    'file-warning'=>'#ea580c','image'=>'#9333ea','sun'=>'#d97706','scan-eye'=>'#7c3aed',
    'shield'=>'#16a34a','circle-slash'=>'#dc2626','circle-help'=>'#64748b','wrench'=>'#64748b',
    'credit-card'=>'#db2777','router'=>'#059669','monitor-speaker'=>'#7c3aed',
];
?>

<style>
/* Troubleshoot Two-Step Styles */
.ts-hero { text-align: center; padding: 24px 0 20px; }
.ts-hero h1 { font-size: 28px; font-weight: 800; color: #111827; letter-spacing: -0.03em; margin-bottom: 6px; }
.dark .ts-hero h1 { color: #f1f5f9; }
.ts-hero p { font-size: 15px; color: #64748b; margin-bottom: 24px; }
.dark .ts-hero p { color: #94a3b8; }

/* Glowing Search Bar */
.ts-search-wrap { max-width: 680px; margin: 0 auto 28px; position: relative; }
.ts-search-glow { position: absolute; inset: -2px; background: linear-gradient(135deg, #3b82f6, #8b5cf6, #3b82f6); border-radius: 18px; opacity: 0.3; filter: blur(8px); z-index: 0; transition: opacity 0.3s; }
.ts-search-wrap:focus-within .ts-search-glow { opacity: 0.5; }
.ts-search-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.85); backdrop-filter: blur(16px) saturate(200%); border: 1px solid #e5e7eb; border-radius: 16px; padding: 6px 6px 6px 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.25s; }
.ts-search-inner:focus-within { border-color: #3b82f6; box-shadow: 0 4px 24px rgba(37,99,235,0.12); }
.dark .ts-search-inner { background: rgba(30,41,59,0.85); border-color: #334155; }
.dark .ts-search-inner:focus-within { border-color: #3b82f6; }
.ts-search-inner svg, .ts-search-inner i { width: 20px; height: 20px; color: #94a3b8; flex-shrink: 0; }
.ts-search-inner input { flex: 1; border: none; background: transparent; font-size: 15px; color: #1e293b; outline: none; padding: 12px 8px; }
.ts-search-inner input::placeholder { color: #94a3b8; }
.dark .ts-search-inner input { color: #f1f5f9; }
.ts-ai-btn { display: flex; align-items: center; gap: 8px; padding: 12px 22px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s; text-decoration: none; box-shadow: 0 2px 8px rgba(37,99,235,0.3); }
.ts-ai-btn:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); box-shadow: 0 4px 14px rgba(37,99,235,0.4); transform: translateY(-1px); }
.ts-ai-btn i, .ts-ai-btn svg { width: 16px; height: 16px; }

/* Section Headers */
.ts-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.ts-section-title { font-size: 18px; font-weight: 700; color: #111827; }
.dark .ts-section-title { color: #f1f5f9; }
.ts-back-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.ts-back-btn:hover { background: #e2e8f0; color: #1e293b; }
.dark .ts-back-btn { background: #1e293b; border-color: #334155; color: #94a3b8; }
.dark .ts-back-btn:hover { background: #334155; color: #f1f5f9; }
.ts-back-btn i, .ts-back-btn svg { width: 15px; height: 15px; }

/* Device Cards */
.ts-device-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 28px 16px 24px;
    background: #fff;
    border: 1.5px solid #f1f5f9;
    border-radius: 16px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    overflow: hidden;
}
.ts-device-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--device-color);
    opacity: 0;
    transition: opacity 0.2s;
}
.ts-device-card:hover {
    border-color: #bfdbfe;
    box-shadow: 0 12px 32px -6px rgba(0,0,0,0.08), 0 4px 12px -4px rgba(0,0,0,0.04);
    transform: translateY(-4px);
}
.ts-device-card:hover::after { opacity: 1; }
.dark .ts-device-card { background: #1e293b; border-color: #334155; }
.dark .ts-device-card:hover { border-color: #3b82f6; }

.ts-device-icon {
    width: 60px; height: 60px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    transition: transform 0.3s;
}
.ts-device-card:hover .ts-device-icon { transform: scale(1.1) rotate(-3deg); }
.ts-device-icon i, .ts-device-icon svg { width: 28px; height: 28px; }
.ts-device-label { font-size: 15px; font-weight: 700; color: #111827; text-align: center; }
.dark .ts-device-label { color: #f1f5f9; }
.ts-device-desc { font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.4; }
.ts-device-count { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #2563eb; background: #eff6ff; padding: 3px 10px; border-radius: 20px; margin-top: 4px; }
.dark .ts-device-count { background: rgba(37,99,235,0.15); color: #60a5fa; }

/* Issue Cards (filtered view) */
.ts-issue-card {
    display: flex;
    align-items: stretch;
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 20px;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
    cursor: pointer;
}
.ts-issue-card:hover {
    border-color: #bfdbfe;
    box-shadow: 0 8px 24px -4px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.dark .ts-issue-card { background: #1e293b; border-color: #334155; }
.dark .ts-issue-card:hover { border-color: #3b82f6; }

.ts-issue-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: transform 0.25s;
}
.ts-issue-card:hover .ts-issue-icon { transform: scale(1.08); }
.ts-issue-icon i, .ts-issue-icon svg { width: 22px; height: 22px; }
.ts-issue-body { flex: 1; margin-left: 14px; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
.ts-issue-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #111827; }
.dark .ts-issue-title { color: #f1f5f9; }
.ts-issue-desc { font-size: 13px; color: #64748b; margin-top: 3px; line-height: 1.4; }
.dark .ts-issue-desc { color: #94a3b8; }
.ts-issue-arrow { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; flex-shrink: 0; align-self: center; transition: all 0.2s; }
.ts-issue-card:hover .ts-issue-arrow { background: #2563eb; }
.ts-issue-arrow i, .ts-issue-arrow svg { width: 16px; height: 16px; color: #94a3b8; }
.ts-issue-card:hover .ts-issue-arrow i, .ts-issue-card:hover .ts-issue-arrow svg { color: #fff; }
.dark .ts-issue-arrow { background: #334155; }
.dark .ts-issue-arrow i, .dark .ts-issue-arrow svg { color: #64748b; }

/* Severity Dots */
.sev-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.sev-dot.high { background: #ef4444; }
.sev-dot.medium { background: #f59e0b; }
.sev-dot.info { background: #3b82f6; }

/* AI Banner */
.ts-ai-banner { background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 50%, #f0fdf4 100%); border: 1px solid #bfdbfe; border-radius: 16px; padding: 24px 28px; display: flex; align-items: center; gap: 20px; position: relative; overflow: hidden; }
.ts-ai-banner::before { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(37,99,235,0.06); border-radius: 50%; }
.dark .ts-ai-banner { background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(139,92,246,0.06), rgba(16,163,74,0.04)); border-color: #334155; }
.ts-ai-icon-wrap { width: 52px; height: 52px; border-radius: 14px; background: rgba(37,99,235,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ts-ai-icon-wrap svg, .ts-ai-icon-wrap i { width: 24px; height: 24px; color: #2563eb; }

/* View transitions */
.ts-view { animation: tsFadeIn 0.3s ease; }
@keyframes tsFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

/* Responsive */
@media (max-width: 900px) { .ts-device-grid { grid-template-columns: repeat(3, 1fr) !important; } .ts-search-inner { flex-direction: column; padding: 12px; gap: 10px; } .ts-search-inner input { padding: 10px; } .ts-ai-btn { width: 100%; justify-content: center; } }
@media (max-width: 600px) { .ts-device-grid { grid-template-columns: repeat(2, 1fr) !important; } .ts-ai-banner { flex-direction: column; text-align: center; } .ts-issue-grid { grid-template-columns: 1fr !important; } }
</style>

<div style="max-width:1200px;margin:0 auto;">

    <!-- Hero -->
    <div class="ts-hero">
        <h1>Troubleshooting</h1>
        <p id="ts-subtitle">Select your device type to see relevant troubleshooting guides</p>
    </div>

    <!-- Search Bar with AI Button -->
    <div class="ts-search-wrap">
        <div class="ts-search-glow"></div>
        <div class="ts-search-inner">
            <i data-lucide="search"></i>
            <input type="text" id="troubleshoot-search"
                   value="<?= e($query) ?>"
                   placeholder='Describe your problem (e.g., "My monitor is black" or "WiFi is slow")...'
                   onkeyup="filterCurrentView(this.value)">
            <a href="<?= $urlBase ?>ai" class="ts-ai-btn">
                <i data-lucide="sparkles"></i>
                Ask IT Support AI
            </a>
        </div>
    </div>

    <!-- VIEW 1: Device Type Selection -->
    <div id="view-devices" class="ts-view">
        <div class="ts-device-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:32px;">
            <?php foreach ($devices as $d): ?>
                <div class="ts-device-card" style="--device-color:<?= $d['fg'] ?>;" onclick="selectDevice('<?= $d['id'] ?>')">
                    <div class="ts-device-icon" style="background:<?= $d['bg'] ?>;color:<?= $d['fg'] ?>;">
                        <i data-lucide="<?= $d['icon'] ?>"></i>
                    </div>
                    <div class="ts-device-label"><?= $d['label'] ?></div>
                    <div class="ts-device-desc"><?= $d['desc'] ?></div>
                    <div class="ts-device-count"><?= $d['count'] ?> guides</div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- AI Banner -->
        <div class="ts-ai-banner">
            <div class="ts-ai-icon-wrap">
                <i data-lucide="bot"></i>
            </div>
            <div style="flex:1;position:relative;z-index:1;">
                <h3 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:4px;" class="dark:text-white">Not sure what's wrong?</h3>
                <p style="font-size:14px;color:#475569;line-height:1.5;" class="dark:text-gray-300">
                    Our IT Support AI can help you diagnose the issue step by step. Just describe what you're seeing.
                </p>
            </div>
            <a href="<?= $urlBase ?>ai" class="ts-ai-btn" style="flex-shrink:0;">
                <i data-lucide="bot"></i>
                Ask IT Support AI
            </a>
        </div>

        <!-- Error Code Lookup -->
        <div style="margin-top:24px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <i data-lucide="hash" style="width:18px;height:18px;color:#6366f1;"></i>
                <h3 style="font-size:16px;font-weight:700;color:#111827;">Error Code Lookup</h3>
            </div>
            <div style="display:flex;gap:8px;margin-bottom:12px;">
                <input type="text" id="error-search" placeholder="Type an error code (e.g., BSOD, 0x80070002, CODE_43)" style="flex:1;padding:12px 16px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;background:#fff;" onkeyup="searchErrorCodes(this.value)">
            </div>
            <div id="error-results"></div>
            <div id="error-categories" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                <button onclick="searchErrorCodes('bsod')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;cursor:pointer;">BSOD</button>
                <button onclick="searchErrorCodes('update')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;cursor:pointer;">Update</button>
                <button onclick="searchErrorCodes('network')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;cursor:pointer;">Network</button>
                <button onclick="searchErrorCodes('hardware')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#fefce8;color:#ca8a04;border:1px solid #fef08a;cursor:pointer;">Hardware</button>
                <button onclick="searchErrorCodes('printer')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;cursor:pointer;">Printer</button>
                <button onclick="searchErrorCodes('driver')" style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#fdf2f8;color:#db2777;border:1px solid #fbcfe8;cursor:pointer;">Driver</button>
            </div>
        </div>
    </div>

    <!-- VIEW 2: Filtered Issues (hidden by default) -->
    <div id="view-issues" style="display:none;">
        <div class="ts-section-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="ts-back-btn" onclick="showDevices()">
                    <i data-lucide="arrow-left"></i>
                    Back
                </button>
                <div>
                    <div class="ts-section-title" id="device-title">Issues</div>
                    <div style="font-size:13px;color:#94a3b8;margin-top:1px;" id="device-desc"></div>
                </div>
            </div>
            <div class="ts-device-count" id="issue-count" style="font-size:12px;"></div>
        </div>
        <div class="ts-issue-grid" id="issue-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:32px;"></div>

        <!-- AI Banner in issues view too -->
        <div class="ts-ai-banner" style="margin-top:8px;">
            <div class="ts-ai-icon-wrap">
                <i data-lucide="sparkles"></i>
            </div>
            <div style="flex:1;">
                <h3 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:2px;" class="dark:text-white">Can't find your issue?</h3>
                <p style="font-size:13px;color:#64748b;" class="dark:text-gray-300">Let our AI help you figure out what's wrong.</p>
            </div>
            <a href="<?= $urlBase ?>ai" class="ts-ai-btn" style="padding:10px 18px;font-size:13px;">
                <i data-lucide="sparkles"></i> Ask AI
            </a>
        </div>
    </div>
</div>

<script>
// Device data with issues
var deviceData = <?= json_encode($devices) ?>;
var severityColors = <?= json_encode($severityColors) ?>;
var issueIconBg = <?= json_encode($issueIconBg) ?>;
var issueIconFg = <?= json_encode($issueIconFg) ?>;

var currentView = 'devices';
var currentDeviceId = null;
var errorSearchTimer = null;

// Error Code Search
function searchErrorCodes(query) {
    clearTimeout(errorSearchTimer);
    errorSearchTimer = setTimeout(function() {
        var el = document.getElementById('error-results');
        if (!query || query.length < 2) {
            el.innerHTML = '';
            return;
        }
        el.innerHTML = '<div style="padding:12px;color:#94a3b8;font-size:13px;">Searching...</div>';
        fetch(APP_BASE + 'api/troubleshooting/errors.php?q=' + encodeURIComponent(query))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.length) {
                    el.innerHTML = '<div style="padding:16px;background:#f8fafc;border-radius:10px;text-align:center;"><p style="font-size:14px;color:#64748b;">No error codes found for "' + escapeHtml(query) + '"</p><p style="font-size:12px;color:#94a3b8;margin-top:4px;">Try a different search term or check the category buttons below.</p></div>';
                    return;
                }
                var html = '<div style="display:flex;flex-direction:column;gap:10px;">';
                data.forEach(function(ec) {
                    var sevColors = {critical:'#dc2626',high:'#ea580c',medium:'#d97706',low:'#16a34a'};
                    var catColors = {bsod:'#fef2f2',windows:'#eff6ff',network:'#f0fdf4',hardware:'#fefce8',printer:'#fff7ed',driver:'#fdf2f8',update:'#f0f9ff'};
                    var catTextColors = {bsod:'#dc2626',windows:'#2563eb',network:'#16a34a',hardware:'#ca8a04',printer:'#ea580c',driver:'#db2777',update:'#0284c7'};
                    var sevColor = sevColors[ec.severity] || '#64748b';
                    var bgColor = catColors[ec.category] || '#f8fafc';
                    var catColor = catTextColors[ec.category] || '#64748b';
                    html += '<div onclick="showErrorDetail(' + ec.id + ')" style="padding:16px;background:' + bgColor + ';border:1px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.08)\'" onmouseout="this.style.boxShadow=\'none\'">';
                    html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">';
                    html += '<code style="font-size:14px;font-weight:700;color:#111827;background:rgba(0,0,0,0.06);padding:2px 8px;border-radius:4px;">' + escapeHtml(ec.code) + '</code>';
                    html += '<span style="font-size:11px;font-weight:600;color:' + sevColor + ';text-transform:uppercase;">' + ec.severity + '</span>';
                    html += '</div>';
                    html += '<div style="font-size:14px;font-weight:600;color:#374151;margin-bottom:4px;">' + escapeHtml(ec.title) + '</div>';
                    html += '<div style="font-size:12px;color:#64748b;line-height:1.5;">' + escapeHtml(ec.description).substring(0, 120) + '...</div>';
                    html += '<div style="display:flex;gap:6px;margin-top:8px;"><span style="font-size:11px;padding:2px 8px;border-radius:10px;background:rgba(0,0,0,0.05);color:' + catColor + ';font-weight:600;">' + ec.category + '</span></div>';
                    html += '</div>';
                });
                html += '</div>';
                el.innerHTML = html;
            })
            .catch(function() {
                el.innerHTML = '<div style="padding:12px;color:#dc2626;font-size:13px;">Search failed. Please try again.</div>';
            });
    }, 300);
}

function showErrorDetail(id) {
    fetch(APP_BASE + 'api/troubleshooting/errors.php?id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(ec) {
            var sevColors = {critical:'#dc2626',high:'#ea580c',medium:'#d97706',low:'#16a34a'};
            var sevColor = sevColors[ec.severity] || '#64748b';
            var html = '<div style="padding:20px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;margin-top:12px;">';
            html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">';
            html += '<div><code style="font-size:16px;font-weight:800;color:#111827;background:#f1f5f9;padding:4px 12px;border-radius:6px;">' + escapeHtml(ec.code) + '</code>';
            html += '<span style="margin-left:8px;font-size:12px;font-weight:600;color:' + sevColor + ';">' + ec.severity.toUpperCase() + '</span></div>';
            html += '<button onclick="document.getElementById(\'error-results\').innerHTML=\'\'" style="padding:6px 12px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;font-size:13px;">Close</button>';
            html += '</div>';
            html += '<h3 style="font-size:18px;font-weight:700;color:#111827;margin-bottom:12px;">' + escapeHtml(ec.title) + '</h3>';
            html += '<p style="font-size:14px;color:#475569;line-height:1.7;margin-bottom:16px;">' + escapeHtml(ec.description) + '</p>';
            html += '<div style="margin-bottom:16px;">';
            html += '<h4 style="font-size:13px;font-weight:700;color:#dc2626;margin-bottom:8px;display:flex;align-items:center;gap:6px;">Common Causes</h4>';
            var causes = ec.common_causes.split(',');
            html += '<ul style="padding-left:20px;">';
            causes.forEach(function(c) { html += '<li style="font-size:13px;color:#475569;line-height:1.8;">' + escapeHtml(c.trim()) + '</li>'; });
            html += '</ul></div>';
            html += '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;">';
            html += '<h4 style="font-size:13px;font-weight:700;color:#166534;margin-bottom:8px;">Fix Steps</h4>';
            var steps = ec.fix_steps.split(/\\n|\.\s+/);
            steps.forEach(function(s) {
                if (s.trim()) html += '<div style="font-size:13px;color:#166534;line-height:1.8;padding:2px 0;">' + escapeHtml(s.trim()) + '</div>';
            });
            html += '</div></div>';
            document.getElementById('error-results').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('error-results').innerHTML = '<div style="padding:12px;color:#dc2626;font-size:13px;">Failed to load error details.</div>';
        });
}

function escapeHtml(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function selectDevice(deviceId) {
    var device = deviceData.find(function(d) { return d.id === deviceId; });
    if (!device) return;

    currentDeviceId = deviceId;
    currentView = 'issues';

    // Update header
    document.getElementById('device-title').innerHTML = '<span style="margin-right:4px;">' + device.label + '</span>';
    document.getElementById('device-desc').textContent = device.desc;
    document.getElementById('issue-count').textContent = device.issues.length + ' guides available';
    document.getElementById('ts-subtitle').textContent = 'Showing troubleshooting guides for ' + device.label;

    // Build issue cards
    var grid = document.getElementById('issue-grid');
    var html = '';
    device.issues.forEach(function(issue) {
        var sc = severityColors[issue.severity] || severityColors.info;
        var bg = issueIconBg[issue.icon] || '#f1f5f9';
        var fg = issueIconFg[issue.icon] || '#64748b';

        html += '<a href="<?= $urlBase ?>troubleshoot/wizard.php?issue=' + issue.slug + '&device=' + currentDeviceId + '" class="ts-issue-card" data-title="' + issue.title.toLowerCase() + '" data-desc="' + issue.desc.toLowerCase() + '">' +
            '<div class="ts-issue-icon" style="background:' + bg + ';color:' + fg + ';">' +
                '<i data-lucide="' + issue.icon + '"></i>' +
            '</div>' +
            '<div class="ts-issue-body">' +
                '<div class="ts-issue-title">' + issue.title + ' <span class="sev-dot ' + sc.dot + '"></span></div>' +
                '<div class="ts-issue-desc">' + issue.desc + '</div>' +
            '</div>' +
            '<div class="ts-issue-arrow"><i data-lucide="chevron-right"></i></div>' +
        '</a>';
    });
    grid.innerHTML = html;

    // Switch views with animation
    document.getElementById('view-devices').style.display = 'none';
    document.getElementById('view-issues').style.display = 'block';
    document.getElementById('view-issues').className = 'ts-view';

    // Re-render icons
    try { lucide.createIcons(); } catch(e) {}
}

function showDevices() {
    currentView = 'devices';
    currentDeviceId = null;
    document.getElementById('view-devices').style.display = 'block';
    document.getElementById('view-devices').className = 'ts-view';
    document.getElementById('view-issues').style.display = 'none';
    document.getElementById('ts-subtitle').textContent = 'Select your device type to see relevant troubleshooting guides';
    document.getElementById('troubleshoot-search').value = '';
}

function filterCurrentView(query) {
    var q = query.toLowerCase().trim();

    if (currentView === 'devices') {
        // Search across all devices and their issues
        var cards = document.querySelectorAll('#view-devices .ts-device-card');
        var visibleCount = 0;
        cards.forEach(function(card, i) {
            var device = deviceData[i];
            var matchDevice = device.label.toLowerCase().indexOf(q) !== -1 || device.desc.toLowerCase().indexOf(q) !== -1;
            var matchIssue = device.issues.some(function(iss) {
                return iss.title.toLowerCase().indexOf(q) !== -1 || iss.desc.toLowerCase().indexOf(q) !== -1;
            });
            if (!q || matchDevice || matchIssue) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
    } else {
        // Search within current device's issues
        var issueCards = document.querySelectorAll('#view-issues .ts-issue-card');
        var shown = 0;
        issueCards.forEach(function(card) {
            var title = card.getAttribute('data-title') || '';
            var desc = card.getAttribute('data-desc') || '';
            if (!q || title.indexOf(q) !== -1 || desc.indexOf(q) !== -1) {
                card.style.display = '';
                shown++;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('issue-count').textContent = shown + ' guides available';
    }
}

// Auto-select device from URL parameter
var urlDevice = '<?= $selectedDevice ?>';
if (urlDevice && deviceData.find(function(d) { return d.id === urlDevice; })) {
    selectDevice(urlDevice);
}
</script>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
