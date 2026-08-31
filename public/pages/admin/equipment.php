<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Equipment Management';
$active_menu = 'admin-equipment';
$required_permission = 'equipment.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';

$equipmentTableExists = Database::fetch("SHOW TABLES LIKE 'equipment'") !== null;

if ($equipmentTableExists) {
    $equipment = Database::fetchAll(
        "SELECT e.*, u.full_name as author_name
         FROM equipment e
         LEFT JOIN users u ON e.created_by = u.id
         WHERE e.deleted_at IS NULL
         ORDER BY e.device_type, e.manufacturer, e.model_name"
    );
} else {
    $equipment = Database::fetchAll(
        "SELECT dm.id,
                COALESCE(m.name, dm.manufacturer_name) as manufacturer,
                dm.model as model_name,
                dm.device_type,
                dm.generation as year,
                dm.known_issues,
                dm.tools_needed,
                NULL as location,
                'active' as status,
                u.full_name as author_name,
                dm.created_at
         FROM device_models dm
         LEFT JOIN manufacturers m ON m.id = dm.manufacturer_id
         LEFT JOIN users u ON u.id = 1
         ORDER BY dm.device_type, manufacturer, dm.model"
    );
}

$total = count($equipment);
$counts = ['laptop'=>0,'desktop'=>0,'server'=>0,'printer'=>0,'switch'=>0,'cctv'=>0];
foreach ($equipment as $e) {
    $t = strtolower($e['device_type'] ?? '');
    if (isset($counts[$t])) $counts[$t]++;
}
?>
<div>
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico green"><i data-lucide="package"></i></div>
                <div>
                    <h1 class="page-hero-title">Equipment Management</h1>
                    <p class="page-hero-sub">Manage device types, manufacturers, models, and repair guides</p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openEqEditor(null)" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> Add Equipment</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:24px;">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#111827;"><?= $total ?></div>
            <div style="font-size:11px;color:#64748b;">Total Models</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#2563eb;"><?= $counts['laptop'] ?></div>
            <div style="font-size:11px;color:#64748b;">Laptops</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#16a34a;"><?= $counts['desktop'] ?></div>
            <div style="font-size:11px;color:#64748b;">Desktops</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#9333ea;"><?= $counts['server'] ?></div>
            <div style="font-size:11px;color:#64748b;">Servers</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#ea580c;"><?= $counts['printer'] ?></div>
            <div style="font-size:11px;color:#64748b;">Printers</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:#475569;"><?= $counts['switch'] + $counts['cctv'] ?></div>
            <div style="font-size:11px;color:#64748b;">Network/CCTV</div>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Equipment</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Type</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;" class="hide-mobile">Location</th>
                    <th style="text-align:right;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment as $e): ?>
                <tr id="eq-row-<?= $e['id'] ?>" style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:14px 20px;">
                        <div style="font-size:13px;font-weight:600;color:#111827;"><?= e($e['manufacturer']) ?> <?= e($e['model_name']) ?></div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?= e($e['year'] ?? '') ?></div>
                    </td>
                    <td style="padding:14px 20px;">
                        <span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;"><?= e(ucfirst($e['device_type'])) ?></span>
                    </td>
                    <td style="padding:14px 20px;" class="hide-mobile">
                        <span style="font-size:12px;color:#64748b;"><?= e($e['location'] ?? '—') ?></span>
                    </td>
                    <td style="padding:14px 20px;text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                            <button onclick="openEqViewer(<?= $e['id'] ?>)" title="View" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#16a34a';this.style.background='#f0fdf4'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="eye" style="width:14px;height:14px;"></i></button>
                            <button onclick="openEqEditor(<?= $e['id'] ?>)" title="Edit" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="pencil" style="width:14px;height:14px;"></i></button>
                            <button onclick="eqDelete(<?= $e['id'] ?>)" title="Delete" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Viewer Modal -->
<div id="eq-viewer-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeEqViewer()"></div>
<div id="eq-viewer-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 style="font-size:16px;font-weight:800;color:#111827;">Equipment Details</h2>
        <div style="display:flex;gap:6px;">
            <button onclick="closeEqViewer();openEqEditor(currentEqViewId)" style="padding:5px 10px;background:#eff6ff;color:#2563eb;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit</button>
            <button onclick="closeEqViewer()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
        </div>
    </div>
    <div id="eq-viewer-content" style="padding:24px;"></div>
