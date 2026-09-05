<?php
/**
 * Equipment Database — Level 1: Brand Selection
 * Clean 3-level navigation: Equipment (brands) → Brand (devices) → Device (details)
 */
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$canManageBrands = Auth::hasPermission('equipment.manage');

/* Handle POST: save brand */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_brand') {
    if (!$canManageBrands) { http_response_code(403); exit('Forbidden'); }
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) { http_response_code(419); exit('Session expired'); }
    $id = (int)($_POST['brand_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $typesIn = (array)($_POST['device_types'] ?? []);
    if (!empty($_POST['device_types_custom'])) {
        foreach (explode(',', (string)$_POST['device_types_custom']) as $t) { $typesIn[] = $t; }
    }
    $deviceTypes = implode(',', array_values(array_filter(array_map('trim', $typesIn), fn($t) => $t !== '')));
    if ($name !== '') {
        $data = ['name' => $name, 'description' => $description, 'image_url' => $imageUrl, 'device_types' => $deviceTypes];
        if ($id > 0) { Database::update('manufacturers', $data, 'id = ?', [$id]); }
        else { Database::insert('manufacturers', $data); }
    }
    redirect(app_base() . 'equipment');
}

$page_title = 'Equipment Database';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

/* Fetch equipment counts per brand */
$equipment = Database::fetchAll(
    "SELECT id, manufacturer, model_name, device_type, image_url
     FROM equipment WHERE deleted_at IS NULL ORDER BY manufacturer, model_name"
);

$brandRows = [];
foreach ($equipment as $eq) {
    $bName = trim($eq['manufacturer'] ?? '') !== '' ? $eq['manufacturer'] : 'Unknown';
    $key = mb_strtolower($bName);
    if (!isset($brandRows[$key])) {
        $brandRows[$key] = ['name' => $bName, 'count' => 0, 'types' => [], 'image' => $eq['image_url'] ?? null];
    }
    $brandRows[$key]['count']++;
    $t = strtolower(trim($eq['device_type'] ?? 'other'));
    if ($t !== '' && !in_array($t, $brandRows[$key]['types'], true)) {
        $brandRows[$key]['types'][] = $t;
    }
    if (!$brandRows[$key]['image'] && !empty($eq['image_url'])) {
        $brandRows[$key]['image'] = $eq['image_url'];
    }
}

$mfgRows = Database::fetchAll('SELECT id, name, image_url, description, device_types FROM manufacturers ORDER BY name');
$mfgMap = [];
foreach ($mfgRows as $m) { $mfgMap[mb_strtolower($m['name'])] = $m; }

$brandCards = [];
foreach ($brandRows as $key => $stat) {
    $mfg = $mfgMap[$key] ?? null;
    $brandCards[] = [
        'id' => $mfg ? (int)$mfg['id'] : 0,
        'name' => $stat['name'], 'key' => $key,
        'description' => $mfg['description'] ?? ($stat['count'] . ' device model' . ($stat['count'] === 1 ? '' : 's')),
        'image_url' => $mfg['image_url'] ?? $stat['image'],
        'count' => $stat['count'], 'types' => $stat['types'],
    ];
}
foreach ($mfgRows as $m) {
    $key = mb_strtolower($m['name']);
    if (!isset($brandRows[$key])) {
        $dbTypes = array_filter(array_map('trim', explode(',', (string)($m['device_types'] ?? ''))));
        $brandCards[] = [
            'id' => (int)$m['id'], 'name' => $m['name'], 'key' => $key,
            'description' => $m['description'] ?? 'No devices registered',
            'image_url' => $m['image_url'], 'count' => 0, 'types' => array_map('strtolower', $dbTypes),
        ];
    }
}
usort($brandCards, fn($a, $b) => $b['count'] <=> $a['count'] ?: strcasecmp($a['name'], $b['name']));
$totalModels = count($equipment);

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
    'nvr' => ['icon' => 'hard-drive', 'label' => 'NVR'],
    'other' => ['icon' => 'package', 'label' => 'Other'],
];
?>
<div>
    <!-- Page Hero -->
    <div class="page-hero fx-reveal">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico green"><i data-lucide="package"></i></div>
                <div>
                    <h1 class="page-hero-title">Equipment Database</h1>
                    <p class="page-hero-sub">Select a brand to browse device models, specs, and repair guides</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="padding:6px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;font-size:12px;color:#166534;font-weight:600;">
                    <?= $totalModels ?> total models
                </div>
                <?php if ($canManageBrands): ?>
                <button class="eq-add-btn" onclick="openBrandModal()">
                    <i data-lucide="plus"></i> Add Brand
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Brand Search -->
    <div class="fx-reveal" style="--fx-delay:30ms;margin-bottom:24px;position:relative;max-width:480px;">
        <i data-lucide="search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#94a3b8;"></i>
        <input type="text" id="brand-search" placeholder="Search brands..." class="form-input" style="padding-left:40px;font-size:14px;" oninput="filterBrands()">
    </div>

    <!-- Brand Grid -->
    <div class="fx-reveal" style="--fx-delay:50ms;">
        <div id="brand-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
            <?php foreach ($brandCards as $bc):
                $initials = mb_strtoupper(mb_substr($bc['name'], 0, 2));
                $typeBadges = [];
                foreach (array_slice($bc['types'], 0, 4) as $t) {
                    $tl = $typeLabels[$t] ?? ['icon' => 'package', 'label' => ucfirst($t)];
                    $typeBadges[] = $tl;
                }
            ?>
            <a href="<?= app_base() ?>brand?name=<?= urlencode($bc['name']) ?>"
               class="eq-brand-link"
               data-search="<?= e(strtolower($bc['name'] . ' ' . ($bc['description'] ?? '') . ' ' . implode(' ', $bc['types']))) ?>"
               style="display:block;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;text-decoration:none;transition:all 0.2s;cursor:pointer;"
               onmouseover="this.style.borderColor='#2563eb';this.style.boxShadow='0 8px 24px rgba(37,99,235,0.12)';this.style.transform='translateY(-2px)'"
               onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none';this.style.transform='none'">
                <div style="height:140px;background:linear-gradient(135deg,#f8fafc 0%,#eff6ff 100%);display:flex;align-items:center;justify-content:center;border-bottom:1px solid #f1f5f9;position:relative;overflow:hidden;">
                    <?php if (!empty($bc['image_url'])): ?>
                        <img src="<?= e($bc['image_url']) ?>" alt="<?= e($bc['name']) ?>"
                             style="max-height:60px;max-width:140px;object-fit:contain;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div style="display:none;align-items:center;justify-content:center;width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:28px;font-weight:800;letter-spacing:1px;"><?= $initials ?></div>
                    <?php else: ?>
                        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;font-weight:800;letter-spacing:1px;box-shadow:0 4px 12px rgba(37,99,235,0.25);"><?= $initials ?></div>
                    <?php endif; ?>
                    <div style="position:absolute;top:12px;right:12px;padding:4px 10px;background:#fff;border:1px solid #e5e7eb;border-radius:20px;font-size:11px;font-weight:700;color:#475569;box-shadow:0 2px 4px rgba(0,0,0,0.06);">
                        <?= $bc['count'] ?> model<?= $bc['count'] !== 1 ? 's' : '' ?>
                    </div>
                </div>
                <div style="padding:16px 18px;">
                    <h3 style="font-size:16px;font-weight:800;color:#111827;margin-bottom:4px;"><?= e($bc['name']) ?></h3>
                    <p style="font-size:12px;color:#64748b;line-height:1.4;margin-bottom:10px;"><?= e(mb_strimwidth($bc['description'] ?? '', 0, 80, '…')) ?></p>
                    <?php if (!empty($typeBadges)): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                        <?php foreach ($typeBadges as $tl): ?>
                        <span style="display:inline-flex;align-items:center;gap:3px;padding:3px 8px;background:#f1f5f9;border-radius:6px;font-size:10px;color:#475569;font-weight:600;">
                            <i data-lucide="<?= $tl['icon'] ?>" style="width:11px;height:11px;"></i>
                            <?= e($tl['label']) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="padding:0 18px 14px;display:flex;align-items:center;gap:6px;font-size:12px;color:#2563eb;font-weight:600;">
                    Browse devices <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div id="brand-empty" style="display:none;text-align:center;padding:60px 20px;">
            <i data-lucide="search" style="width:40px;height:40px;color:#cbd5e1;margin-bottom:12px;"></i>
            <p style="color:#94a3b8;font-size:14px;font-weight:600;">No brands match your search</p>
        </div>
    </div>
