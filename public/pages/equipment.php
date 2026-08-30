<?php
$page_title = 'Equipment Database';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

// Fetch equipment from database
$equipment = Database::fetchAll(
    "SELECT id, manufacturer, model_name, device_type, category, year, cpu, ram, storage,
            display_spec, ports, known_issues, tools_needed, repair_guides, location, status,
            image_url, disassembly_guide, assembly_guide, guide_videos
     FROM equipment WHERE deleted_at IS NULL ORDER BY device_type, manufacturer, model_name"
);

// Count by type
$typeCounts = [];
$brandCounts = [];
foreach ($equipment as $e) {
    $t = strtolower($e['device_type'] ?? 'other');
    $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
    $b = $e['manufacturer'] ?? 'Unknown';
    $brandCounts[$b] = ($brandCounts[$b] ?? 0) + 1;
}
arsort($brandCounts);

// Brand colors
$brandColors = [
    'Lenovo' => ['bg'=>'#fef2f2','fg'=>'#dc2626'], 'Dell' => ['bg'=>'#eff6ff','fg'=>'#2563eb'],
    'HP' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'], 'HPE' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'],
    'Brother' => ['bg'=>'#fefce8','fg'=>'#ca8a04'], 'Cisco' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'],
    'Ubiquiti' => ['bg'=>'#f8fafc','fg'=>'#111827'], 'TP-Link' => ['bg'=>'#eff6ff','fg'=>'#2563eb'],
    'Hikvision' => ['bg'=>'#fef2f2','fg'=>'#dc2626'], 'Dahua' => ['bg'=>'#fef2f2','fg'=>'#dc2626'],
];
$defaultBrandColor = ['bg'=>'#f1f5f9','fg'=>'#475569'];

