<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'Knowledge Management';
$active_menu = 'admin-kb';
$required_permission = 'knowledge.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';

// Fetch articles from database
$articles = Database::fetchAll(
    "SELECT ka.id, ka.title, ka.category, ka.status, ka.quality_score, ka.use_count,
            ka.helpful_count, ka.not_helpful_count, ka.created_at, ka.updated_at,
            ka.issue, ka.symptoms, ka.root_cause, ka.solution, ka.tools_used,
            ka.commands_used, ka.device_type, ka.manufacturer, ka.model,
            u.full_name as author_name
     FROM knowledge_articles ka
     LEFT JOIN users u ON ka.author_id = u.id
     WHERE ka.deleted_at IS NULL
     ORDER BY ka.created_at DESC"
);

$countAll = $countPending = $countPublished = $countDraft = 0;
foreach ($articles as $a) {
    $countAll++;
    if (in_array($a['status'], ['submitted', 'under_review'])) $countPending++;
    elseif ($a['status'] === 'published') $countPublished++;
    elseif ($a['status'] === 'draft') $countDraft++;
}
?>
<div>
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico blue"><i data-lucide="file-check"></i></div>
                <div>
                    <h1 class="page-hero-title">Knowledge Base Management</h1>
                    <p class="page-hero-sub">Create, edit, review, and manage knowledge articles</p>
                </div>
            </div>
        </div>
        <div class="page-hero-actions">
            <button onclick="openKbEditor(null)" class="btn btn-primary"><i data-lucide="plus" style="width:15px;height:15px;"></i> New Article</button>
        </div>
    </div>

    <!-- Filters -->
    <div style="display:flex;gap:8px;margin-bottom:20px;overflow-x:auto;padding-bottom:4px;">
        <button onclick="kbFilterAdmin('all')" class="kb-filter-btn" data-filter="all" style="padding:6px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;background:#2563eb;color:#fff;">All (<?= $countAll ?>)</button>
        <button onclick="kbFilterAdmin('submitted')" class="kb-filter-btn" data-filter="submitted" style="padding:6px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;background:#f1f5f9;color:#475569;">Pending Review (<?= $countPending ?>)</button>
        <button onclick="kbFilterAdmin('published')" class="kb-filter-btn" data-filter="published" style="padding:6px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;background:#f1f5f9;color:#475569;">Published (<?= $countPublished ?>)</button>
        <button onclick="kbFilterAdmin('draft')" class="kb-filter-btn" data-filter="draft" style="padding:6px 16px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;background:#f1f5f9;color:#475569;">Drafts (<?= $countDraft ?>)</button>
    </div>

    <!-- Articles Table -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Article</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;" class="hide-mobile">Author</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Status</th>
                    <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;" class="hide-mobile">Uses</th>
                    <th style="text-align:right;padding:12px 20px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr id="kb-row-<?= $a['id'] ?>" style="border-bottom:1px solid #f1f5f9;" data-status="<?= e($a['status']) ?>">
                        <td style="padding:14px 20px;">
                            <div style="font-size:13px;font-weight:600;color:#111827;"><?= e($a['title']) ?></div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?= e(ucfirst($a['category'] ?? 'General')) ?> · <?= e($a['device_type'] ?? 'Any') ?> · <?= date('M j, Y', strtotime($a['created_at'])) ?></div>
                        </td>
                        <td style="padding:14px 20px;" class="hide-mobile">
                            <span style="font-size:13px;color:#64748b;"><?= e($a['author_name'] ?? 'Unknown') ?></span>
                        </td>
                        <td style="padding:14px 20px;">
                            <?php
                            $statusMap = [
                                'published' => ['bg'=>'#dcfce7','fg'=>'#166534','label'=>'Published'],
                                'submitted' => ['bg'=>'#fef3c7','fg'=>'#92400e','label'=>'Pending Review'],
                                'under_review' => ['bg'=>'#fef3c7','fg'=>'#92400e','label'=>'Under Review'],
                                'draft' => ['bg'=>'#f1f5f9','fg'=>'#475569','label'=>'Draft'],
                                'rejected' => ['bg'=>'#fee2e2','fg'=>'#991b1b','label'=>'Rejected'],
                            ];
                            $sc = $statusMap[$a['status']] ?? $statusMap['draft'];
                            ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:<?= $sc['bg'] ?>;color:<?= $sc['fg'] ?>;"><?= $sc['label'] ?></span>
                        </td>
                        <td style="padding:14px 20px;" class="hide-mobile">
                            <span style="font-size:13px;color:#64748b;"><?= intval($a['use_count']) ?> uses</span>
                        </td>
                        <td style="padding:14px 20px;text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                                <?php if (in_array($a['status'], ['submitted', 'under_review', 'draft'])): ?>
                                    <button onclick="kbApprove(<?= $a['id'] ?>)" style="padding:4px 10px;background:#dcfce7;color:#166534;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;" onmouseover="this.style.background='#bbf7d0'" onmouseout="this.style.background='#dcfce7'">Approve</button>
                                    <button onclick="kbReject(<?= $a['id'] ?>)" style="padding:4px 10px;background:#fee2e2;color:#991b1b;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">Reject</button>
                                <?php endif; ?>
                                <button onclick="openKbViewer(<?= $a['id'] ?>)" title="View article" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#16a34a';this.style.background='#f0fdf4'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="eye" style="width:14px;height:14px;"></i></button>
                                <button onclick="openKbEditor(<?= $a['id'] ?>)" title="Edit article" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#2563eb';this.style.background='#eff6ff'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="pencil" style="width:14px;height:14px;"></i></button>
                                <button onclick="kbDeleteArticle(<?= $a['id'] ?>)" title="Delete article" style="padding:6px;border-radius:6px;color:#94a3b8;background:none;border:none;cursor:pointer;display:inline-flex;" onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'" onmouseout="this.style.color='#94a3b8';this.style.background='none'"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($articles)): ?>
            <div style="text-align:center;padding:40px;">
                <i data-lucide="book-open" style="width:36px;height:36px;color:#cbd5e1;margin-bottom:8px;"></i>
                <p style="color:#94a3b8;font-size:13px;">No articles yet. Click "New Article" to create one.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== EDITOR MODAL ===== -->
