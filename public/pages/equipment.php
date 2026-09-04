<?php
/**
 * Equipment Database — brand-driven browsing UI
 * Brands, device-type filters and equipment data are 100% database driven.
 */
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

/* --------------------------------------------------------------------
 * Brand management (add / edit) — persists to the `manufacturers` table
 * -------------------------------------------------------------------- */
$canManageBrands = Auth::hasPermission('equipment.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_brand') {
    if (!$canManageBrands) { http_response_code(403); exit('Forbidden'); }

    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Session expired — please go back and try again.');
    }

    $id          = (int)($_POST['brand_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl    = trim($_POST['image_url'] ?? '');

    $typesIn = (array)($_POST['device_types'] ?? []);
    if (!empty($_POST['device_types_custom'])) {
        foreach (explode(',', (string)$_POST['device_types_custom']) as $t) {
            $typesIn[] = $t;
        }
    }
    $deviceTypes = implode(',', array_values(array_filter(array_map('trim', $typesIn), fn($t) => $t !== '')));

    if ($name !== '') {
        $data = [
            'name'         => $name,
            'description'  => $description,
            'image_url'    => $imageUrl,
            'device_types' => $deviceTypes,
        ];
        if ($id > 0) {
            Database::update('manufacturers', $data, 'id = ?', [$id]);
        } else {
            Database::insert('manufacturers', $data);
        }
    }
    redirect(app_base() . 'equipment');
}

$page_title  = 'Equipment Database';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

/* ------------------------------ Data ------------------------------ */
$equipmentTableExists = Database::fetch("SHOW TABLES LIKE 'equipment'") !== null;

if ($equipmentTableExists) {
    $equipment = Database::fetchAll(
        "SELECT id, manufacturer, model_name, device_type, category, year, cpu, ram, storage,
                display_spec, ports, known_issues, tools_needed, repair_guides, location, status,
                image_url, disassembly_guide, assembly_guide, guide_videos
         FROM equipment WHERE deleted_at IS NULL ORDER BY device_type, manufacturer, model_name"
    );
} else {
    $equipment = Database::fetchAll(
        "SELECT dm.id,
                COALESCE(m.name, dm.manufacturer_name) as manufacturer,
                dm.model as model_name,
                dm.device_type,
                NULL as category,
                dm.generation as year,
                NULL as cpu,
                NULL as ram,
                NULL as storage,
                NULL as display_spec,
                NULL as ports,
                dm.known_issues,
                dm.tools_needed,
                NULL as repair_guides,
                NULL as location,
                'active' as status,
                NULL as image_url,
                NULL as disassembly_guide,
                NULL as assembly_guide,
                NULL as guide_videos
         FROM device_models dm
         LEFT JOIN manufacturers m ON m.id = dm.manufacturer_id
         ORDER BY dm.device_type, manufacturer, dm.model"
    );
}
/* ------------------------- Brand + type index ------------------------- */
$brands = Database::fetchAll(
    'SELECT id, name, description, image_url, device_types FROM manufacturers ORDER BY name'
);

// Case-insensitive brand-name -> manufacturer record map
$brandByKey = [];
foreach ($brands as $b) {
    $brandByKey[mb_strtolower(trim($b['name']))] = $b;
}

// Equipment per brand (case-insensitive match on manufacturer name)
$brandRows = []; // key => ['name'=>..,'count'=>n,'types'=>[]]
foreach ($equipment as $eq) {
    $bName = trim($eq['manufacturer'] ?? '') !== '' ? $eq['manufacturer'] : 'Unknown';
    $key   = mb_strtolower($bName);
    if (!isset($brandRows[$key])) {
        $brandRows[$key] = ['name' => $bName, 'count' => 0, 'types' => []];
    }
    $brandRows[$key]['count']++;
    $t = strtolower(trim($eq['device_type'] ?? 'other'));
    if ($t !== '' && !in_array($t, $brandRows[$key]['types'], true)) {
        $brandRows[$key]['types'][] = $t;
    }
}

// Merge DB brand records with equipment-derived brands (DB record wins)
$brandCards = [];
$coveredKeys = [];
foreach ($brands as $b) {
    $key = mb_strtolower(trim($b['name']));
    $coveredKeys[$key] = true;
    $stat = $brandRows[$key] ?? ['name' => $b['name'], 'count' => 0, 'types' => []];
    $dbTypes = array_filter(array_map('trim', explode(',', (string)($b['device_types'] ?? ''))));
    $types = array_values(array_unique(array_merge($stat['types'], array_map('strtolower', $dbTypes))));
    $brandCards[] = [
        'id' => (int)$b['id'], 'name' => $b['name'], 'key' => $key,
        'description' => $b['description'], 'image_url' => $b['image_url'],
        'count' => $stat['count'], 'types' => $types,
    ];
}
foreach ($brandRows as $key => $stat) {
    if (!isset($coveredKeys[$key])) {
        $brandCards[] = [
            'id' => 0, 'name' => $stat['name'], 'key' => $key,
            'description' => null, 'image_url' => null,
            'count' => $stat['count'], 'types' => $stat['types'],
        ];
    }
}
usort($brandCards, fn($a, $b) => $b['count'] <=> $a['count'] ?: strcasecmp($a['name'], $b['name']));

// Device-type chips: values derived from real data; this map only adds icons/labels
$typeLabels = [
    'laptop' => ['icon' => 'laptop', 'label' => 'Laptops'],
    'desktop' => ['icon' => 'monitor', 'label' => 'Desktops'],
    'server' => ['icon' => 'server', 'label' => 'Servers'],
    'monitor' => ['icon' => 'monitor', 'label' => 'Monitors'],
    'printer' => ['icon' => 'printer', 'label' => 'Printers'],
    'router' => ['icon' => 'router', 'label' => 'Routers'],
    'switch' => ['icon' => 'network', 'label' => 'Switches'],
    'access point' => ['icon' => 'wifi', 'label' => 'Access Points'],
    'cctv' => ['icon' => 'camera', 'label' => 'CCTV'],
    'ups' => ['icon' => 'zap', 'label' => 'UPS'],
    'scanner' => ['icon' => 'scan', 'label' => 'Scanners'],
    'tablet' => ['icon' => 'tablet', 'label' => 'Tablets'],
    'workstation' => ['icon' => 'cpu', 'label' => 'Workstations'],
    'nas' => ['icon' => 'hard-drive', 'label' => 'NAS'],
    'other' => ['icon' => 'package', 'label' => 'Other'],
];
$allTypes = [];
foreach ($brandCards as $bc) {
    foreach ($bc['types'] as $t) { $allTypes[$t] = true; }
}
$allTypes = array_keys($allTypes);
sort($allTypes);
?>
<div>
    <!-- Page Hero -->
    <div class="page-hero fx-reveal">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico green"><i data-lucide="package"></i></div>
                <div>
                    <h1 class="page-hero-title">Equipment Database</h1>
                    <p class="page-hero-sub">Pick a brand, filter by device type, dive into specs &amp; repair guides</p>
                </div>
            </div>
            <?php if ($canManageBrands): ?>
            <button class="eq-add-btn" onclick="openBrandModal()">
                <i data-lucide="plus"></i> Add Brand
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 1: Brands -->
    <div class="fx-reveal" style="--fx-delay:50ms;margin-bottom:22px;">
        <div class="eq-step-label"><span class="eq-step-num">1</span> Choose a brand</div>
        <div class="brand-grid">
            <button class="brand-card brand-card--all" data-brand="" onclick="selectBrand(this)">
                <div class="brand-card-logo brand-card-logo--all"><i data-lucide="layout-grid"></i></div>
                <div class="brand-card-body">
                    <div class="brand-card-name">All Brands</div>
                    <div class="brand-card-desc">Show every manufacturer</div>
                </div>
                <span class="brand-card-count"><?= count($equipment) ?></span>
            </button>
            <?php foreach ($brandCards as $bc): ?>
            <button class="brand-card" data-brand="<?= e($bc['key']) ?>" onclick="selectBrand(this)">
                <div class="brand-card-logo">
                    <?php if (!empty($bc['image_url'])): ?>
                        <img src="<?= e($bc['image_url']) ?>" alt="<?= e($bc['name']) ?>"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <span style="display:none;"><?= e(mb_strtoupper(mb_substr($bc['name'], 0, 2))) ?></span>
                    <?php else: ?>
                        <span><?= e(mb_strtoupper(mb_substr($bc['name'], 0, 2))) ?></span>
                    <?php endif; ?>
                </div>
                <div class="brand-card-body">
                    <div class="brand-card-name"><?= e($bc['name']) ?></div>
                    <?php if (!empty($bc['description'])): ?>
                        <div class="brand-card-desc"><?= e(mb_strimwidth($bc['description'], 0, 64, '…')) ?></div>
                    <?php else: ?>
                        <div class="brand-card-desc"><?= $bc['count'] ?> model<?= $bc['count'] === 1 ? '' : 's' ?> in stock</div>
                    <?php endif; ?>
                    <div class="brand-card-types">
                        <?php foreach (array_slice($bc['types'], 0, 3) as $t): ?>
                            <span class="eq-mini-chip"><i data-lucide="<?= $typeLabels[$t]['icon'] ?? 'package' ?>"></i><?= e($typeLabels[$t]['label'] ?? ucfirst($t)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="brand-card-count"><?= $bc['count'] ?></span>
                <?php if ($canManageBrands && $bc['id'] > 0): ?>
                <span class="brand-card-edit" role="button" title="Edit brand"
                      onclick="event.stopPropagation();openBrandModal(<?= $bc['id'] ?>)">
                    <i data-lucide="pencil"></i>
                </span>
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 2: Device type -->
    <div class="fx-reveal" style="--fx-delay:100ms;margin-bottom:22px;">
        <div class="eq-step-label">
            <span class="eq-step-num">2</span> Filter by device type
            <span id="brand-context" class="eq-brand-context"></span>
        </div>
        <div class="eq-chip-row" id="type-chips">
            <button class="eq-chip is-active" data-type="" onclick="selectType(this)">
                <i data-lucide="grid-3x3"></i> All
            </button>
            <?php foreach ($allTypes as $t):
                $tl = $typeLabels[$t] ?? ['icon' => 'package', 'label' => ucfirst($t)];
                $n = 0;
                foreach ($brandRows as $stat) { if (in_array($t, $stat['types'], true)) $n += $stat['count']; }
            ?>
            <button class="eq-chip" data-type="<?= e($t) ?>" onclick="selectType(this)">
                <i data-lucide="<?= $tl['icon'] ?>"></i> <?= e($tl['label']) ?>
                <em><?= $n ?></em>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 3: Equipment -->
    <div class="fx-reveal" style="--fx-delay:150ms;">
        <div class="eq-step-label" style="margin-bottom:10px;">
            <span class="eq-step-num">3</span> Equipment list
            <span id="result-count" class="eq-brand-context"></span>
        </div>
        <div style="margin-bottom:14px;position:relative;max-width:420px;">
            <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;"></i>
            <input type="text" id="eq-search" placeholder="Search model, brand or type…" class="form-input" style="padding-left:36px;" oninput="applyFilters()">
        </div>

        <!-- Brand form modal (add / edit) -->
        <?php if ($canManageBrands): ?>
        <div id="brand-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:9998;" onclick="closeBrandModal()"></div>
        <div id="brand-modal" class="eq-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(520px,94vw);background:#fff;border-radius:16px;z-index:9999;box-shadow:0 24px 64px rgba(15,23,42,.25);overflow:hidden;">
            <form method="post" action="">
                <input type="hidden" name="action" value="save_brand">
                <input type="hidden" name="csrf_token" value="<?= e(Auth::generateCsrfToken()) ?>">
                <input type="hidden" name="brand_id" id="bm-id" value="0">
                <div style="padding:18px 22px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                    <h3 id="bm-title" style="font-size:15px;font-weight:800;color:#111827;">Add Brand</h3>
                    <button type="button" onclick="closeBrandModal()" style="background:none;border:none;cursor:pointer;padding:4px;"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
                </div>
                <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:5px;">Brand name *</label>
                        <input type="text" name="name" id="bm-name" class="form-input" required placeholder="e.g. Lenovo">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:5px;">Description</label>
                        <textarea name="description" id="bm-desc" class="form-input" rows="2" placeholder="Short description shown on the brand card"></textarea>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:5px;">Image / logo URL</label>
                        <input type="text" name="image_url" id="bm-img" class="form-input" placeholder="https://…/logo.png">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:5px;">Device types this brand offers</label>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            <?php foreach ($allTypes as $t):
                                $tl = $typeLabels[$t] ?? ['icon' => 'package', 'label' => ucfirst($t)]; ?>
                            <label class="eq-check-chip">
                                <input type="checkbox" name="device_types[]" value="<?= e($t) ?>">
                                <i data-lucide="<?= $tl['icon'] ?>"></i> <?= e($tl['label']) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="device_types_custom" class="form-input" style="margin-top:8px;font-size:12px;" placeholder="Add others, comma-separated (e.g. projector, pos terminal)">
                    </div>
                </div>
                <div style="padding:14px 22px;background:#f8fafc;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" class="eq-btn-ghost" onclick="closeBrandModal()">Cancel</button>
                    <button type="submit" class="eq-btn-primary"><i data-lucide="save"></i> Save Brand</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Equipment Table -->
        <div class="eq-table-wrap">
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
                        $rowType = strtolower(trim($e['device_type'] ?? 'other'));
                        $rowBrand = mb_strtolower(trim($e['manufacturer'] ?? '') !== '' ? $e['manufacturer'] : 'unknown');
                        $tc = $typeLabels[$rowType] ?? ['icon' => 'package', 'label' => ucfirst($rowType)];
                    ?>
                    <tr class="eq-row"
                        data-type="<?= e($rowType) ?>"
                        data-brand="<?= e($rowBrand) ?>"
                        data-search="<?= e(strtolower($e['manufacturer'] . ' ' . $e['model_name'] . ' ' . $e['device_type'])) ?>"
                        style="border-bottom:1px solid #f1f5f9;cursor:pointer;"
                        onclick="openEqViewer(<?= $e['id'] ?>)">
                        <td style="padding:14px 20px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i data-lucide="<?= $tc['icon'] ?>" style="width:17px;height:17px;color:#2563eb;"></i>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#111827;"><?= e($e['manufacturer']) ?> <?= e($e['model_name']) ?></div>
                                    <?php if ($e['year']): ?><div style="font-size:11px;color:#94a3b8;"><?= e($e['year']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 20px;">
                            <span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;"><?= e(ucfirst($e['device_type'])) ?></span>
                        </td>
                        <td style="padding:14px 20px;" class="hide-mobile">
                            <span style="font-size:12px;color:#64748b;"><?= e($specShort ?: '—') ?></span>
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

            <div id="eq-empty" style="display:none;text-align:center;padding:40px;">
                <i data-lucide="package" style="width:36px;height:36px;color:#cbd5e1;margin-bottom:8px;"></i>
                <p style="color:#94a3b8;font-size:13px;">No equipment matches this filter.</p>
            </div>
        </div>
    </div>
</div>

<!-- Equipment Viewer Modal (preserved from original page) -->
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
var brandNames = <?= json_encode(array_map(fn($b) => ['key' => $b['key'], 'name' => $b['name']], $brandCards)) ?>;
var currentBrand = '';
var currentTypeFilter = '';

function brandDisplayName(key) {
    if (!key) return 'All Brands';
    for (var i = 0; i < brandNames.length; i++) {
        if (brandNames[i].key === key) return brandNames[i].name;
    }
    return key.charAt(0).toUpperCase() + key.slice(1);
}

function selectBrand(btn) {
    currentBrand = btn.dataset.brand || '';
    document.querySelectorAll('.brand-card').forEach(function(c) { c.classList.remove('is-selected'); });
    btn.classList.add('is-selected');
    var ctx = document.getElementById('brand-context');
    ctx.textContent = currentBrand ? '· ' + brandDisplayName(currentBrand) : '';
    applyFilters();
}

function selectType(btn) {
    currentTypeFilter = btn.dataset.type || '';
    document.querySelectorAll('.eq-chip').forEach(function(c) { c.classList.remove('is-active'); });
    btn.classList.add('is-active');
    applyFilters();
}

function applyFilters() {
    var search = (document.getElementById('eq-search').value || '').toLowerCase();
    var visible = 0;
    document.querySelectorAll('.eq-row').forEach(function(row) {
        var matchBrand  = !currentBrand || row.dataset.brand === currentBrand;
        var matchType   = !currentTypeFilter || row.dataset.type === currentTypeFilter;
        var matchSearch = !search || row.dataset.search.indexOf(search) !== -1;
        var show = matchBrand && matchType && matchSearch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var empty = document.getElementById('eq-empty');
    empty.style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('result-count').textContent = '· ' + visible + ' shown';
}

/* ---------------------- Brand management modal ---------------------- */
var brandRecords = <?= json_encode(array_values(array_filter($brandCards, fn($b) => $b['id'] > 0))) ?>;

function openBrandModal(id) {
    id = id || 0;
    var rec = null;
    for (var i = 0; i < brandRecords.length; i++) { if (brandRecords[i].id == id) { rec = brandRecords[i]; break; } }
    document.getElementById('bm-id').value = id;
    document.getElementById('bm-title').textContent = rec ? 'Edit Brand' : 'Add Brand';
    document.getElementById('bm-name').value = rec ? rec.name : '';
    document.getElementById('bm-desc').value = rec && rec.description ? rec.description : '';
    document.getElementById('bm-img').value = rec && rec.image_url ? rec.image_url : '';
    var custom = '';
    if (rec) {
        document.querySelectorAll('#brand-modal input[name="device_types[]"]').forEach(function(cb) {
            cb.checked = rec.types.indexOf(cb.value) !== -1;
        });
        var known = Array.prototype.slice.call(document.querySelectorAll('#brand-modal input[name="device_types[]"]')).map(function(cb){return cb.value;});
        custom = rec.types.filter(function(t){ return known.indexOf(t) === -1; }).join(', ');
    } else {
        document.querySelectorAll('#brand-modal input[name="device_types[]"]').forEach(function(cb) { cb.checked = false; });
    }
    document.querySelector('#brand-modal input[name="device_types_custom"]').value = custom;
    document.getElementById('brand-modal-overlay').style.display = 'block';
    document.getElementById('brand-modal').style.display = 'block';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeBrandModal() {
    document.getElementById('brand-modal-overlay').style.display = 'none';
    document.getElementById('brand-modal').style.display = 'none';
}
</script>

<script>
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
                html += '<div onclick="showToolPopup(\'' + esc(t.name).replace(/'/g, "\\'") + '\',\'' + esc(t.desc).replace(/'/g, "\\'") + '\',\'' + esc(t.howto).replace(/'/g, "\\'") + '\',\'' + esc(t.image).replace(/'/g, "\\'") + '\')" style="position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #e5e7eb;background:#f8fafc;text-align:center;transition:all 0.15s;" onmouseover="this.style.borderColor=\'#2563eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'flex\'" onmouseout="this.style.borderColor=\'#e5e7eb\';var o=this.querySelector(\'.tov\');if(o)o.style.display=\'none\'">';
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
%%CHUNK2%%