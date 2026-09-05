<?php
/**
 * Equipment Database — Level 2: Brand Devices
 * Shows all devices for a specific brand in a clean table view
 */
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$brand = isset($_GET['name']) ? trim($_GET['name']) : '';
if (empty($brand)) { header('Location: ' . app_base() . 'equipment'); exit; }

$page_title = $brand . ' Equipment';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

$equipment = Database::fetchAll(
    "SELECT id, manufacturer, model_name, device_type, category, year, cpu, ram, storage,
            display_spec, ports, known_issues, tools_needed, repair_guides, location, status,
            image_url, disassembly_guide, assembly_guide, guide_videos
     FROM equipment WHERE deleted_at IS NULL AND LOWER(manufacturer) = LOWER(?)
     ORDER BY device_type, model_name",
    [$brand]
);

$typeLabels = [
    'laptop' => ['icon' => 'laptop', 'label' => 'Laptops', 'color' => '#2563eb'],
    'desktop' => ['icon' => 'monitor', 'label' => 'Desktops', 'color' => '#7c3aed'],
    'server' => ['icon' => 'server', 'label' => 'Servers', 'color' => '#dc2626'],
    'printer' => ['icon' => 'printer', 'label' => 'Printers', 'color' => '#059669'],
    'switch' => ['icon' => 'network', 'label' => 'Switches', 'color' => '#d97706'],
    'router' => ['icon' => 'router', 'label' => 'Routers', 'color' => '#0891b2'],
    'monitor' => ['icon' => 'monitor', 'label' => 'Monitors', 'color' => '#4f46e5'],
    'access point' => ['icon' => 'wifi', 'label' => 'Access Points', 'color' => '#16a34a'],
    'cctv' => ['icon' => 'camera', 'label' => 'CCTV', 'color' => '#dc2626'],
    'nvr' => ['icon' => 'hard-drive', 'label' => 'NVR', 'color' => '#9333ea'],
];