<div id="kb-editor-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeKbEditor()"></div>
<div id="kb-editor-panel" style="display:none;position:fixed;top:0;right:0;width:min(640px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 id="kb-editor-title" style="font-size:16px;font-weight:800;color:#111827;">New Article</h2>
        <button onclick="closeKbEditor()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
    </div>
    <form id="kb-editor-form" onsubmit="return saveKbArticle(event)" style="padding:20px 24px 40px;">
        <input type="hidden" id="kb-edit-id" value="">

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Title *</label>
            <input type="text" id="kb-edit-title" required placeholder="e.g., No Display Troubleshooting" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;box-sizing:border-box;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Category *</label>
                <select id="kb-edit-category" required style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#fff;box-sizing:border-box;">
                    <option value="Display">Display</option><option value="Power">Power</option><option value="Network">Network</option>
                    <option value="Printer">Printer</option><option value="Software">Software</option><option value="CCTV">CCTV</option>
                    <option value="Audio">Audio</option><option value="Hardware">Hardware</option><option value="Security">Security</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Device Type</label>
                <select id="kb-edit-device" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#fff;box-sizing:border-box;">
                    <option value="">Any / General</option><option value="Desktop">Desktop</option><option value="Laptop">Laptop</option>
                    <option value="Server">Server</option><option value="Printer">Printer</option><option value="Router">Router</option>
                    <option value="CCTV">CCTV</option><option value="Monitor">Monitor</option>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Status</label>
                <select id="kb-edit-status" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#fff;box-sizing:border-box;">
                    <option value="draft">Draft</option><option value="submitted">Submitted (Pending Review)</option>
                    <option value="published">Published</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Manufacturer</label>
                <input type="text" id="kb-edit-manufacturer" placeholder="e.g., HP, Dell (optional)" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;box-sizing:border-box;">
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Issue / Problem Description *</label>
            <textarea id="kb-edit-issue" required rows="2" placeholder="Describe the problem this article addresses..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;resize:vertical;box-sizing:border-box;"></textarea>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Symptoms (comma-separated)</label>
            <input type="text" id="kb-edit-symptoms" placeholder="e.g., Black screen, No signal, Monitor LED on" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Root Cause</label>
            <textarea id="kb-edit-rootcause" rows="2" placeholder="What typically causes this issue..." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;resize:vertical;box-sizing:border-box;"></textarea>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Solution Steps (one per line, format: Title:Description) *</label>
            <textarea id="kb-edit-solution" required rows="8" placeholder="Check Power:Verify both computer and monitor have power cables connected.&#10;Reseat Display Cable:Power off and reconnect HDMI/DisplayPort cable at both ends.&#10;Test Different Cable:Try a known-good display cable." style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;resize:vertical;font-family:monospace;box-sizing:border-box;"></textarea>
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Tools Needed (comma-separated)</label>
            <input type="text" id="kb-edit-tools" placeholder="e.g., Phillips screwdriver, Multimeter, Known-good cable" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Related Commands (comma-separated)</label>
            <input type="text" id="kb-edit-commands" placeholder="e.g., sfc /scannow, chkdsk, ipconfig" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;box-sizing:border-box;">
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid #f1f5f9;">
            <button type="button" onclick="closeKbEditor()" style="padding:8px 16px;background:#f1f5f9;color:#475569;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
            <button type="submit" id="kb-editor-save-btn" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Save Article</button>
        </div>
    </form>