// Type config
$typeConfig = [
    'laptop' => ['icon' => 'laptop', 'label' => 'Laptops'],
    'desktop' => ['icon' => 'desktop', 'label' => 'Desktops'],
    'server' => ['icon' => 'server', 'label' => 'Servers'],
    'monitor' => ['icon' => 'monitor', 'label' => 'Monitors'],
    'printer' => ['icon' => 'printer', 'label' => 'Printers'],
    'router' => ['icon' => 'router', 'label' => 'Routers'],
    'switch' => ['icon' => 'network', 'label' => 'Switches'],
    'access point' => ['icon' => 'wifi', 'label' => 'Access Points'],
    'cctv' => ['icon' => 'camera', 'label' => 'CCTV'],
    'other' => ['icon' => 'hard-drive', 'label' => 'Other'],
];
?>
<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
            <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.02em;">Equipment Database</h1>
            <p style="font-size:13px;color:#64748b;margin-top:2px;">Browse devices, models, specifications, and repair guides</p>
        </div>
    </div>

    <!-- Brands -->
    <div style="margin-bottom:24px;">
        <h2 style="font-size:14px;font-weight:700;color:#374151;margin-bottom:10px;">Browse by Brand</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach ($brandCounts as $brand => $count):
                $bc = $brandColors[$brand] ?? $defaultBrandColor;
            ?>
            <a href="<?= $urlBase ?>brand?name=<?= urlencode($brand) ?>" style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;background:<?= $bc['bg'] ?>;border:1px solid <?= $bc['fg'] ?>20;border-radius:10px;text-decoration:none;transition:all 0.15s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow='none'">
                <div style="width:28px;height:28px;border-radius:6px;background:<?= $bc['fg'] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;">
                    <?= strtoupper(substr($brand, 0, 2)) ?>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:700;color:<?= $bc['fg'] ?>;"><?= e($brand) ?></div>
                    <div style="font-size:10px;color:#94a3b8;"><?= $count ?> models</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Search -->
    <div style="margin-bottom:20px;position:relative;">
        <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
        <input type="text" id="eq-search" placeholder="Search by model, manufacturer, or serial number..." class="form-input" style="padding-left:36px;" oninput="filterEquipment()">
    </div>

    <!-- Device Categories -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;margin-bottom:28px;">
        <?php foreach ($typeConfig as $key => $tc):
            $count = $typeCounts[$key] ?? 0;
            if ($count === 0) continue;
        ?>
            <button onclick="filterByType('<?= $key ?>')" data-type="<?= $key ?>" class="eq-type-btn" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff'">
                <i data-lucide="<?= $tc['icon'] ?>" style="width:22px;height:22px;color:#2563eb;"></i>
                <span style="font-size:12px;font-weight:700;color:#111827;"><?= $tc['label'] ?></span>
                <span style="font-size:11px;color:#94a3b8;"><?= $count ?> models</span>
            </button>
        <?php endforeach; ?>
        <button onclick="filterByType('')" data-type="" class="eq-type-btn" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;background:#2563eb;border:1px solid #2563eb;border-radius:12px;cursor:pointer;transition:all 0.15s;">
            <i data-lucide="grid-3x3" style="width:22px;height:22px;color:#fff;"></i>
            <span style="font-size:12px;font-weight:700;color:#fff;">All</span>
            <span style="font-size:11px;color:#e0e7ff;"><?= count($equipment) ?> models</span>
        </button>
    </div>

    <!-- Equipment Table -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Equipment</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;" class="hide-mobile">Specs</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Issues</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;" class="hide-mobile">Location</th>
                </tr>
            </thead>
            <tbody id="eq-tbody">
                <?php foreach ($equipment as $e):
                    $issueCount = !empty($e['known_issues']) ? count(explode('|', $e['known_issues'])) : 0;
                    $specParts = array_filter([$e['cpu'], $e['ram'], $e['storage']]);
                    $specShort = implode(' · ', array_slice($specParts, 0, 2));
                    $tc = $typeConfig[strtolower($e['device_type'])] ?? $typeConfig['other'];
                ?>
                <tr class="eq-row" data-type="<?= e(strtolower($e['device_type'])) ?>" data-search="<?= e(strtolower($e['manufacturer'] . ' ' . $e['model_name'] . ' ' . $e['device_type'])) ?>" style="border-bottom:1px solid #f1f5f9;cursor:pointer;" onclick="openEqViewer(<?= $e['id'] ?>)">
                    <td style="padding:14px 20px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-lucide="<?= $tc['icon'] ?>" style="width:17px;height:17px;color:#2563eb;"></i>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827;"><a href="<?= $urlBase ?>brand?name=<?= urlencode($e['manufacturer']) ?>" style="color:#2563eb;text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?= e($e['manufacturer']) ?></a> <?= e($e['model_name']) ?></div>
                                <?php if ($e['year']): ?><div style="font-size:11px;color:#94a3b8;"><?= e($e['year']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 20px;">
                        <span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;"><?= e(ucfirst($e['device_type'])) ?></span>
                    </td>
                    <td style="padding:14px 20px;" class="hide-mobile">
                        <span style="font-size:12px;color:#64748b;"><?= e($specShort ?: 'N/A') ?></span>
                    </td>
                    <td style="padding:14px 20px;">
                        <?php if ($issueCount > 0): ?>
                            <span style="padding:2px 8px;background:#fef3c7;border-radius:12px;font-size:11px;color:#92400e;font-weight:600;"><?= $issueCount ?> known</span>
                        <?php else: ?>
                            <span style="font-size:11px;color:#94a3b8;">None</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:14px 20px;" class="hide-mobile">
                        <span style="font-size:12px;color:#64748b;"><?= e($e['location'] ?? '—') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($equipment)): ?>
            <div style="text-align:center;padding:40px;">
                <i data-lucide="package" style="width:36px;height:36px;color:#cbd5e1;margin-bottom:8px;"></i>
                <p style="color:#94a3b8;font-size:13px;">No equipment found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Equipment Viewer Modal -->
<div id="eq-viewer-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeEqViewer()"></div>
<div id="eq-viewer-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="eq-viewer-title" style="font-size:16px;font-weight:800;color:#111827;">Equipment Details</h2>
        <button onclick="closeEqViewer()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
    </div>
    <div id="eq-viewer-content" style="padding:24px;"></div>
</div>

<script>
var eqData = <?= json_encode($equipment) ?>;
var currentTypeFilter = '';

function filterEquipment() {
    var search = document.getElementById('eq-search').value.toLowerCase();
    document.querySelectorAll('.eq-row').forEach(function(row) {
        var matchSearch = !search || row.dataset.search.indexOf(search) !== -1;
        var matchType = !currentTypeFilter || row.dataset.type === currentTypeFilter;
        row.style.display = (matchSearch && matchType) ? '' : 'none';
    });
}