</div>

<?php if ($canManageBrands): ?>
<div id="brand-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);z-index:10000;" onclick="closeBrandModal()"></div>
<div id="brand-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(520px,94vw);background:#fff;border-radius:16px;z-index:10001;box-shadow:0 25px 60px rgba(0,0,0,0.3),0 0 0 1px rgba(0,0,0,0.05);overflow:hidden;">
    <form method="post" action="">
        <input type="hidden" name="action" value="save_brand">
        <input type="hidden" name="csrf_token" value="<?= e(Auth::generateCsrfToken()) ?>">
        <input type="hidden" name="brand_id" id="bm-id" value="0">
        <div style="padding:20px 24px;background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;"><i data-lucide="building-2" style="width:18px;height:18px;color:#fff;"></i></div>
                <h3 id="bm-title" style="font-size:16px;font-weight:800;color:#fff;margin:0;">Add Brand</h3>
            </div>
            <button type="button" onclick="closeBrandModal()" style="background:rgba(255,255,255,0.2);border:none;cursor:pointer;padding:6px;border-radius:8px;transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'"><i data-lucide="x" style="width:16px;height:16px;color:#fff;"></i></button>
        </div>
        <div style="padding:22px 24px;display:flex;flex-direction:column;gap:16px;max-height:55vh;overflow-y:auto;">
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">Brand name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="bm-name" required placeholder="e.g. Lenovo" style="width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;color:#111827;background:#f9fafb;transition:border-color 0.15s,box-shadow 0.15s;box-sizing:border-box;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';this.style.background='#fff'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none';this.style.background='#f9fafb'">
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">Description</label>
                <textarea name="description" id="bm-desc" rows="2" placeholder="Short description shown on the brand card" style="width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;color:#111827;background:#f9fafb;transition:border-color 0.15s,box-shadow 0.15s;box-sizing:border-box;resize:vertical;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';this.style.background='#fff'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none';this.style.background='#f9fafb'"></textarea>
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">Logo URL</label>
                <div style="position:relative;">
                    <i data-lucide="image" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8;pointer-events:none;"></i>
                    <input type="text" name="image_url" id="bm-img" placeholder="https://.../logo.png" style="width:100%;padding:10px 14px 10px 36px;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;color:#111827;background:#f9fafb;transition:border-color 0.15s,box-shadow 0.15s;box-sizing:border-box;" onfocus="this.style.borderColor='#2563eb';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';this.style.background='#fff'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none';this.style.background='#f9fafb'">
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:8px;">Device types this brand offers</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach (['laptop','desktop','server','monitor','printer','router','switch','access point','cctv','nvr'] as $t):
                        $tl = $typeLabels[$t] ?? ['icon' => 'package', 'label' => ucfirst($t)]; ?>
                    <label style="display:inline-flex;align-items:center;gap:5px;padding:7px 12px;background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:8px;font-size:11px;color:#475569;font-weight:600;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.borderColor='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#f8fafc'">
                        <input type="checkbox" name="device_types[]" value="<?= e($t) ?>" style="accent-color:#2563eb;width:14px;height:14px;">
                        <i data-lucide="<?= $tl['icon'] ?>" style="width:13px;height:13px;"></i> <?= e($tl['label']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="device_types_custom" style="margin-top:8px;width:100%;padding:8px 14px;border:1.5px dashed #cbd5e1;border-radius:8px;font-size:12px;color:#64748b;background:#fafbfc;box-sizing:border-box;" placeholder="Add others, comma-separated (e.g. projector)">
            </div>
        </div>
        <div style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" onclick="closeBrandModal()" style="padding:10px 20px;background:#fff;border:1.5px solid #d1d5db;border-radius:10px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.borderColor='#9ca3af';this.style.background='#f9fafb'" onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fff'">Cancel</button>
            <button type="submit" style="padding:10px 20px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;border-radius:10px;font-size:13px;font-weight:700;color:#fff;cursor:pointer;display:flex;align-items:center;gap:7px;box-shadow:0 2px 8px rgba(37,99,235,0.3);transition:all 0.15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(37,99,235,0.4)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(37,99,235,0.3)';this.style.transform='none'"><i data-lucide="save" style="width:14px;height:14px;"></i> Save Brand</button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
function filterBrands() {
    var search = (document.getElementById('brand-search').value || '').toLowerCase();
    var cards = document.querySelectorAll('.eq-brand-link');
    var visible = 0;
    cards.forEach(function(c) {
        var match = !search || c.dataset.search.indexOf(search) !== -1;
        c.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('brand-empty').style.display = visible === 0 ? 'block' : 'none';
}

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
    document.querySelectorAll('#brand-modal input[name="device_types[]"]').forEach(function(cb) {
        cb.checked = rec && rec.types && rec.types.indexOf(cb.value) !== -1;
    });
    document.querySelector('#brand-modal input[name="device_types_custom"]').value = '';
    document.getElementById('brand-modal-overlay').style.display = 'block';
    document.getElementById('brand-modal').style.display = 'block';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeBrandModal() {
    document.getElementById('brand-modal-overlay').style.display = 'none';
    document.getElementById('brand-modal').style.display = 'none';
}

// Move modal outside page-content (which has transform that breaks position:fixed)
(function() {
    var ov = document.getElementById('brand-modal-overlay');
    var md = document.getElementById('brand-modal');
    if (ov) document.body.appendChild(ov);
    if (md) document.body.appendChild(md);
})();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