</div>

<!-- ===== VIEWER MODAL ===== -->
<div id="kb-viewer-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;backdrop-filter:blur(4px);" onclick="closeKbViewer()"></div>
<div id="kb-viewer-panel" style="display:none;position:fixed;top:0;right:0;width:min(700px,95vw);height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 24px rgba(0,0,0,0.15);overflow-y:auto;">
    <div style="position:sticky;top:0;background:#fff;border-bottom:1px solid #e5e7eb;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;z-index:1;">
        <h2 style="font-size:16px;font-weight:800;color:#111827;">Article Preview</h2>
        <div style="display:flex;gap:6px;align-items:center;">
            <button onclick="closeKbViewer();openKbEditor(currentKbViewId)" style="padding:5px 10px;background:#eff6ff;color:#2563eb;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit</button>
            <button onclick="closeKbViewer()" style="background:none;border:none;cursor:pointer;padding:4px;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i data-lucide="x" style="width:18px;height:18px;color:#64748b;"></i></button>
        </div>
    </div>
    <div id="kb-viewer-content" style="padding:24px;"></div>
</div>

<script>
/* ---- Filter ---- */
function kbFilterAdmin(status) {
    document.querySelectorAll('[data-status]').forEach(function(row) {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
    document.querySelectorAll('.kb-filter-btn').forEach(function(btn) {
        if (btn.dataset.filter === status) {
            btn.style.background = '#2563eb'; btn.style.color = '#fff';
        } else {
            btn.style.background = '#f1f5f9'; btn.style.color = '#475569';
        }
    });
}

/* ---- Articles data ---- */
var kbArticles = <?= json_encode($articles) ?>;
var currentKbViewId = null;

/* ---- Viewer Modal ---- */
function openKbViewer(id) {
    currentKbViewId = id;
    var art = null;
    for (var i = 0; i < kbArticles.length; i++) {
        if (kbArticles[i].id == id) { art = kbArticles[i]; break; }
    }
    if (!art) return;

    var catColors = {Display:'#2563eb',Power:'#dc2626',Network:'#16a34a',Printer:'#ea580c',Software:'#9333ea',CCTV:'#475569',Audio:'#ca8a04',Hardware:'#0891b2',Security:'#be123c'};
    var catColor = catColors[art.category] || '#475569';

    var html = '';
    // Header
    html += '<div style="margin-bottom:20px;">';
    html += '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">';
    html += '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:' + catColor + '15;color:' + catColor + ';">' + (art.category || 'General') + '</span>';
    var statusMap = {published:['#dcfce7','#166534','Published'],submitted:['#fef3c7','#92400e','Pending Review'],draft:['#f1f5f9','#475569','Draft']};
    var sc = statusMap[art.status] || statusMap.draft;
    html += '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:' + sc[0] + ';color:' + sc[1] + ';">' + sc[2] + '</span>';
    if (art.device_type) html += '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#f1f5f9;color:#475569;">' + art.device_type + '</span>';
    html += '</div>';
    html += '<h1 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:6px;">' + escHtml(art.title) + '</h1>';
    if (art.issue) html += '<p style="font-size:13px;color:#475569;line-height:1.6;background:#f8fafc;padding:12px 16px;border-radius:8px;border-left:3px solid ' + catColor + ';">' + escHtml(art.issue) + '</p>';
    html += '<div style="display:flex;gap:14px;margin-top:12px;font-size:11px;color:#94a3b8;">';
    html += '<span>By ' + escHtml(art.author_name || 'Admin') + '</span>';
    html += '<span>' + art.use_count + ' views</span>';
    html += '<span>' + (art.quality_score > 0 ? art.quality_score + '% quality' : 'No ratings yet') + '</span>';
    html += '</div>';
    html += '</div>';

    // Symptoms
    if (art.symptoms) {
        var syms = art.symptoms.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        if (syms.length) {
            html += '<div style="margin-bottom:16px;">';
            html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">\u{1F50D} Symptoms</h3>';
            html += '<div style="display:flex;gap:6px;flex-wrap:wrap;">';
            syms.forEach(function(s) { html += '<span style="padding:3px 10px;background:#f1f5f9;border-radius:6px;font-size:11px;color:#475569;font-weight:500;">' + escHtml(s) + '</span>'; });
            html += '</div></div>';
        }
    }

    // Root Cause
    if (art.root_cause) {
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">\u{1F3AF} Root Cause</h3>';
        html += '<p style="font-size:13px;color:#475569;line-height:1.6;">' + escHtml(art.root_cause) + '</p>';
        html += '</div>';
    }

    // Solution Steps
    if (art.solution) {
        var lines = art.solution.split('\n').filter(function(l){return l.trim();});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;">\u{1F527} Solution Steps (' + lines.length + ')</h3>';
        lines.forEach(function(line, idx) {
            var title = 'Step ' + (idx+1), desc = line;
            var colonIdx = line.indexOf(':');
            if (colonIdx > 0 && colonIdx < 60) { title = line.substring(0, colonIdx).trim(); desc = line.substring(colonIdx+1).trim(); }
            html += '<div style="display:flex;gap:10px;padding:10px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:6px;">';
            html += '<div style="width:24px;height:24px;border-radius:6px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;">' + (idx+1) + '</div>';
            html += '<div><div style="font-size:12px;font-weight:700;color:#111827;">' + escHtml(title) + '</div>';
            html += '<div style="font-size:11.5px;color:#64748b;line-height:1.5;">' + escHtml(desc) + '</div></div></div>';
        });
        html += '</div>';
    }

    // Tools
    if (art.tools_used) {
        var tools = art.tools_used.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">\u{1F6E0}\u{FE0F} Tools Needed</h3>';
        html += '<div style="display:flex;gap:6px;flex-wrap:wrap;">';
        tools.forEach(function(t) { html += '<span style="padding:3px 10px;background:#eff6ff;border-radius:6px;font-size:11px;color:#2563eb;font-weight:600;">' + escHtml(t) + '</span>'; });
        html += '</div></div>';
    }

    // Commands
    if (art.commands_used) {
        var cmds = art.commands_used.split(',').map(function(s){return s.trim();}).filter(function(s){return s;});
        html += '<div style="margin-bottom:16px;">';
        html += '<h3 style="font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">\u{1F4BB} Related Commands</h3>';
        html += '<div style="display:flex;gap:6px;flex-wrap:wrap;">';
        cmds.forEach(function(c) { html += '<code style="padding:3px 10px;background:#f1f5f9;border-radius:6px;font-size:11px;font-weight:600;color:#1d4ed8;">' + escHtml(c) + '</code>'; });
        html += '</div></div>';
    }

    document.getElementById('kb-viewer-content').innerHTML = html;
    document.getElementById('kb-viewer-overlay').style.display = 'block';
    document.getElementById('kb-viewer-panel').style.display = 'block';
}

function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/* ---- Editor Modal ---- */
function openKbEditor(id) {
    var form = document.getElementById('kb-editor-form');
    form.reset();
    document.getElementById('kb-edit-id').value = '';
    document.getElementById('kb-editor-title').textContent = 'New Article';

    if (id) {
        // Find article data
        var art = null;
        for (var i = 0; i < kbArticles.length; i++) {
            if (kbArticles[i].id == id) { art = kbArticles[i]; break; }
        }
        if (art) {
            document.getElementById('kb-editor-title').textContent = 'Edit Article';
            document.getElementById('kb-edit-id').value = art.id;
            document.getElementById('kb-edit-title').value = art.title || '';
            document.getElementById('kb-edit-category').value = art.category || 'Display';
            document.getElementById('kb-edit-device').value = art.device_type || '';
            document.getElementById('kb-edit-status').value = art.status || 'draft';
            document.getElementById('kb-edit-manufacturer').value = art.manufacturer || '';
            document.getElementById('kb-edit-issue').value = art.issue || '';
            document.getElementById('kb-edit-symptoms').value = art.symptoms || '';
            document.getElementById('kb-edit-rootcause').value = art.root_cause || '';
            document.getElementById('kb-edit-solution').value = art.solution || '';
            document.getElementById('kb-edit-tools').value = art.tools_used || '';
            document.getElementById('kb-edit-commands').value = art.commands_used || '';
        }
    }

    document.getElementById('kb-editor-overlay').style.display = 'block';
    document.getElementById('kb-editor-panel').style.display = 'block';
}

function closeKbEditor() {
    document.getElementById('kb-editor-overlay').style.display = 'none';
    document.getElementById('kb-editor-panel').style.display = 'none';
}

// Move modals outside page-content to ensure position:fixed works correctly
(function(){
    var eOv = document.getElementById('kb-editor-overlay');
    var ePanel = document.getElementById('kb-editor-panel');
    var vOv = document.getElementById('kb-viewer-overlay');
    var vPanel = document.getElementById('kb-viewer-panel');
    if(eOv) document.body.appendChild(eOv);
    if(ePanel) document.body.appendChild(ePanel);
    if(vOv) document.body.appendChild(vOv);
    if(vPanel) document.body.appendChild(vPanel);
})();

function saveKbArticle(e) {
    e.preventDefault();
    var id = document.getElementById('kb-edit-id').value;
    var data = {
        title: document.getElementById('kb-edit-title').value.trim(),
        category: document.getElementById('kb-edit-category').value,
        device_type: document.getElementById('kb-edit-device').value,
        status: document.getElementById('kb-edit-status').value,
        manufacturer: document.getElementById('kb-edit-manufacturer').value.trim(),
        issue: document.getElementById('kb-edit-issue').value.trim(),
        symptoms: document.getElementById('kb-edit-symptoms').value.trim(),
        root_cause: document.getElementById('kb-edit-rootcause').value.trim(),
        solution: document.getElementById('kb-edit-solution').value.trim(),
        tools_used: document.getElementById('kb-edit-tools').value.trim(),
        commands_used: document.getElementById('kb-edit-commands').value.trim(),
    };

    if (!data.title || !data.issue || !data.solution) {
        Swal.fire('Required', 'Title, Issue, and Solution Steps are required.', 'warning');
        return false;
    }

    var url = '<?= $urlBase ?>api/knowledge/save.php';
    var method = id ? 'PUT' : 'POST';
    if (id) data.id = parseInt(id);

    var saveBtn = document.getElementById('kb-editor-save-btn');
    if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, true, 'Saving…');

    fetch(url, { method: method, headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data) })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, false);
        if (d.success) {
            Swal.fire({ title: id ? 'Updated!' : 'Created!', text: 'Article has been ' + (id ? 'updated' : 'created') + '.', icon: 'success', timer: 1500, showConfirmButton: false });
            closeKbEditor();
            setTimeout(function() { location.reload(); }, 1200);
        } else {
            Swal.fire('Error', d.error || 'Failed to save', 'error');
        }
    })
    .catch(function() { if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, false); Swal.fire('Error', 'Network error', 'error'); });
    return false;
}