</div>

<!-- Editor Modal -->
<div id="eq-editor-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeEqEditor()"></div>
<div id="eq-editor-panel" style="display:none;position:fixed;top:0;right:0;width:min(680px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="eq-editor-title" style="font-size:16px;font-weight:800;color:#111827;">Add Equipment</h2>
        <button onclick="closeEqEditor()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
    </div>
    <form id="eq-editor-form" onsubmit="return saveEq(event)" style="padding:20px 24px 40px;">
        <input type="hidden" id="eq-edit-id" value="">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Manufacturer *</label>
                <input type="text" id="eq-edit-mfg" required placeholder="e.g., HP, Dell, Lenovo" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Model Name *</label>
                <input type="text" id="eq-edit-model" required placeholder="e.g., ThinkPad T14 Gen 3" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Device Type *</label>
                <select id="eq-edit-type" required style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box;">
                    <option value="Laptop">Laptop</option><option value="Desktop">Desktop</option><option value="Server">Server</option>
                    <option value="Printer">Printer</option><option value="Switch">Switch</option><option value="Router">Router</option>
                    <option value="Monitor">Monitor</option><option value="CCTV">CCTV Camera</option><option value="Access Point">Access Point</option>
                    <option value="NVR">NVR/DVR</option><option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Year</label>
                <input type="text" id="eq-edit-year" placeholder="e.g., 2023" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">CPU</label>
                <input type="text" id="eq-edit-cpu" placeholder="e.g., Intel i5-12400" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">RAM</label>
                <input type="text" id="eq-edit-ram" placeholder="e.g., 16GB DDR4" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Storage</label>
                <input type="text" id="eq-edit-storage" placeholder="e.g., 512GB NVMe" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Display</label>
            <input type="text" id="eq-edit-display" placeholder='e.g., 14" FHD IPS' style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Ports</label>
            <input type="text" id="eq-edit-ports" placeholder="e.g., 2x USB-A, 1x USB-C, HDMI, RJ-45" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Known Issues (comma-separated)</label>
            <textarea id="eq-edit-issues" rows="2" placeholder="e.g., Battery swelling, WiFi disconnects" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
        </div>

        <!-- DISASSEMBLY STEPS BUILDER -->
        <div style="margin-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <label style="font-size:12px;font-weight:700;color:#991b1b;display:flex;align-items:center;gap:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#fee2e2;font-size:11px;">&#128295;</span> Disassembly Guide</label>
                <button type="button" onclick="addDisStep()" style="padding:3px 10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">+ Add Step</button>
            </div>
            <div id="dis-steps-container"></div>
        </div>

        <!-- ASSEMBLY STEPS BUILDER -->
        <div style="margin-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <label style="font-size:12px;font-weight:700;color:#166534;display:flex;align-items:center;gap:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#dcfce7;font-size:11px;">&#9989;</span> Assembly Guide</label>
                <button type="button" onclick="addAsmStep()" style="padding:3px 10px;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">+ Add Step</button>
            </div>
            <div id="asm-steps-container"></div>
        </div>

        <!-- TOOLS BUILDER -->
        <div style="margin-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <label style="font-size:12px;font-weight:700;color:#2563eb;display:flex;align-items:center;gap:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#eff6ff;font-size:11px;">&#128295;</span> Required Tools</label>
                <button type="button" onclick="addTool()" style="padding:3px 10px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">+ Add Tool</button>
            </div>
            <div id="tools-container"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Location</label>
                <input type="text" id="eq-edit-location" placeholder="e.g., Room 201" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Asset Tag</label>
                <input type="text" id="eq-edit-asset" placeholder="e.g., IT-001234" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box;">
            </div>
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Product Image</label>
            <div style="display:flex;gap:10px;align-items:center;">
                <div id="eq-edit-img-preview" style="width:80px;height:80px;border-radius:10px;border:2px dashed #d1d5db;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;flex-shrink:0;cursor:pointer;" onclick="document.getElementById('eq-edit-img-file').click()">
                    <i data-lucide="camera" style="width:20px;height:20px;color:#94a3b8;"></i>
                </div>
                <div style="flex:1;">
                    <input type="file" id="eq-edit-img-file" accept="image/*" style="display:none;" onchange="uploadProductImage(this)">
                    <input type="text" id="eq-edit-image" placeholder="Or paste image URL here" style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;box-sizing:border-box;">
                    <div style="font-size:10px;color:#94a3b8;margin-top:3px;">Click the image area to upload, or paste a URL above</div>
                </div>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Notes</label>
            <textarea id="eq-edit-notes" rows="2" placeholder="Additional notes..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid #f1f5f9;">
            <button type="button" onclick="closeEqEditor()" style="padding:8px 16px;background:#f1f5f9;color:#475569;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
            <button type="submit" id="eq-editor-save-btn" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Save Equipment</button>
        </div>
    </form>