function filterByType(type) {
    currentTypeFilter = type;
    document.querySelectorAll('.eq-type-btn').forEach(function(btn) {
        if (btn.dataset.type === type) {
            btn.style.background = '#2563eb'; btn.style.borderColor = '#2563eb';
            btn.querySelectorAll('*').forEach(function(c) { if (c.tagName === 'SPAN' || c.tagName === 'I') c.style.color = '#fff'; });
        } else {
            btn.style.background = '#fff'; btn.style.borderColor = '#e5e7eb';
            btn.querySelectorAll('span').forEach(function(c) { c.style.color = ''; });
            btn.querySelectorAll('i').forEach(function(c) { c.style.color = '#2563eb'; });
        }
    });
    filterEquipment();
}

function openEqViewer(id) {
    var art = null;
    for (var i = 0; i < eqData.length; i++) { if (eqData[i].id == id) { art = eqData[i]; break; } }
    if (!art) return;

    var html = '';
    // Product Image
    if (art.image_url) {
        html += '<div style="text-align:center;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;">';
        html += '<img src="' + esc(art.image_url) + '" alt="' + esc(art.manufacturer + ' ' + art.model_name) + '" style="max-height:180px;max-width:100%;object-fit:contain;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">';
        html += '<div style="display:none;align-items:center;justify-content:center;height:120px;"><i data-lucide="image-off" style="width:36px;height:36px;color:#cbd5e1;"></i></div>';
        html += '</div>';
    }
    // Header
    html += '<div style="display:flex;gap:12px;margin-bottom:20px;">';
    html += '<div style="width:48px;height:48px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="cpu" style="width:24px;height:24px;color:#2563eb;"></i></div>';
    html += '<div><h1 style="font-size:18px;font-weight:800;color:#111827;">' + esc(art.manufacturer) + ' ' + esc(art.model_name) + '</h1>';
    html += '<div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;">';
    html += '<span style="padding:2px 8px;background:#eff6ff;border-radius:12px;font-size:11px;color:#2563eb;font-weight:600;">' + esc(art.device_type) + '</span>';
    if (art.year) html += '<span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;">' + esc(art.year) + '</span>';
    if (art.location) html += '<span style="padding:2px 8px;background:#f0fdf4;border-radius:12px;font-size:11px;color:#166534;font-weight:600;">' + esc(art.location) + '</span>';
    html += '</div></div></div>';

    // Specs Grid
    var specs = [
        ['CPU', art.cpu], ['RAM', art.ram], ['Storage', art.storage],
        ['Display', art.display_spec], ['Ports', art.ports]
    ];
    var specHtml = '';
    specs.forEach(function(s) {
        if (s[1]) specHtml += '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;"><span style="font-size:12px;color:#64748b;">' + s[0] + '</span><span style="font-size:12px;color:#111827;font-weight:600;text-align:right;max-width:60%;">' + esc(s[1]) + '</span></div>';
    });
    if (specHtml) {
        html += '<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Specifications</h3>';
        html += specHtml + '</div>';
    }

    // Repair Guides
    if (art.repair_guides) {
        var guides = art.repair_guides.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Repair Guides (' + guides.length + ')</h3>';
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

    // Known Issues
    if (art.known_issues) {
        var issues = art.known_issues.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Known Issues (' + issues.length + ')</h3>';
        issues.forEach(function(issue) {
            html += '<div style="padding:8px 12px;background:#fef3c7;border-radius:8px;border-left:3px solid #d97706;margin-bottom:6px;">';
            html += '<div style="font-size:12px;color:#92400e;line-height:1.5;">' + esc(issue) + '</div></div>';
        });
        html += '</div>';
    }

    // Tools as image grid with hover overlay
    if (art.tools_needed) {
        var tools = [];
        try { tools = JSON.parse(art.tools_needed); } catch(e) { tools = art.tools_needed.split(',').map(function(t){return {name:t.trim(),desc:'',howto:'',image:''};}); }
        if (tools.length && tools[0] && tools[0].name) {
            html += '<div style="margin-bottom:16px;">';
            html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Required Tools (' + tools.length + ')</h3>';
            html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">';
            tools.forEach(function(t) {
                html += '<div onclick="showToolPopup(\'' + esc(t.name).replace(/'/g, "\\'") + '\',\'' + esc(t.desc).replace(/'/g, "\\'") + '\',\'' + esc(t.howto).replace(/'/g, "\\'") + '\',\'' + esc(t.image).replace(/'/g, "\\'") + '\')" style="position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #e5e7eb;background:#f8fafc;text-align:center;transition:all 0.15s;" onmouseover="this.style.borderColor='#2563eb';var o=this.querySelector('.tov');if(o)o.style.display='flex'" onmouseout="this.style.borderColor='#e5e7eb';var o=this.querySelector('.tov');if(o)o.style.display='none'">';
                if (t.image) html += '<img src="' + esc(t.image) + '" style="width:100%;height:80px;object-fit:cover;" onerror="this.style.display=\'none\'">';
                else html += '<div style="height:80px;display:flex;align-items:center;justify-content:center;"><i data-lucide="wrench" style="width:24px;height:24px;color:#cbd5e1;"></i></div>';
                html += '<div style="padding:4px 6px;font-size:10px;font-weight:600;color:#374151;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(t.name) + '</div>';
                html += '<div class="tov" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);color:#fff;align-items:center;justify-content:center;padding:8px;font-size:11px;font-weight:700;border-radius:8px;">' + esc(t.name) + '</div>';
                html += '</div>';
            });
            html += '</div></div>';
        } else if (tools.length) {
            html += '<div style="margin-bottom:16px;">';
            html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Required Tools</h3>';
            html += '<div style="display:flex;gap:6px;flex-wrap:wrap;">';
            tools.forEach(function(t) { html += '<span style="padding:3px 10px;background:#eff6ff;border-radius:6px;font-size:11px;color:#2563eb;font-weight:600;">' + esc(typeof t === 'string' ? t : t.name) + '</span>'; });
            html += '</div></div>';
        }
    }

    // Disassembly Guide
    if (art.disassembly_guide) {
        var dSteps = art.disassembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#fee2e2;font-size:12px;">&#128295;</span> Disassembly Guide (' + dSteps.length + ' steps)</h3>';
        html += '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;">';
        dSteps.forEach(function(step, i) {
            var dVideo = ''; var dvm = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (dvm) { dVideo = dvm[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;' + (i < dSteps.length-1 ? 'border-bottom:1px solid #fecaca;' : '') + '">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">' + (i+1) + '</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#991b1b;line-height:1.5;">' + esc(step.replace(/^\d+\.\s*/, '')) + '</div>';
            if (dVideo) html += '<a href="' + esc(dVideo) + '" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#fee2e2;border-radius:6px;font-size:10px;color:#991b1b;font-weight:600;text-decoration:none;"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    // Assembly Guide
    if (art.assembly_guide) {
        var aSteps = art.assembly_guide.split('|').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#166534;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#dcfce7;font-size:12px;">&#9989;</span> Assembly Guide (' + aSteps.length + ' steps)</h3>';
        html += '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;">';
        aSteps.forEach(function(step, i) {
            var aVideo = ''; var avm = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
            if (avm) { aVideo = avm[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
            html += '<div style="padding:8px 0;' + (i < aSteps.length-1 ? 'border-bottom:1px solid #bbf7d0;' : '') + '">';
            html += '<div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">' + (i+1) + '</div>';
            html += '<div style="flex:1;"><div style="font-size:12px;color:#166534;line-height:1.5;">' + esc(step.replace(/^\d+\.\s*/, '')) + '</div>';
            if (aVideo) html += '<a href="' + esc(aVideo) + '" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:#dcfce7;border-radius:6px;font-size:10px;color:#166534;font-weight:600;text-decoration:none;"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
            html += '</div></div></div>';
        });
        html += '</div></div>';
    }

    document.getElementById('eq-viewer-title').textContent = art.manufacturer + ' ' + art.model_name;
    document.getElementById('eq-viewer-content').innerHTML = html;
    document.getElementById('eq-viewer-overlay').style.display = 'block';
    document.getElementById('eq-viewer-panel').style.display = 'block';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeEqViewer() {
    document.getElementById('eq-viewer-overlay').style.display = 'none';
    document.getElementById('eq-viewer-panel').style.display = 'none';
}

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