/* ---- Approve / Reject / Delete ---- */
function kbApprove(id) {
    Swal.fire({ title: 'Approve Article?', text: 'This article will be published and visible to all users.', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#16a34a', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve it!', cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        fetch('<?= $urlBase ?>api/knowledge/approve.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ article_id: id, action: 'approve' })
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                Swal.fire({ title: 'Approved!', text: 'Article is now published.', icon: 'success', timer: 1500, showConfirmButton: false });
                setTimeout(function() { location.reload(); }, 1200);
            } else { Swal.fire('Error', d.error || 'Failed', 'error'); }
        }).catch(function() { Swal.fire('Error', 'Network error', 'error'); });
    });
}

function kbReject(id) {
    Swal.fire({ title: 'Reject Article?', text: 'This article will be moved back to draft status.', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, reject it!', cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        fetch('<?= $urlBase ?>api/knowledge/approve.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ article_id: id, action: 'reject' })
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                Swal.fire({ title: 'Rejected', text: 'Article moved to draft.', icon: 'info', timer: 1500, showConfirmButton: false });
                setTimeout(function() { location.reload(); }, 1200);
            } else { Swal.fire('Error', d.error || 'Failed', 'error'); }
        }).catch(function() { Swal.fire('Error', 'Network error', 'error'); });
    });
}

function kbDeleteArticle(id) {
    Swal.fire({ title: 'Delete Article?', text: 'This action cannot be undone.', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!', cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        fetch('<?= $urlBase ?>api/knowledge/approve.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ article_id: id, action: 'delete' })
        }).then(function(r) { return r.json(); }).then(function(d) {
            var row = document.getElementById('kb-row-' + id);
            if (row) row.remove();
            Swal.fire({ title: 'Deleted!', text: 'Article has been removed.', icon: 'success', timer: 1500, showConfirmButton: false });
        }).catch(function() { Swal.fire('Error', 'Network error', 'error'); });
    });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
