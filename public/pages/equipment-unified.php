<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Equipment & Tools Database';
$active_menu = 'equipment';
require APP_ROOT . '/includes/layout_header.php';

$isAdmin = Auth::hasPermission('equipment.edit');
$viewMode = $_GET['view'] ?? 'brands';
$selectedBrand = $_GET['brand'] ?? '';

$equipmentTableExists = Database::fetch("SHOW TABLES LIKE 'equipment'") !== null;

if ($equipmentTableExists) {
    $equipment = Database::fetchAll(
        "SELECT 'equipment' as type, id, manufacturer, model_name, device_type, category, year, cpu, ram, storage,
                display_spec, ports, known_issues, tools_needed, repair_guides, location, status,
                image_url, disassembly_guide, assembly_guide, guide_videos, created_at
         FROM equipment WHERE deleted_at IS NULL"
    );

    $toolRows = Database::fetchAll(
        "SELECT 'tool' as type, id, name as manufacturer, name as model_name,
                'tool' as device_type, NULL as category, NULL as year, NULL as cpu, NULL as ram, NULL as storage,
                NULL as display_spec, NULL as ports, related_issues as known_issues, NULL as tools_needed,
                purpose as repair_guides, NULL as location, 'active' as status, NULL as image_url,
                NULL as disassembly_guide, NULL as assembly_guide, NULL as guide_videos, created_at
         FROM tools ORDER BY manufacturer, model_name"
    );
    $equipment = array_merge($equipment, $toolRows);
    usort($equipment, fn($a, $b) => strcmp(($a['manufacturer'] ?? ''), ($b['manufacturer'] ?? '')) ?: strcmp(($a['model_name'] ?? ''), ($b['model_name'] ?? '')));
} else {
    $equipment = Database::fetchAll(
        "SELECT 'equipment' as type, dm.id,
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
                NULL as guide_videos,
                dm.created_at
         FROM device_models dm
         LEFT JOIN manufacturers m ON m.id = dm.manufacturer_id
         ORDER BY manufacturer, model_name"
    );
    $toolRows = Database::fetchAll("SELECT 'tool' as type, id, name as manufacturer, name as model_name, category, category as device_type, NULL as year, NULL as cpu, NULL as ram, NULL as storage, NULL as display_spec, NULL as ports, related_issues as known_issues, NULL as tools_needed, NULL as repair_guides, NULL as location, 'active' as status, NULL as image_url, NULL as disassembly_guide, NULL as assembly_guide, NULL as guide_videos, created_at FROM tools ORDER BY manufacturer, model_name");
    $equipment = array_merge($equipment, $toolRows);
    usort($equipment, fn($a, $b) => strcmp(($a['manufacturer'] ?? ''), ($b['manufacturer'] ?? '')) ?: strcmp(($a['model_name'] ?? ''), ($b['model_name'] ?? '')));
}

// Count by brand
$brandCounts = [];
$typeStats = ['equipment' => 0, 'tool' => 0];
foreach ($equipment as $e) {
    $b = $e['manufacturer'] ?? 'Unknown';
    $brandCounts[$b] = ($brandCounts[$b] ?? 0) + 1;
    $typeStats[$e['type']]++;
}
arsort($brandCounts);

// Filter by brand if selected
$filtered = $equipment;
if ($selectedBrand) {
    $filtered = array_filter($equipment, function($e) use ($selectedBrand) {
        return $e['manufacturer'] === $selectedBrand;
    });
}

$brandColors = [
    'Lenovo' => ['bg'=>'#fef2f2','fg'=>'#dc2626'], 'Dell' => ['bg'=>'#eff6ff','fg'=>'#2563eb'],
    'HP' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'], 'HPE' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'],
    'Brother' => ['bg'=>'#fefce8','fg'=>'#ca8a04'], 'Cisco' => ['bg'=>'#f0fdf4','fg'=>'#16a34a'],
    'Ubiquiti' => ['bg'=>'#f8fafc','fg'=>'#111827'], 'TP-Link' => ['bg'=>'#eff6ff','fg'=>'#2563eb'],
    'Hikvision' => ['bg'=>'#fef2f2','fg'=>'#dc2626'], 'Dahua' => ['bg'=>'#fef2f2','fg'=>'#dc2626'],
];
$defaultBrandColor = ['bg'=>'#f1f5f9','fg'=>'#475569'];