</div>

<script>
var eqData = <?= json_encode($equipment) ?>;
var currentEqViewId = null;
var disStepCounter = 0;
var asmStepCounter = 0;
var toolCounter = 0;

/* ---- Dynamic Step Builders ---- */
function addDisStep(title, desc, video) {
    title = title || ''; desc = desc || ''; video = video || '';
    var id = disStepCounter++;
    var html = '<div id="dis-'+id+'" style="display:flex;gap:8px;margin-bottom:8px;padding:10px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;align-items:flex-start;">';
    html += '<div style="width:24px;height:24px;border-radius:50%;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;margin-top:6px;" id="dis-num-'+id+'">'+(document.querySelectorAll('#dis-steps-container > div').length+1)+'</div>';
    html += '<div style="flex:1;display:flex;flex-direction:column;gap:6px;">';
    html += '<input type="text" placeholder="Step title (e.g., Remove bottom screws)" value="'+escH(title)+'" data-field="title" style="width:100%;padding:5px 8px;border:1px solid #fecaca;border-radius:6px;font-size:12px;font-weight:600;box-sizing:border-box;">';
    html += '<textarea placeholder="Step description (what to do, tips, warnings)" data-field="desc" rows="2" style="width:100%;padding:5px 8px;border:1px solid #fecaca;border-radius:6px;font-size:11px;resize:vertical;box-sizing:border-box;">'+escH(desc)+'</textarea>';
    html += '<input type="text" placeholder="Video URL (optional - e.g., https://youtube.com/watch?v=...)" value="'+escH(video)+'" data-field="video" style="width:100%;padding:5px 8px;border:1px dashed #fecaca;border-radius:6px;font-size:11px;box-sizing:border-box;">';
    html += '</div>';
    html += '<button type="button" onclick="removeDisStep('+id+')" style="padding:4px;background:none;border:none;cursor:pointer;color:#dc2626;flex-shrink:0;" title="Remove step"><i data-lucide="x" style="width:14px;height:14px;"></i></button>';
    html += '</div>';
    document.getElementById('dis-steps-container').insertAdjacentHTML('beforeend', html);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function removeDisStep(id) { var el=document.getElementById('dis-'+id);if(el)el.remove(); renumberSteps('dis-steps-container','dis-num-'); }
function addAsmStep(title, desc, video) {
    title = title || ''; desc = desc || ''; video = video || '';
    var id = asmStepCounter++;
    var html = '<div id="asm-'+id+'" style="display:flex;gap:8px;margin-bottom:8px;padding:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">';
    html += '<div style="width:24px;height:24px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;margin-top:6px;" id="asm-num-'+id+'">'+(document.querySelectorAll('#asm-steps-container > div').length+1)+'</div>';
    html += '<div style="flex:1;display:flex;flex-direction:column;gap:6px;">';
    html += '<input type="text" placeholder="Step title (e.g., Reconnect battery cable)" value="'+escH(title)+'" data-field="title" style="width:100%;padding:5px 8px;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;font-weight:600;box-sizing:border-box;">';
    html += '<textarea placeholder="Step description" data-field="desc" rows="2" style="width:100%;padding:5px 8px;border:1px solid #bbf7d0;border-radius:6px;font-size:11px;resize:vertical;box-sizing:border-box;">'+escH(desc)+'</textarea>';
    html += '<input type="text" placeholder="Video URL (optional)" value="'+escH(video)+'" data-field="video" style="width:100%;padding:5px 8px;border:1px dashed #bbf7d0;border-radius:6px;font-size:11px;box-sizing:border-box;">';
    html += '</div>';
    html += '<button type="button" onclick="removeAsmStep('+id+')" style="padding:4px;background:none;border:none;cursor:pointer;color:#16a34a;flex-shrink:0;" title="Remove step"><i data-lucide="x" style="width:14px;height:14px;"></i></button>';
    html += '</div>';
    document.getElementById('asm-steps-container').insertAdjacentHTML('beforeend', html);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function removeAsmStep(id) { var el=document.getElementById('asm-'+id);if(el)el.remove(); renumberSteps('asm-steps-container','asm-num-'); }
function renumberSteps(containerId, prefix) {
    var steps = document.getElementById(containerId).children;
    for (var i = 0; i < steps.length; i++) { var num = steps[i].querySelector('[id^="'+prefix+'"]'); if(num) num.textContent = i+1; }
}

/* ---- Dynamic Tool Builder ---- */
function addTool(name, desc, howto, image) {
    name = name || ''; desc = desc || ''; howto = howto || ''; image = image || '';
    var id = toolCounter++;
    var html = '<div id="tool-'+id+'" style="margin-bottom:10px;padding:12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">';
    html += '<div style="display:flex;gap:10px;">';
    // Image upload area
    html += '<div style="width:64px;height:64px;border-radius:8px;background:#fff;border:2px dashed #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;cursor:pointer;position:relative;" onclick="document.getElementById(\'tool-file-'+id+'\').click()" onmouseover="this.style.borderColor=\'#2563eb\'" onmouseout="this.style.borderColor=\'#d1d5db\'">';
    if (image) html += '<img id="tool-img-'+id+'" src="'+escH(image)+'" style="width:100%;height:100%;object-fit:cover;">';
    else html += '<div id="tool-img-'+id+'" style="display:flex;flex-direction:column;align-items:center;justify-content:center;"><i data-lucide="camera" style="width:18px;height:18px;color:#94a3b8;"></i><span style="font-size:8px;color:#94a3b8;margin-top:2px;">Upload</span></div>';
    html += '<input type="file" id="tool-file-'+id+'" accept="image/*" style="display:none;" onchange="uploadToolImage(this,'+id+')">';
    html += '<input type="hidden" data-field="image" value="'+escH(image)+'">';
    html += '</div>';
    html += '<div style="flex:1;display:flex;flex-direction:column;gap:6px;">';
    html += '<input type="text" placeholder="Tool name (e.g., Phillips PH0 Screwdriver)" value="'+escH(name)+'" data-field="name" style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;box-sizing:border-box;">';
    html += '<input type="text" placeholder="Description (e.g., Small cross-head screwdriver for electronics)" value="'+escH(desc)+'" data-field="desc" style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;box-sizing:border-box;">';
    html += '<input type="text" placeholder="How to use (e.g., Use to remove M.2 SSD screw)" value="'+escH(howto)+'" data-field="howto" style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;box-sizing:border-box;">';
    html += '';
    html += '</div>';
    html += '<button type="button" onclick="removeTool('+id+')" style="padding:4px;background:none;border:none;cursor:pointer;color:#dc2626;flex-shrink:0;" title="Remove tool"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
    html += '</div></div>';
    document.getElementById('tools-container').insertAdjacentHTML('beforeend', html);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function removeTool(id) { var el=document.getElementById('tool-'+id);if(el)el.remove(); }
function uploadProductImage(input) {
    var file = input.files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('file', file);
    fd.append('type', 'product');
    var preview = document.getElementById('eq-edit-img-preview');
    preview.innerHTML = '<i data-lucide="loader" style="width:18px;height:18px;color:#2563eb;animation:spin 1s linear infinite;"></i>'; lucide.createIcons();
    fetch('../../api/upload.php', {method:'POST', body:fd}).then(function(r){return r.json();}).then(function(d){
        if (d.url) {
            document.getElementById('eq-edit-image').value = d.url;
            preview.innerHTML = '<img src="'+d.url+'" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
        } else { Swal.fire('Upload Failed', d.error || 'Could not upload image', 'error'); preview.innerHTML = '<i data-lucide="camera" style="width:20px;height:20px;color:#94a3b8;"></i>'; lucide.createIcons(); }
    }).catch(function(){ Swal.fire('Upload Failed', 'Network error', 'error'); preview.innerHTML = '<i data-lucide="camera" style="width:20px;height:20px;color:#94a3b8;"></i>'; lucide.createIcons(); });
}
function uploadToolImage(input, toolId) {
    var file = input.files[0];
    if (!file) return;
    var fd = new FormData();
    fd.append('file', file);
    fd.append('type', 'tool');
    var img = document.getElementById('tool-img-' + toolId);
    if (img && img.tagName !== 'IMG') { img.innerHTML = '<i data-lucide="loader" style="width:18px;height:18px;color:#2563eb;animation:spin 1s linear infinite;"></i>'; lucide.createIcons(); }
    fetch('../../api/upload.php', {method:'POST', body:fd}).then(function(r){return r.json();}).then(function(d){
        if (d.url) {
            var container = document.getElementById('tool-' + toolId);
            var hiddenInput = container.querySelector('[data-field="image"]');
            if (hiddenInput) hiddenInput.value = d.url;
            if (img && img.tagName === 'IMG') { img.src = d.url; }
            else {
                var parent = img ? img.parentElement : null;
                if (parent) parent.innerHTML = '<img id="tool-img-'+toolId+'" src="'+d.url+'" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display=\'none\'">';
            }
        } else { Swal.fire('Upload Failed', d.error || 'Could not upload image', 'error'); }
    }).catch(function(){ Swal.fire('Upload Failed', 'Network error', 'error'); });
}

/* ---- Collect data from builders ---- */
function collectSteps(containerId) {
    var steps = [];
    document.getElementById(containerId).children.forEach(function(el) {
        var title = el.querySelector('[data-field="title"]').value.trim();
        var desc = el.querySelector('[data-field="desc"]').value.trim();
        var video = el.querySelector('[data-field="video"]').value.trim();
        if (title || desc) {
            var s = title;
            if (desc) s += ': ' + desc;
            if (video) s += ' [video:' + video + ']';
            steps.push(s);
        }
    });
    return steps.join('|');
}
function collectTools() {
    var tools = [];
    document.getElementById('tools-container').children.forEach(function(el) {
        var name = el.querySelector('[data-field="name"]').value.trim();
        var desc = el.querySelector('[data-field="desc"]').value.trim();
        var howto = el.querySelector('[data-field="howto"]').value.trim();
        var image = el.querySelector('[data-field="image"]').value.trim();
        if (name) tools.push({name:name, desc:desc, howto:howto, image:image});
    });
    return JSON.stringify(tools);
}

/* ---- Parse existing data into builders ---- */
function parseStepsToBuilder(str, containerId, addFn) {
    if (!str) return;
    str.split('|').forEach(function(step) {
        step = step.trim();
        if (!step) return;
        var title = step, desc = '', video = '';
        var vm = step.match(/\[video:(https?:\/\/[^\]]+)\]/);
        if (vm) { video = vm[1]; step = step.replace(/\s*\[video:[^\]]+\]/, ''); }
        var colonIdx = step.indexOf(':');
        if (colonIdx > 0 && colonIdx < 60) { title = step.substring(0, colonIdx).trim(); desc = step.substring(colonIdx+1).trim(); }
        else { title = step.replace(/^\d+\.\s*/, '').trim(); }
        addFn(title, desc, video);
    });
}
function parseToolsToBuilder(jsonStr) {
    if (!jsonStr) return;
    try {
        var tools = JSON.parse(jsonStr);
        if (!Array.isArray(tools)) return;
        tools.forEach(function(t) { addTool(t.name||'', t.desc||'', t.howto||'', t.image||''); });
    } catch(e) {}
}

/* ---- Viewer ---- */
function openEqViewer(id) {
    currentEqViewId = id;
    var art = null;
    for (var i = 0; i < eqData.length; i++) { if (eqData[i].id == id) { art = eqData[i]; break; } }
    if (!art) return;
    var html = '';
    if (art.image_url) {
        html += '<div style="text-align:center;margin-bottom:20px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;"><img src="'+esc(art.image_url)+'" style="max-height:180px;max-width:100%;object-fit:contain;" onerror="this.style.display=\'none\'"></div>';
    }
    html += '<div style="display:flex;gap:12px;margin-bottom:20px;"><div style="width:48px;height:48px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="cpu" style="width:24px;height:24px;color:#2563eb;"></i></div>';
    html += '<div><h1 style="font-size:18px;font-weight:800;color:#111827;">'+esc(art.manufacturer)+' '+esc(art.model_name)+'</h1><div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;">';
    html += '<span style="padding:2px 8px;background:#eff6ff;border-radius:12px;font-size:11px;color:#2563eb;font-weight:600;">'+esc(art.device_type)+'</span>';
    if(art.year)html+='<span style="padding:2px 8px;background:#f1f5f9;border-radius:12px;font-size:11px;color:#475569;font-weight:600;">'+esc(art.year)+'</span>';
    if(art.location)html+='<span style="padding:2px 8px;background:#f0fdf4;border-radius:12px;font-size:11px;color:#166534;font-weight:600;">'+esc(art.location)+'</span>';
    html += '</div></div></div>';
    var specs=[['CPU',art.cpu],['RAM',art.ram],['Storage',art.storage],['Display',art.display_spec],['Ports',art.ports]];
    var sH='';specs.forEach(function(s){if(s[1])sH+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;"><span style="font-size:12px;color:#64748b;">'+s[0]+'</span><span style="font-size:12px;color:#111827;font-weight:600;text-align:right;max-width:60%;">'+esc(s[1])+'</span></div>';});
    if(sH)html+='<div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Specifications</h3>'+sH+'</div>';

    // Tools as image grid
    if (art.tools_needed) {
        var tools = []; try { tools = JSON.parse(art.tools_needed); } catch(e) { tools = art.tools_needed.split(',').map(function(t){return {name:t.trim(),desc:'',howto:'',image:''};}); }
        if (tools.length && tools[0].name) {
            html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Required Tools ('+tools.length+')</h3>';
            html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">';
            tools.forEach(function(t) {
                html += '<div class="tool-thumb" onclick="showToolDetail(this)" data-name="'+esc(t.name)+'" data-desc="'+esc(t.desc)+'" data-howto="'+esc(t.howto)+'" data-image="'+esc(t.image)+'" style="position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #e5e7eb;background:#f8fafc;text-align:center;">';
                if (t.image) html += '<img src="'+esc(t.image)+'" style="width:100%;height:80px;object-fit:cover;" onerror="this.style.display=\'none\'">';
                else html += '<div style="height:80px;display:flex;align-items:center;justify-content:center;"><i data-lucide="wrench" style="width:24px;height:24px;color:#cbd5e1;"></i></div>';
                html += '<div style="padding:4px 6px;font-size:10px;font-weight:600;color:#374151;line-height:1.3;">'+esc(t.name)+'</div>';
                html += '<div class="tool-overlay" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);color:#fff;align-items:center;justify-content:center;padding:6px;font-size:10px;font-weight:600;border-radius:8px;">'+esc(t.name)+'</div>';
                html += '</div>';
            });
            html += '</div></div>';
        }
    }

    // Disassembly
    if (art.disassembly_guide) {
        var dSteps = art.disassembly_guide.split('|').filter(function(l){return l.trim();});
        html += renderGuideSteps(dSteps, 'Disassembly', '#dc2626', '#fef2f2', '#fecaca', '#991b1b', '#fee2e2');
    }
    // Assembly
    if (art.assembly_guide) {
        var aSteps = art.assembly_guide.split('|').filter(function(l){return l.trim();});
        html += renderGuideSteps(aSteps, 'Assembly', '#16a34a', '#f0fdf4', '#bbf7d0', '#166534', '#dcfce7');
    }
    // Known Issues
    if (art.known_issues) {
        var issues = art.known_issues.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        html += '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">Known Issues ('+issues.length+')</h3>';
        issues.forEach(function(is){html+='<div style="padding:8px 12px;background:#fef3c7;border-radius:8px;border-left:3px solid #d97706;margin-bottom:6px;"><div style="font-size:12px;color:#92400e;line-height:1.5;">'+esc(is)+'</div></div>';});
        html += '</div>';
    }
    if(art.notes){html+='<div style="padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:4px;">Notes</h3><p style="font-size:12px;color:#64748b;line-height:1.5;">'+esc(art.notes)+'</p></div>';}
    document.getElementById('eq-viewer-content').innerHTML = html;
    document.getElementById('eq-viewer-overlay').style.display='block';
    document.getElementById('eq-viewer-panel').style.display='block';
    if(typeof lucide!=='undefined')lucide.createIcons();
}
function renderGuideSteps(steps, title, color, bgColor, borderColor, textColor, badgeBg) {
    var icon = title==='Disassembly' ? '&#128295;' : '&#9989;';
    var h = '<div style="margin-bottom:16px;"><h3 style="font-size:13px;font-weight:700;color:'+textColor+';margin-bottom:8px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:'+badgeBg+';font-size:11px;">'+icon+'</span> '+title+' Guide ('+steps.length+' steps)</h3>';
    h += '<div style="background:'+bgColor+';border:1px solid '+borderColor+';border-radius:10px;padding:14px 16px;">';
    steps.forEach(function(step,i){
        var video='';var vm=step.match(/\[video:(https?:\/\/[^\]]+)\]/);if(vm){video=vm[1];step=step.replace(/\s*\[video:[^\]]+\]/,'');}
        h+='<div style="padding:8px 0;'+(i<steps.length-1?'border-bottom:1px solid '+borderColor+';':'')+'"><div style="display:flex;gap:10px;"><div style="width:22px;height:22px;border-radius:50%;background:'+color+';color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;">'+(i+1)+'</div><div style="flex:1;"><div style="font-size:12px;color:'+textColor+';line-height:1.5;">'+esc(step.replace(/^\d+\.\s*/,''))+'</div>';
        if(video)h+='<a href="'+esc(video)+'" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;padding:3px 8px;background:'+badgeBg+';border-radius:6px;font-size:10px;color:'+textColor+';font-weight:600;text-decoration:none;"><i data-lucide="play-circle" style="width:12px;height:12px;"></i> Watch Video</a>';
        h+='</div></div></div>';
    });
    h += '</div></div>';
    return h;
}
function closeEqViewer(){document.getElementById('eq-viewer-overlay').style.display='none';document.getElementById('eq-viewer-panel').style.display='none';}

/* ---- Tool hover and detail ---- */
document.addEventListener('mouseover',function(e){var t=e.target.closest('.tool-thumb');if(t){var o=t.querySelector('.tool-overlay');if(o)o.style.display='flex';}});
document.addEventListener('mouseout',function(e){var t=e.target.closest('.tool-thumb');if(t){var o=t.querySelector('.tool-overlay');if(o)o.style.display='none';}});
function showToolDetail(el){
    var name=el.dataset.name,desc=el.dataset.desc,howto=el.dataset.howto,image=el.dataset.image;
    var body='<div style="text-align:center;margin-bottom:12px;">';
    if(image)body+='<img src="'+esc(image)+'" style="max-height:120px;border-radius:8px;" onerror="this.style.display=\'none\'">';
    body+='</div><h3 style="font-size:15px;font-weight:800;color:#111827;margin-bottom:8px;">'+esc(name)+'</h3>';
    if(desc)body+='<p style="font-size:12px;color:#64748b;margin-bottom:8px;">'+esc(desc)+'</p>';
    if(howto)body+='<div style="padding:8px 12px;background:#eff6ff;border-radius:8px;border-left:3px solid #2563eb;"><div style="font-size:11px;font-weight:700;color:#2563eb;margin-bottom:2px;">How to use:</div><div style="font-size:12px;color:#374151;">'+esc(howto)+'</div></div>';
    Swal.fire({title:name,html:body,icon:null,confirmButtonColor:'#2563eb',confirmButtonText:'Close',width:400});
}

/* ---- Editor ---- */
function openEqEditor(id) {
    document.getElementById('eq-editor-form').reset();
    document.getElementById('eq-edit-id').value = '';
    document.getElementById('eq-editor-title').textContent = 'Add Equipment';
    document.getElementById('dis-steps-container').innerHTML = '';
    document.getElementById('asm-steps-container').innerHTML = '';
    document.getElementById('tools-container').innerHTML = '';
    disStepCounter=0;asmStepCounter=0;toolCounter=0;
    if (id) {
        var art=null;for(var i=0;i<eqData.length;i++){if(eqData[i].id==id){art=eqData[i];break;}}
        if(art){
            document.getElementById('eq-editor-title').textContent='Edit Equipment';
            document.getElementById('eq-edit-id').value=art.id;
            document.getElementById('eq-edit-mfg').value=art.manufacturer||'';
            document.getElementById('eq-edit-model').value=art.model_name||'';
            document.getElementById('eq-edit-type').value=art.device_type||'Laptop';
            document.getElementById('eq-edit-year').value=art.year||'';
            document.getElementById('eq-edit-cpu').value=art.cpu||'';
            document.getElementById('eq-edit-ram').value=art.ram||'';
            document.getElementById('eq-edit-storage').value=art.storage||'';
            document.getElementById('eq-edit-display').value=art.display_spec||'';
            document.getElementById('eq-edit-ports').value=art.ports||'';
            document.getElementById('eq-edit-issues').value=art.known_issues||'';
            document.getElementById('eq-edit-image').value=art.image_url||'';
            var imgPrev=document.getElementById('eq-edit-img-preview');
            if(art.image_url){imgPrev.innerHTML='<img src="'+art.image_url+'" style="width:100%;height:100%;object-fit:cover;border-radius:8px;" onerror="this.style.display=\'none\'">';}else{imgPrev.innerHTML='<i data-lucide="camera" style="width:20px;height:20px;color:#94a3b8;"></i>';}
            document.getElementById('eq-edit-location').value=art.location||'';
            document.getElementById('eq-edit-asset').value=art.asset_tag||'';
            document.getElementById('eq-edit-notes').value=art.notes||'';
            parseStepsToBuilder(art.disassembly_guide,'dis-steps-container',addDisStep);
            parseStepsToBuilder(art.assembly_guide,'asm-steps-container',addAsmStep);
            parseToolsToBuilder(art.tools_needed);
        }
    }
    document.getElementById('eq-editor-overlay').style.display='block';
    document.getElementById('eq-editor-panel').style.display='block';
    if(typeof lucide!=='undefined')lucide.createIcons();
}
function closeEqEditor(){document.getElementById('eq-editor-overlay').style.display='none';document.getElementById('eq-editor-panel').style.display='none';}

function saveEq(e) {
    e.preventDefault();
    var id = document.getElementById('eq-edit-id').value;
    var data = {
        manufacturer:document.getElementById('eq-edit-mfg').value.trim(),
        model_name:document.getElementById('eq-edit-model').value.trim(),
        device_type:document.getElementById('eq-edit-type').value,
        year:document.getElementById('eq-edit-year').value.trim(),
        cpu:document.getElementById('eq-edit-cpu').value.trim(),
        ram:document.getElementById('eq-edit-ram').value.trim(),
        storage:document.getElementById('eq-edit-storage').value.trim(),
        display_spec:document.getElementById('eq-edit-display').value.trim(),
        ports:document.getElementById('eq-edit-ports').value.trim(),
        known_issues:document.getElementById('eq-edit-issues').value.trim(),
        image_url:document.getElementById('eq-edit-image').value.trim(),
        location:document.getElementById('eq-edit-location').value.trim(),
        asset_tag:document.getElementById('eq-edit-asset').value.trim(),
        notes:document.getElementById('eq-edit-notes').value.trim(),
        disassembly_guide:collectSteps('dis-steps-container'),
        assembly_guide:collectSteps('asm-steps-container'),
        tools_needed:collectTools(),
    };
    if(!data.manufacturer||!data.model_name){Swal.fire('Required','Manufacturer and Model are required.','warning');return false;}
    if(id)data.id=parseInt(id);
    var saveBtn=document.getElementById('eq-editor-save-btn');
    if(typeof setButtonLoading==='function') setButtonLoading(saveBtn,true,'Saving…');
    fetch('<?= $urlBase ?>api/equipment/save.php',{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}).then(function(r){return r.json();}).then(function(d){
        if(typeof setButtonLoading==='function') setButtonLoading(saveBtn,false);
        if(d.success){Swal.fire({title:id?'Updated!':'Added!',text:'Equipment has been '+(id?'updated':'added')+'.',icon:'success',timer:1500,showConfirmButton:false});closeEqEditor();setTimeout(function(){location.reload();},1200);}
        else Swal.fire('Error',d.error||'Failed','error');
    }).catch(function(){if(typeof setButtonLoading==='function') setButtonLoading(saveBtn,false);Swal.fire('Error','Network error','error');});
    return false;
}
function eqDelete(id){
    Swal.fire({title:'Delete Equipment?',text:'This action cannot be undone.',icon:'question',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Yes, delete it!',cancelButtonText:'Cancel'}).then(function(r){
        if(!r.isConfirmed)return;
        fetch('<?= $urlBase ?>api/equipment/save.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id})}).then(function(r){return r.json();}).then(function(d){
            var row=document.getElementById('eq-row-'+id);if(row)row.remove();
            Swal.fire({title:'Deleted!',text:'Equipment removed.',icon:'success',timer:1500,showConfirmButton:false});
        }).catch(function(){Swal.fire('Error','Network error','error');});
    });
}
function esc(s){if(!s)return'';var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function escH(s){if(!s)return'';return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