/* Group by device type */
$grouped = [];
foreach ($equipment as $eq) {
    $t = strtolower(trim($eq['device_type'] ?? 'other'));
    if (!isset($grouped[$t])) { $grouped[$t] = []; }
    $grouped[$t][] = $eq;
}
?>
<div>
    <!-- Breadcrumb -->
    <div style="margin-bottom:20px;" class="fx-reveal">
        <a href="<?= app_base() ?>equipment" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748b;text-decoration:none;margin-bottom:12px;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Equipment
        </a>
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="page-hero-ico green"><i data-lucide="badge-check"></i></div>
            <div>
                <h1 style="font-size:24px;font-weight:800;color:#111827;letter-spacing:-0.03em;" class="dark:text-gray-100"><?= e(ucfirst($brand)) ?></h1>
                <p style="font-size:13px;color:#64748b;margin-top:2px;"><?= count($equipment) ?> device model<?= count($equipment) !== 1 ? 's' : '' ?> in this brand</p>
            </div>
        </div>
    </div>

    <!-- Device Type Tabs -->
    <div class="fx-reveal" style="--fx-delay:30ms;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:6px;" id="type-tabs">
        <button class="eq-chip is-active" onclick="filterByType('', this)" style="padding:6px 14px;border:1px solid #e5e7eb;border-radius:20px;font-size:12px;font-weight:600;color:#475569;background:#fff;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <i data-lucide="grid-3x3" style="width:13px;height:13px;"></i> All (<?= count($equipment) ?>)
        </button>
        <?php foreach ($grouped as $type => $items):
            $tl = $typeLabels[$type] ?? ['icon' => 'package', 'label' => ucfirst($type), 'color' => '#64748b']; ?>
        <button class="eq-chip" onclick="filterByType('<?= e($type) ?>', this)" style="padding:6px 14px;border:1px solid #e5e7eb;border-radius:20px;font-size:12px;font-weight:600;color:#475569;background:#fff;cursor:pointer;display:flex;align-items:center;gap:6px;">
            <i data-lucide="<?= $tl['icon'] ?>" style="width:13px;height:13px;color:<?= $tl['color'] ?>;"></i> <?= e($tl['label']) ?> (<?= count($items) ?>)
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="fx-reveal" style="--fx-delay:50ms;margin-bottom:20px;position:relative;max-width:400px;">
        <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
        <input type="text" id="brand-search" placeholder="Search models..." class="form-input" style="padding-left:36px;" oninput="filterTable()">
    </div>

    <!-- Device Table -->
    <div class="fx-reveal" style="--fx-delay:70ms;" id="device-table-wrap">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #e5e7eb;background:#f8fafc;">
                        <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Model</th>
                        <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Type</th>
                        <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;" class="hide-mobile">Specs</th>
                        <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Status</th>
                        <th style="text-align:center;padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Guides</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipment as $e):
                        $t = strtolower(trim($e['device_type'] ?? 'other'));
                        $tl = $typeLabels[$t] ?? ['icon' => 'package', 'label' => ucfirst($t), 'color' => '#64748b'];
                        $issueCount = !empty($e['known_issues']) ? count(explode('|', $e['known_issues'])) : 0;
                        $hasAssembly = !empty($e['assembly_guide']);
                        $hasDisassembly = !empty($e['disassembly_guide']);
                        $guideCount = ($hasAssembly ? 1 : 0) + ($hasDisassembly ? 1 : 0);
                        $specParts = array_filter([$e['cpu'], $e['ram'], $e['storage']]);
                        $specShort = implode(' · ', array_slice($specParts, 0, 2));
                    ?>
                    <tr class="device-row" data-type="<?= e($t) ?>" data-search="<?= e(strtolower($e['model_name'] . ' ' . $e['device_type'] . ' ' . $e['year'])) ?>"
                        style="border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background 0.1s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"
                        onclick="openDeviceViewer(<?= $e['id'] ?>)">
                        <td style="padding:14px 20px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <?php if ($e['image_url']): ?>
                                    <div style="width:44px;height:44px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;border:1px solid #f1f5f9;">
                                        <img src="<?= e($e['image_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<i data-lucide=&quot;package&quot; style=&quot;width:20px;height:20px;color:#cbd5e1;&quot;></i>'">
                                    </div>
                                <?php else: ?>
                                    <div style="width:44px;height:44px;border-radius:10px;background:<?= $tl['color'] ?>15;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i data-lucide="<?= $tl['icon'] ?>" style="width:20px;height:20px;color:<?= $tl['color'] ?>;"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-size:14px;font-weight:700;color:#111827;"><?= e($e['model_name']) ?></div>
                                    <div style="font-size:11px;color:#94a3b8;"><?= e(ucfirst($e['manufacturer'])) ?><?= $e['year'] ? ' · ' . e($e['year']) : '' ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:<?= $tl['color'] ?>10;border:1px solid <?= $tl['color'] ?>25;border-radius:8px;font-size:11px;color:<?= $tl['color'] ?>;font-weight:600;">
                                <i data-lucide="<?= $tl['icon'] ?>" style="width:11px;height:11px;"></i> <?= e($tl['label']) ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px;" class="hide-mobile">
                            <span style="font-size:12px;color:#64748b;"><?= e($specShort ?: '—') ?></span>
                        </td>
                        <td style="padding:14px 16px;">
                            <?php if ($issueCount > 0): ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;font-size:11px;color:#92400e;font-weight:600;">
                                    <?= $issueCount ?> known issue<?= $issueCount > 1 ? 's' : '' ?>
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:11px;color:#166534;font-weight:600;">
                                    No issues
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px 16px;text-align:center;">
                            <?php if ($guideCount > 0): ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:11px;color:#2563eb;font-weight:600;">
                                    <i data-lucide="book-open" style="width:11px;height:11px;"></i> <?= $guideCount ?>
                                </span>
                            <?php else: ?>
                                <span style="font-size:11px;color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div id="brand-empty" style="display:none;text-align:center;padding:40px;">
                <i data-lucide="search" style="width:32px;height:32px;color:#cbd5e1;margin-bottom:8px;"></i>
                <p style="color:#94a3b8;font-size:13px;">No devices match your search.</p>
            </div>
        </div>
    </div>
</div>

<!-- Device Viewer Modal -->
<div id="dv-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);z-index:10000;" onclick="closeDeviceViewer()"></div>
<div id="dv-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;background:#fff;z-index:10001;box-shadow:-8px 0 30px rgba(0,0,0,0.2);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="dv-title" style="font-size:16px;font-weight:800;color:#111827;">Device Details</h2>
        <button onclick="closeDeviceViewer()" style="background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;transition:background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
    </div>
    <div id="dv-content" style="padding:24px;"></div>
</div>

<script>
var eqData = <?= json_encode($equipment) ?>;
var currentType = '';

function filterByType(type, btn) {
    currentType = type;
    document.querySelectorAll('#type-tabs .eq-chip').forEach(function(c) { c.classList.remove('is-active'); });
    btn.classList.add('is-active');
    filterTable();
}