$typeConfig = [
    'laptop' => ['icon' => 'laptop', 'label' => 'Laptops'],
    'desktop' => ['icon' => 'monitor', 'label' => 'Desktops'],
    'server' => ['icon' => 'server', 'label' => 'Servers'],
    'monitor' => ['icon' => 'monitor', 'label' => 'Monitors'],
    'printer' => ['icon' => 'printer', 'label' => 'Printers'],
    'router' => ['icon' => 'router', 'label' => 'Routers'],
    'switch' => ['icon' => 'network', 'label' => 'Switches'],
    'access point' => ['icon' => 'wifi', 'label' => 'Access Points'],
    'cctv' => ['icon' => 'camera', 'label' => 'CCTV'],
    'tool' => ['icon' => 'wrench', 'label' => 'Tools'],
    'other' => ['icon' => 'hard-drive', 'label' => 'Other'],
];
?>

<style>
    .eq-container {
        max-width: 1320px;
        margin: 0 auto;
        padding: 12px 4px 28px;
    }
    .eq-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        padding: 28px 22px 18px;
        margin: 8px 0 20px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 28px;
        background: linear-gradient(135deg, rgba(255,255,255,0.88), rgba(239,246,255,0.8));
        box-shadow: 0 18px 45px -28px rgba(37,99,235,0.45);
        position: relative;
        overflow: hidden;
    }
    .eq-hero::before {
        content: "";
        position: absolute;
        right: -40px;
        top: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(96,165,250,0.32), rgba(96,165,250,0));
    }
    .eq-hero-copy { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }
    .eq-hero-icon {
        width: 62px; height: 62px; border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff; box-shadow: 0 18px 32px -18px rgba(37,99,235,0.9);
    }
    .eq-hero-icon i { width: 28px; height: 28px; }
    .eq-hero-title {
        font-size: clamp(26px, 3.2vw, 40px);
        font-weight: 800; letter-spacing: -0.06em; color: #0f172a; line-height: 1;
        margin: 0;
    }
    .dark .eq-hero-title { color: #f8fafc; }
    .eq-hero-sub {
        margin-top: 8px; font-size: 14px; color: #64748b;
        max-width: 560px;
    }
    .dark .eq-hero-sub { color: #cbd5e1; }

    .eq-brand-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;
        margin: 22px 0 26px;
    }
    .eq-brand-card {
        display: block; padding: 16px 18px; border-radius: 20px; border: 1px solid rgba(148,163,184,0.2);
        background: rgba(255,255,255,0.82); cursor: pointer; transition: all 0.2s ease; text-decoration: none;
        box-shadow: 0 12px 28px -26px rgba(15,23,42,0.5);
    }
    .eq-brand-card:hover {
        transform: translateY(-4px); box-shadow: 0 18px 34px -24px rgba(37,99,235,0.45);
        border-color: rgba(96,165,250,0.8);
    }
    .dark .eq-brand-card {
        background: rgba(15,23,42,0.78); border-color: rgba(51,65,85,0.9);
    }
    .eq-brand-icon {
        width: 42px; height: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-bottom: 14px; font-weight: 800; color: #fff; font-size: 13px; letter-spacing: 0.08em;
    }
    .eq-brand-name { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .dark .eq-brand-name { color: #f8fafc; }
    .eq-brand-count { font-size: 12px; color: #64748b; }
    .dark .eq-brand-count { color: #94a3b8; }

    .eq-controls {
        display: flex; gap: 14px; flex-wrap: wrap; align-items: center; margin-bottom: 22px;
    }
    .eq-search-wrap {
        position: relative; flex: 1; min-width: 220px;
    }
    .eq-search-wrap input {
        width: 100%; background: rgba(255,255,255,0.8); border: 1px solid rgba(148,163,184,0.24);
        border-radius: 16px; padding: 13px 16px 13px 44px; font-size: 14px; color: #0f172a;
        box-shadow: inset 0 1px 2px rgba(15,23,42,0.03);
    }
    .dark .eq-search-wrap input { background: rgba(15,23,42,0.72); border-color: rgba(51,65,85,0.9); color: #f8fafc; }
    .eq-search-wrap i {
        position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8;
    }

    .eq-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 16px; }
    .eq-item {
        background: rgba(255,255,255,0.82); border: 1px solid rgba(148,163,184,0.18); border-radius: 20px; padding: 16px;
        cursor: pointer; transition: all 0.2s ease; text-decoration: none; display: flex; flex-direction: column;
        box-shadow: 0 14px 30px -28px rgba(15,23,42,0.6);
    }
    .eq-item:hover { transform: translateY(-3px); box-shadow: 0 18px 36px -28px rgba(37,99,235,0.5); border-color: rgba(96,165,250,0.68); }
    .dark .eq-item { background: rgba(15,23,42,0.8); border-color: rgba(51,65,85,0.9); }
    .eq-item-header { display: flex; gap: 12px; margin-bottom: 12px; }
    .eq-item-icon {
        width: 46px; height: 46px; border-radius: 14px; background: linear-gradient(135deg, #eff6ff, #e0e7ff);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #2563eb;
    }
    .eq-item-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
    .dark .eq-item-title { color: #f8fafc; }
    .eq-item-type { font-size: 11px; color: #64748b; }
    .dark .eq-item-type { color: #94a3b8; }
    .eq-item-specs { font-size: 12px; color: #64748b; line-height: 1.6; margin: 10px 0; padding: 10px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
    .dark .eq-item-specs { border-color: rgba(51,65,85,0.9); color: #cbd5e1; }
    .eq-item-footer { display: flex; gap: 8px; margin-top: auto; align-items: center; }
    .eq-item-badge {
        display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; font-size: 10px; font-weight: 700;
    }

    .eq-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.56); z-index: 1000; align-items: center; justify-content: center; }
    .eq-modal.active { display: flex; }
    .eq-modal-content {
        background: rgba(255,255,255,0.98); border-radius: 24px; max-width: 700px; width: 95vw; max-height: 90vh;
        overflow-y: auto; box-shadow: 0 28px 70px -34px rgba(15,23,42,0.7);
    }
    .dark .eq-modal-content { background: rgba(15,23,42,0.96); }
    .eq-modal-header {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px; border-bottom: 1px solid #e2e8f0;
        position: sticky; top: 0; background: rgba(255,255,255,0.98); backdrop-filter: blur(10px);
    }
    .dark .eq-modal-header { background: rgba(15,23,42,0.96); border-bottom-color: rgba(51,65,85,0.9); }
    .eq-modal-body { padding: 24px; }

    .eq-form-group { margin-bottom: 16px; }
    .eq-form-label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 7px; }
    .dark .eq-form-label { color: #dbeafe; }
    .eq-form-input { width: 100%; padding: 11px 12px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; color: #0f172a; background: #fff; }
    .dark .eq-form-input { background: rgba(15,23,42,0.75); border-color: #334155; color: #f8fafc; }
    .eq-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    @media (max-width: 768px) {
        .eq-hero { padding: 22px 18px 16px; }
        .eq-list { grid-template-columns: 1fr; }
        .eq-form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="eq-container">
    <div class="eq-hero fx-reveal">
        <div class="eq-hero-copy">
            <div class="eq-hero-icon"><i data-lucide="package"></i></div>
            <div>
                <h1 class="eq-hero-title">Equipment & Tools</h1>
                <p class="eq-hero-sub">Explore devices, specifications, tools, and repair information.</p>
            </div>
        </div>
        <?php if ($isAdmin): ?>
            <button onclick="openAddModal()" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Add New</button>
        <?php endif; ?>
    </div>

    <!-- Search & Stats -->
    <div class="eq-controls">
        <div class="eq-search-wrap">
            <i data-lucide="search"></i>
            <input type="text" id="eq-search" placeholder="Search equipment, tools, models..." oninput="filterItems()">
        </div>
        <div style="display: flex; gap: 8px;">
            <span style="padding: 10px 14px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 12px; font-size: 12px; font-weight: 700; color: #1d4ed8; display: flex; align-items: center; border: 1px solid rgba(96,165,250,0.35); "><?= count($filtered) ?> items</span>
        </div>
    </div>

    <!-- Brand Selector (Most Viewed) -->
    <div style="margin-bottom: 28px;">
        <h2 style="font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i data-lucide="flame" style="width: 16px; height: 16px; color: #f97316;"></i> Most Popular Brands</h2>
        <div class="eq-brand-grid">
            <?php $count = 0; foreach ($brandCounts as $brand => $cnt):
                if ($count++ >= 8) break;
                $bc = $brandColors[$brand] ?? $defaultBrandColor;
            ?>
                <a href="?brand=<?= urlencode($brand) ?>" class="eq-brand-card" style="<?= $selectedBrand === $brand ? 'border-color: ' . $bc['fg'] . '; background: ' . $bc['bg'] . ';' : '' ?>">
                    <div class="eq-brand-icon" style="background: <?= $bc['fg'] ?>;"><?= strtoupper(substr($brand, 0, 2)) ?></div>
                    <div class="eq-brand-name"><?= e($brand) ?></div>
                    <div class="eq-brand-count"><?= $cnt ?> items</div>
                </a>
            <?php endforeach; ?>
            <?php if ($selectedBrand): ?>
                <a href="?" class="eq-brand-card" style="border-color: #64748b; opacity: 0.6;">
                    <div class="eq-brand-icon" style="background: #64748b;">✕</div>
                    <div class="eq-brand-name">Clear</div>
                    <div class="eq-brand-count">Filter</div>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items List -->
    <?php if (!empty($filtered)): ?>
    <div class="eq-list" id="eq-list">
        <?php foreach ($filtered as $item):
            $tc = $typeConfig[strtolower($item['device_type'])] ?? $typeConfig['other'];
            $specs = [];
            if ($item['type'] === 'equipment') {
                if ($item['cpu']) $specs[] = 'CPU: ' . $item['cpu'];
                if ($item['ram']) $specs[] = 'RAM: ' . $item['ram'];
                if ($item['storage']) $specs[] = 'Storage: ' . $item['storage'];
            }
        ?>
        <a href="javascript:openDetail(<?= $item['id'] ?>, '<?= e($item['type']) ?>')" class="eq-item" data-search="<?= e(strtolower($item['manufacturer'] . ' ' . $item['model_name'] . ' ' . $item['device_type'])) ?>">
            <div class="eq-item-header">
                <div class="eq-item-icon"><i data-lucide="<?= $tc['icon'] ?>"></i></div>
                <div style="flex: 1;">
                    <div class="eq-item-title"><?= e($item['manufacturer']) ?> <?= e($item['model_name']) ?></div>
                    <div class="eq-item-type"><?= e(ucfirst($tc['label'])) ?><?= $item['year'] ? ' · ' . $item['year'] : '' ?></div>
                </div>
            </div>
            <?php if (!empty($specs)): ?>
            <div class="eq-item-specs"><?= implode(' · ', array_map('e', $specs)) ?></div>
            <?php endif; ?>
            <div class="eq-item-footer">
                <span class="eq-item-badge" style="background: <?= $item['type'] === 'tool' ? '#fef3c7' : '#eff6ff' ?>; color: <?= $item['type'] === 'tool' ? '#92400e' : '#2563eb' ?>;">
                    <i data-lucide="<?= $item['type'] === 'tool' ? 'wrench' : 'cpu' ?>" style="width: 10px; height: 10px;"></i> <?= ucfirst($item['type']) ?>
                </span>
                <?php if ($isAdmin): ?>
                <button onclick="editItem(event, <?= $item['id'] ?>, '<?= e($item['type']) ?>')" class="btn btn-secondary btn-sm"><i data-lucide="edit-2" style="width: 12px; height: 12px;"></i></button>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px;">
        <i data-lucide="package" style="width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
        <h3 style="font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 4px;">No items found</h3>
        <p style="font-size: 13px; color: #94a3b8;">Try searching or browse other brands.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="eq-modal" id="eq-modal">
    <div class="eq-modal-content">
        <div class="eq-modal-header">
            <h2 id="modal-title" style="font-size: 16px; font-weight: 800; color: #0f172a;">Add Equipment</h2>
            <button onclick="closeModal()" class="btn btn-ghost btn-sm"><i data-lucide="x"></i></button>
        </div>
        <div class="eq-modal-body">
            <form id="eq-form" onsubmit="submitForm(event)">
                <input type="hidden" id="item-id" value="">
                <input type="hidden" id="item-type" value="equipment">

                <!-- Type Selection -->
                <div class="eq-form-group">
                    <label class="eq-form-label">Type *</label>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-secondary" id="type-equipment" onclick="setType('equipment')">Equipment</button>
                        <button type="button" class="btn btn-secondary" id="type-tool" onclick="setType('tool')">Tool</button>
                    </div>
                </div>

                <!-- Equipment Fields -->
                <div id="equipment-fields" style="display: none;">
                    <div class="eq-form-grid">
                        <div class="eq-form-group">
                            <label class="eq-form-label">Manufacturer *</label>
                            <input type="text" id="manufacturer" class="eq-form-input" required>
                        </div>
                        <div class="eq-form-group">
                            <label class="eq-form-label">Model Name *</label>
                            <input type="text" id="model_name" class="eq-form-input" required>
                        </div>
                    </div>
                    <div class="eq-form-grid">
                        <div class="eq-form-group">
                            <label class="eq-form-label">Device Type *</label>
                            <select id="device_type" class="eq-form-input" required>
                                <option value="">Select type</option>
                                <option value="laptop">Laptop</option>
                                <option value="desktop">Desktop</option>
                                <option value="server">Server</option>
                                <option value="monitor">Monitor</option>
                                <option value="printer">Printer</option>
                                <option value="router">Router</option>
                                <option value="switch">Switch</option>
                                <option value="access point">Access Point</option>
                                <option value="cctv">CCTV</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="eq-form-group">
                            <label class="eq-form-label">Year</label>
                            <input type="number" id="year" class="eq-form-input" min="1990" max="2100">
                        </div>
                    </div>
                    <div class="eq-form-grid">
                        <div><div class="eq-form-group"><label class="eq-form-label">CPU</label><input type="text" id="cpu" class="eq-form-input" placeholder="e.g., Intel i7-10700K"></div></div>
                        <div><div class="eq-form-group"><label class="eq-form-label">RAM</label><input type="text" id="ram" class="eq-form-input" placeholder="e.g., 16GB DDR4"></div></div>
                    </div>
                    <div class="eq-form-group">
                        <label class="eq-form-label">Known Issues</label>
                        <textarea id="known_issues" class="eq-form-input" rows="3" placeholder="Separate multiple issues with commas"></textarea>
                    </div>
                </div>

                <!-- Tool Fields -->
                <div id="tool-fields" style="display: none;">
                    <div class="eq-form-group">
                        <label class="eq-form-label">Tool Name *</label>
                        <input type="text" id="tool_name" class="eq-form-input">
                    </div>
                    <div class="eq-form-group">
                        <label class="eq-form-label">Category *</label>
                        <select id="tool_category" class="eq-form-input">
                            <option value="Power Tools">Power Tools</option>
                            <option value="Hand Tools">Hand Tools</option>
                            <option value="Diagnostic">Diagnostic</option>
                            <option value="Safety">Safety Equipment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="eq-form-group">
                        <label class="eq-form-label">Description</label>
                        <textarea id="tool_description" class="eq-form-input" rows="3"></textarea>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="eq-form-group">
                    <label class="eq-form-label">Image URL</label>
                    <input type="url" id="image_url" class="eq-form-input" placeholder="https://...">
                </div>

                <div style="display: flex; gap: 8px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="eq-modal" id="detail-modal">
    <div class="eq-modal-content">
        <div class="eq-modal-header">
            <h2 id="detail-title" style="font-size: 16px; font-weight: 800;"></h2>
            <button onclick="closeDetailModal()" class="btn btn-ghost btn-sm"><i data-lucide="x"></i></button>
        </div>
        <div class="eq-modal-body" id="detail-body"></div>
    </div>
</div>

<script>
var itemsData = <?= json_encode($filtered) ?>;
var currentType = 'equipment';

function setType(type) {
    currentType = type;
    document.getElementById('item-type').value = type;
    document.getElementById('equipment-fields').style.display = type === 'equipment' ? 'block' : 'none';
    document.getElementById('tool-fields').style.display = type === 'tool' ? 'block' : 'none';
    document.getElementById('type-equipment').classList.toggle('btn-primary', type === 'equipment');
    document.getElementById('type-tool').classList.toggle('btn-primary', type === 'tool');
}

function openAddModal() {
    document.getElementById('item-id').value = '';
    document.getElementById('eq-form').reset();
    setType('equipment');
    document.getElementById('modal-title').textContent = 'Add Equipment';
    document.getElementById('eq-modal').classList.add('active');
}

function closeModal() {
    document.getElementById('eq-modal').classList.remove('active');
}

function filterItems() {
    var search = document.getElementById('eq-search').value.toLowerCase();
    document.querySelectorAll('.eq-list > a').forEach(function(card) {
        card.style.display = !search || card.dataset.search.indexOf(search) !== -1 ? '' : 'none';
    });
}

function submitForm(e) {
    e.preventDefault();
    var formData = new FormData();
    formData.append('type', currentType);
    if (currentType === 'equipment') {
        formData.append('manufacturer', document.getElementById('manufacturer').value);
        formData.append('model_name', document.getElementById('model_name').value);
        formData.append('device_type', document.getElementById('device_type').value);
        formData.append('year', document.getElementById('year').value);
        formData.append('cpu', document.getElementById('cpu').value);
        formData.append('ram', document.getElementById('ram').value);
        formData.append('known_issues', document.getElementById('known_issues').value);
    } else {
        formData.append('name', document.getElementById('tool_name').value);
        formData.append('category', document.getElementById('tool_category').value);
        formData.append('description', document.getElementById('tool_description').value);
        formData.append('purpose', document.getElementById('tool_description').value);
    }
    formData.append('image_url', document.getElementById('image_url').value);

    var itemId = document.getElementById('item-id').value;
    if (itemId) formData.append('id', itemId);

    fetch('<?= $urlBase ?>api/equipment/save.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('Saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(d.error || d.message || 'Error saving', 'error');
        }
    }).catch(() => showToast('Error saving', 'error'));
}

function editItem(e, id, type) {
    e.preventDefault();
    e.stopPropagation();
    // Find item in data
    var item = itemsData.find(it => it.id == id && it.type === type);
    if (!item) return;
    
    document.getElementById('item-id').value = id;
    setType(type);
    document.getElementById('modal-title').textContent = 'Edit ' + (type === 'equipment' ? 'Equipment' : 'Tool');
    
    if (type === 'equipment') {
        document.getElementById('manufacturer').value = item.manufacturer || '';
        document.getElementById('model_name').value = item.model_name || '';
        document.getElementById('device_type').value = item.device_type || '';
        document.getElementById('year').value = item.year || '';
        document.getElementById('cpu').value = item.cpu || '';
        document.getElementById('ram').value = item.ram || '';
        document.getElementById('known_issues').value = item.known_issues || '';
    } else {
        document.getElementById('tool_name').value = item.model_name || '';
        document.getElementById('tool_category').value = item.device_type || '';
        document.getElementById('tool_description').value = item.location || '';
    }
    document.getElementById('image_url').value = item.image_url || '';
    
    document.getElementById('eq-modal').classList.add('active');
}

function openDetail(id, type) {
    var item = itemsData.find(it => it.id == id && it.type === type);
    if (!item) return;
    
    var html = '<h2 style="font-size: 18px; font-weight: 800; margin-bottom: 12px; color: #0f172a;">' + e(item.manufacturer) + ' ' + e(item.model_name) + '</h2>';
    
    if (type === 'equipment' && item.cpu) {
        html += '<div style="padding: 14px; background: #f8fafc; border-radius: 10px; margin-bottom: 16px;">';
        html += '<div><strong>CPU:</strong> ' + e(item.cpu) + '</div>';
        if (item.ram) html += '<div><strong>RAM:</strong> ' + e(item.ram) + '</div>';
        if (item.storage) html += '<div><strong>Storage:</strong> ' + e(item.storage) + '</div>';
        html += '</div>';
    }
    
    if (item.known_issues) {
        html += '<div><strong>Known Issues:</strong></div>';
        item.known_issues.split(',').forEach(issue => {
            html += '<div style="padding: 8px; background: #fef3c7; border-radius: 6px; margin: 4px 0; font-size: 12px;">• ' + e(issue.trim()) + '</div>';
        });
    }
    
    document.getElementById('detail-title').textContent = e(item.manufacturer);
    document.getElementById('detail-body').innerHTML = html;
    document.getElementById('detail-modal').classList.add('active');
}

function closeDetailModal() {
    document.getElementById('detail-modal').classList.remove('active');
}

function e(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