function filterTable() {
    var search = (document.getElementById('brand-search').value || '').toLowerCase();
    var visible = 0;
    document.querySelectorAll('.device-row').forEach(function(row) {
        var matchType = !currentType || row.dataset.type === currentType;
        var matchSearch = !search || row.dataset.search.indexOf(search) !== -1;
        var show = matchType && matchSearch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('brand-empty').style.display = visible === 0 ? 'block' : 'none';
}

function openDeviceViewer(id) {
    var art = null;
    for (var i = 0; i < eqData.length; i++) { if (eqData[i].id == id) { art = eqData[i]; break; } }
    if (!art) return;
    document.getElementById('dv-title').textContent = art.manufacturer + ' ' + art.model_name;

    var html = '';
    // Product Image — hide container on error
    if (art.image_url) {
        html += '<div id="dv-img-wrap" style="text-align:center;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">';
        html += '<img src="' + esc(art.image_url) + '" alt="" style="max-height:180px;max-width:100%;object-fit:contain;" onerror="this.parentElement.style.display=\'none\'">';
        html += '</div>';
    }
    // Header
    html += '<div style="margin-bottom:16px;"><h1 style="font-size:18px;font-weight:800;color:#111827;">' + esc(art.manufacturer) + ' ' + esc(art.model_name) + '</h1>';
    html += '<div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap;">';
    html += '<span style="padding:2px 8px;background:#eff6ff;border-radius:12px;font-size:11px;color:#2563eb;font-weight:600;">' + esc(art.device_type) + '</span>';
    if (art.year) html += '<span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;">' + esc(art.year) + '</span>';
    if (art.location) html += '<span style="padding:2px 8px;background:#f0fdf4;border-radius:12px;font-size:11px;color:#166534;font-weight:600;">' + esc(art.location) + '</span>';
    html += '</div></div>';

    // Specs — only show rows with real values (skip null/undefined/N/A/empty)
    var specs = [['CPU',art.cpu],['RAM',art.ram],['Storage',art.storage],['Display',art.display_spec],['Ports',art.ports]];
    var specHtml = '';
    specs.forEach(function(s){
        if(s[1] && String(s[1]).trim() !== '' && String(s[1]).toUpperCase() !== 'N/A' && String(s[1]).toLowerCase() !== 'null' && String(s[1]) !== 'undefined') {
            specHtml+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;"><span style="font-size:12px;color:#64748b;">'+s[0]+'</span><span style="font-size:12px;color:#111827;font-weight:600;text-align:right;max-width:60%;">'+esc(s[1])+'</span></div>';
        }
    });
    if (specHtml) html += '<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Specifications</h3>'+specHtml+'</div>';

    // Tools as image grid with hover
    if (art.tools_needed) {
        var tools = []; try { tools = JSON.parse(art.tools_needed); } catch(e) { tools = art.tools_needed.split(',').map(function(t){return {name:t.trim(),desc:'',howto:'',image:''};}); }
        if (tools.length && tools[0] && tools[0].name) {
            html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Required Tools ('+tools.length+')</h3>';
            html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">';
            tools.forEach(function(t){
                html += '<div onclick="showToolPopup(\''+esc(t.name).replace(/'/g,"\\'")+'\',\''+esc(t.desc).replace(/'/g,"\\'")+'\',\''+esc(t.howto).replace(/'/g,"\\'")+'\',\''+esc(t.image).replace(/'/g,"\\'")+'\')" style="position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #e5e7eb;background:#f8fafc;text-align:center;" onmouseover="this.style.borderColor=\'#2563eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'flex\'" onmouseout="this.style.borderColor=\'#e5e7eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'none\'">';
                if(t.image)html+='<img src="'+esc(t.image)+'" style="width:100%;height:80px;object-fit:cover;" onerror="this.style.display=\'none\'">';
                else html+='<div style="height:80px;display:flex;align-items:center;justify-content:center;"><i data-lucide="wrench" style="width:24px;height:24px;color:#cbd5e1;"></i></div>';
                html+='<div style="padding:4px 6px;font-size:10px;font-weight:600;color:#374151;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(t.name)+'</div>';
                html+='<div class="tov" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);color:#fff;align-items:center;justify-content:center;padding:8px;font-size:11px;font-weight:700;border-radius:8px;">'+esc(t.name)+'</div>';
                html+='</div>';
            });
            html+='</div></div>';
        }
    }

    // Known Issues
    if (art.known_issues) {
        var issues = art.known_issues.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        if (issues.length) {
            html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Known Issues (' + issues.length + ')</h3>';
            issues.forEach(function(issue) {
                html += '<div style="padding:8px 12px;background:#fef3c7;border-radius:8px;border-left:3px solid #d97706;margin-bottom:6px;">';
                html += '<div style="font-size:12px;color:#92400e;line-height:1.5;">' + esc(issue) + '</div></div>';
            });
            html += '</div>';
        }
    }

    // Disassembly Guide
    if (art.disassembly_guide) {
        var dSteps = art.disassembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:8px;">Disassembly Guide ('+dSteps.length+' steps)</h3>';
        html += '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;">';
        dSteps.forEach(function(step,i){
            var videoUrl = ''; var vm = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (vm) { videoUrl = vm[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;'+(i<dSteps.length-1?'border-bottom:1px solid #fecaca;':'')+'">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">'+(i+1)+'</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#991b1b;line-height:1.5;">'+esc(step.replace(/^\d+\.\s*/,''))+'</div>';
            if (videoUrl) html += '<a href="'+esc(videoUrl)+'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#fee2e2;border-radius:6px;font-size:10px;color:#991b1b;font-weight:600;text-decoration:none;"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    // Assembly Guide
    if (art.assembly_guide) {
        var aSteps = art.assembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#166534;margin-bottom:8px;">Assembly Guide ('+aSteps.length+' steps)</h3>';
        html += '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;">';
        aSteps.forEach(function(step,i){
            var videoUrl = ''; var vm = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (vm) { videoUrl = vm[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;'+(i<aSteps.length-1?'border-bottom:1px solid #bbf7d0;':'')+'">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">'+(i+1)+'</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#166534;line-height:1.5;">'+esc(step.replace(/^\d+\.\s*/,''))+'</div>';
            if (videoUrl) html += '<a href="'+esc(videoUrl)+'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#dcfce7;border-radius:6px;font-size:10px;color:#166534;font-weight:600;text-decoration:none;"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    // Repair Guides
    if (art.repair_guides) {
        var guides = art.repair_guides.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Repair Guides (' + guides.length + ')</h3>';
        guides.forEach(function(g, i) {
            var parts = g.split(':');
            var title = parts.length > 1 ? parts[0].trim() : 'Guide ' + (i+1);
            var desc = parts.length > 1 ? parts.slice(1).join(':').trim() : g.trim();
            html += '<div style="display:flex;gap:10px;padding:10px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:6px;">';
            html += '<div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;">' + (i+1) + '</div>';
            html += '<div><div style="font-size:12px;font-weight:700;color:#111827;">' + esc(title) + '</div>';
            html += '<div style="font-size:11.5px;color:#64748b;line-height:1.5;">' + esc(desc) + '</div></div></div>';
        });
        html += '</div>';
    }

    document.getElementById('dv-content').innerHTML = html;
    document.getElementById('dv-overlay').style.display = 'block';
    document.getElementById('dv-panel').style.display = 'block';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeDeviceViewer() {
    document.getElementById('dv-overlay').style.display = 'none';
    document.getElementById('dv-panel').style.display = 'none';
}

// Move viewer outside page-content (transform breaks position:fixed)
(function() {
    var ov = document.getElementById('dv-overlay');
    var md = document.getElementById('dv-panel');
    if (ov) document.body.appendChild(ov);
    if (md) document.body.appendChild(md);
})();

function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function showToolPopup(name, desc, howto, image) {
    var body = '<div style="text-align:center;margin-bottom:12px;">';
    if (image) body += '<img src="' + image + '" style="max-height:120px;border-radius:8px;" onerror="this.style.display=\'none\'">';
    body += '</div><h3 style="font-size:15px;font-weight:800;color:#111827;margin-bottom:8px;">' + esc(name) + '</h3>';
    if (desc) body += '<p style="font-size:12px;color:#64748b;margin-bottom:8px;">' + esc(desc) + '</p>';
    if (howto) body += '<div style="padding:8px 12px;background:#eff6ff;border-radius:8px;border-left:3px solid #2563eb;"><div style="font-size:11px;font-weight:700;color:#2563eb;margin-bottom:2px;">How to use:</div><div style="font-size:12px;color:#374151;">' + esc(howto) + '</div></div>';
    Swal.fire({title: name, html: body, icon: null, confirmButtonColor: '#2563eb', confirmButtonText: 'Close', width: 400});
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
