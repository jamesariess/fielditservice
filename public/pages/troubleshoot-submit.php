<?php
$page_title = 'Submit Troubleshooting';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';
?>

<style>
.ts-wrap { max-width: 900px; margin: 0 auto; padding: 0 4px; }
.ts-head { margin-bottom: 20px; }
.ts-head h1 { font-size: 22px; font-weight: 800; color: #0f172a; }
.dark .ts-head h1 { color: #f1f5f9; }
.ts-head p { font-size: 13px; color: #64748b; margin-top: 4px; }

.ts-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    padding: 20px; margin-bottom: 16px;
}
.dark .ts-card { background: #1e293b; border-color: #334155; }
.ts-card h3 { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.dark .ts-card h3 { color: #f1f5f9; }

.ts-fg { margin-bottom: 12px; }
.ts-fl { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.dark .ts-fl { color: #94a3b8; }
.ts-fi, .ts-fs, .ts-ft {
    width: 100%; padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; font-family: inherit; box-sizing: border-box;
}
.ts-fi:focus, .ts-fs:focus, .ts-ft:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.dark .ts-fi, .dark .ts-fs, .dark .ts-ft { background: #0f172a; border-color: #334155; color: #f1f5f9; }
.ts-ft { min-height: 50px; resize: vertical; }
.ts-fr { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* List items */
.ts-list { margin-bottom: 12px; }
.ts-item {
    display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    margin-bottom: 8px; position: relative;
}
.dark .ts-item { background: #0f172a; border-color: #1e293b; }
.ts-item-num {
    width: 24px; height: 24px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; font-size: 11px;
    font-weight: 700; color: #fff; flex-shrink: 0;
}
.ts-item-num.q { background: #2563eb; }
.ts-item-num.s { background: #16a34a; }
.ts-item-num.t { background: #d97706; }
.ts-item-body { flex: 1; min-width: 0; }
.ts-item-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.dark .ts-item-title { color: #f1f5f9; }
.ts-item-desc { font-size: 11px; color: #64748b; margin-top: 2px; }
.ts-item-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px; }
.ts-item-tag {
    display: inline-block; padding: 2px 8px; border-radius: 6px;
    font-size: 10px; font-weight: 600;
}
.ts-item-tag.risk-safe { background: #dcfce7; color: #16a34a; }
.ts-item-tag.risk-caution { background: #fef3c7; color: #d97706; }
.ts-item-tag.risk-danger { background: #fee2e2; color: #dc2626; }
.ts-item-tag.vis-yes { background: #dcfce7; color: #16a34a; }
.ts-item-tag.vis-no { background: #fee2e2; color: #dc2626; }
.ts-item-tag.vis-both { background: #fef3c7; color: #d97706; }
.ts-item-tag.vis-always { background: #f1f5f9; color: #6b7280; }
.ts-item-del {
    background: none; border: none; cursor: pointer; color: #94a3b8;
    padding: 4px; flex-shrink: 0;
}
.ts-item-del:hover { color: #dc2626; }

.ts-add-btn {
    display: flex; align-items: center; gap: 6px; padding: 10px;
    border: 2px dashed #d1d5db; border-radius: 10px; background: transparent;
    cursor: pointer; font-size: 13px; font-weight: 600; color: #64748b;
    width: 100%; justify-content: center; transition: all 0.2s;
}
.ts-add-btn:hover { border-color: #2563eb; color: #2563eb; background: #f0f9ff; }

/* Table view for YES/NO/BOTH */
.ts-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
.ts-table th {
    padding: 10px 12px; text-align: left; font-weight: 700; font-size: 12px;
    background: #f1f5f9; color: #374151; border-bottom: 2px solid #e2e8f0;
}
.dark .ts-table th { background: #1e293b; color: #e2e8f0; border-color: #334155; }
.ts-table td {
    padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top;
}
.dark .ts-table td { border-color: #1e293b; }
.ts-table tr:last-child td { border-bottom: none; }
.ts-table .col-yes { background: rgba(22,163,74,0.04); }
.ts-table .col-no { background: rgba(220,38,38,0.04); }
.ts-table .col-both { background: rgba(217,119,6,0.04); }
.ts-step-tag {
    display: inline-block; padding: 2px 8px; border-radius: 6px;
    font-size: 11px; font-weight: 600; margin-bottom: 4px;
}
.ts-step-tag.yes { background: #dcfce7; color: #16a34a; }
.ts-step-tag.no { background: #fee2e2; color: #dc2626; }
.ts-step-tag.both { background: #fef3c7; color: #d97706; }
.ts-step-tag.always { background: #f3f4f6; color: #6b7280; }

.ts-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.ts-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px;
    border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.2s; font-family: inherit;
}
.ts-btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.25); }
.ts-btn-primary:hover { transform: translateY(-1px); }
.ts-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

/* My submissions */
.ts-my-subs { margin-top: 28px; }
.ts-my-subs h2 { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 14px; }
.dark .ts-my-subs h2 { color: #f1f5f9; }
.ts-sub-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; margin-bottom: 6px;
}
.dark .ts-sub-item { background: #1e293b; border-color: #334155; }
.ts-sub-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.dark .ts-sub-title { color: #f1f5f9; }
.ts-sub-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.ts-sub-status {
    display: inline-flex; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600; flex-shrink: 0;
}
.ts-sub-status.pending { background: #fef3c7; color: #d97706; }
.ts-sub-status.approved { background: #dcfce7; color: #16a34a; }
.ts-sub-status.rejected { background: #fee2e2; color: #dc2626; }

/* Modal */
.ts-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    z-index: 1000; align-items: center; justify-content: center;
}
.ts-modal-overlay.open { display: flex; }
.ts-modal {
    background: #fff; border-radius: 16px; padding: 24px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 85vh; overflow-y: auto;
}
.dark .ts-modal { background: #1e293b; }
.ts-modal h3 { font-size: 16px; font-weight: 700; margin-bottom: 16px; color: #0f172a; }
.dark .ts-modal h3 { color: #f1f5f9; }
.ts-modal-close {
    float: right; background: none; border: none; cursor: pointer;
    color: #94a3b8; padding: 4px;
}
.ts-modal-close:hover { color: #dc2626; }

/* Guide groups in step modal */
.ts-guide-group {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; margin-bottom: 8px;
    background: #f8fafc;
}
.dark .ts-guide-group { background: #0f172a; border-color: #1e293b; }
.ts-guide-head {
    display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
}
.ts-guide-num {
    width: 20px; height: 20px; border-radius: 50%; background: #2563eb; color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700;
}
.ts-guide-remove {
    margin-left: auto; background: none; border: none; cursor: pointer;
    color: #94a3b8; font-size: 16px;
}
.ts-guide-remove:hover { color: #dc2626; }

@media (max-width: 640px) { .ts-fr { grid-template-columns: 1fr; } }
</style>

<div class="ts-wrap">
    <div class="ts-head">
        <h1><i data-lucide="file-plus" style="width:22px;height:22px;vertical-align:middle;margin-right:6px;"></i>Submit Troubleshooting Steps</h1>
        <p>Add questions and steps for a troubleshooting issue. Steps are organized by when they appear (YES, NO, or BOTH).</p>
    </div>

    <!-- Issue Details -->
    <div class="ts-card">
        <h3><i data-lucide="clipboard-list" style="width:18px;height:18px;color:#2563eb;"></i> Issue Details</h3>
        <div class="ts-fg">
            <label class="ts-fl">Issue Title *</label>
            <input type="text" class="ts-fi" id="sub-title" placeholder="e.g., Projector Not Turning On">
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Category</label>
                <select class="ts-fs" id="sub-category">
                    <option value="display">Display</option><option value="power">Power</option>
                    <option value="sound">Sound</option><option value="network">Network</option>
                    <option value="printer">Printer</option><option value="cctv">CCTV</option>
                    <option value="software">Software</option><option value="other">Other</option>
                </select>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Severity</label>
                <select class="ts-fs" id="sub-severity">
                    <option value="high">High</option><option value="medium" selected>Medium</option><option value="low">Low</option>
                </select>
            </div>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Description</label>
                <textarea class="ts-ft" id="sub-desc" placeholder="Brief description..."></textarea>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Device Types (comma separated)</label>
                <input type="text" class="ts-fi" id="sub-devices" placeholder="e.g., Desktop, Laptop, Server">
            </div>
        </div>
    </div>

    <!-- Questions -->
    <div class="ts-card">
        <h3><i data-lucide="help-circle" style="width:18px;height:18px;color:#2563eb;"></i> Diagnostic Questions</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">YES/NO questions the technician answers to filter which steps appear.</p>
        <div id="questions-list"></div>
        <button class="ts-add-btn" onclick="openAddQuestion()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Question
        </button>
    </div>

    <!-- Steps -->
    <div class="ts-card">
        <h3><i data-lucide="wrench" style="width:18px;height:18px;color:#16a34a;"></i> Troubleshooting Steps</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Each step is tied to a question. Choose when it appears: on YES, NO, or BOTH answers.</p>
        <div id="steps-preview"></div>
        <button class="ts-add-btn" onclick="openAddStep()" style="margin-top:8px;">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Step
        </button>
    </div>

    <!-- Terminal Results -->
    <div class="ts-card">
        <h3><i data-lucide="flag" style="width:18px;height:18px;color:#d97706;"></i> Terminal Results</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Final outcomes when troubleshooting ends (solved, escalated, or hardware failure).</p>
        <div id="terminals-list"></div>
        <button class="ts-add-btn" onclick="openAddTerminal()" style="margin-top:8px;">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Result
        </button>
    </div>

    <!-- Actions -->
    <div class="ts-actions">
        <button class="ts-btn ts-btn-secondary" onclick="clearForm()">Clear</button>
        <button class="ts-btn ts-btn-primary" onclick="submitForApproval()">
            <i data-lucide="send" style="width:14px;height:14px;"></i> Submit for Approval
        </button>
    </div>

    <!-- My Submissions -->
    <div class="ts-my-subs">
        <h2>My Submissions</h2>
        <div id="my-submissions"></div>
    </div>
</div>

<!-- ===== Add Question Modal ===== -->
<div class="ts-modal-overlay" id="q-modal">
    <div class="ts-modal">
        <button class="ts-modal-close" onclick="closeModal('q-modal')"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        <h3>Add Diagnostic Question</h3>
        <div class="ts-fg">
            <label class="ts-fl">Question *</label>
            <input type="text" class="ts-fi" id="mq-text" placeholder="e.g., Is the power cable connected?">
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Instructions</label>
            <textarea class="ts-ft" id="mq-desc" placeholder="Detailed instructions for the technician..."></textarea>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Risk Level</label>
                <select class="ts-fs" id="mq-risk">
                    <option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option>
                </select>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Device</label>
                <select class="ts-fs" id="mq-device">
                    <option value="all">All Devices</option>
                </select>
            </div>
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Why must this question be answered?</label>
            <textarea class="ts-ft" id="mq-why" placeholder="e.g., This determines if the issue is power-related or cable-related..." style="min-height:40px;"></textarea>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Explain why this diagnostic question matters.</div>
        </div>
        <div class="ts-actions" style="justify-content:flex-end;">
            <button class="ts-btn ts-btn-secondary" onclick="closeModal('q-modal')">Cancel</button>
            <button class="ts-btn ts-btn-primary" onclick="saveQuestion()">Add Question</button>
        </div>
    </div>
</div>

<!-- ===== Add Step Modal ===== -->
<div class="ts-modal-overlay" id="s-modal">
    <div class="ts-modal" style="max-width:560px;">
        <button class="ts-modal-close" onclick="closeModal('s-modal')"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        <h3>Add Troubleshooting Step</h3>
        <div class="ts-fg">
            <label class="ts-fl">Step Title *</label>
            <input type="text" class="ts-fi" id="ms-title" placeholder="e.g., Reseat the RAM modules">
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Instructions *</label>
            <textarea class="ts-ft" id="ms-desc" placeholder="Detailed instructions for the technician..."></textarea>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Risk Level</label>
                <select class="ts-fs" id="ms-risk">
                    <option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option>
                </select>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Device</label>
                <select class="ts-fs" id="ms-device">
                    <option value="all">All Devices</option>
                </select>
            </div>
        </div>
        <!-- Visual Guides -->
        <div class="ts-fg">
            <label class="ts-fl">Visual Guides (sequential steps)</label>
            <div id="ms-guides-list"></div>
            <button class="ts-add-btn" onclick="addMsGuide()" style="padding:8px;font-size:12px;margin-top:6px;">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Guide Step
            </button>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Sequential sub-steps the technician follows.</div>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Expected Result</label>
                <input type="text" class="ts-fi" id="ms-expected" placeholder="What should happen">
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Tools Needed</label>
                <input type="text" class="ts-fi" id="ms-tools" placeholder="Screwdriver, multimeter">
            </div>
        </div>
        <!-- Visibility Assignment -->
        <div class="ts-fg" style="background:#f0f9ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;">
            <label class="ts-fl" style="color:#2563eb;margin-bottom:8px;">When is this step visible?</label>
            <div class="ts-fr" style="gap:10px;">
                <div class="ts-fg" style="margin-bottom:0;">
                    <label class="ts-fl" style="font-size:11px;">Linked to Question</label>
                    <select class="ts-fs" id="ms-question" style="font-size:13px;"></select>
                </div>
                <div class="ts-fg" style="margin-bottom:0;">
                    <label class="ts-fl" style="font-size:11px;">Show when answer is</label>
                    <select class="ts-fs" id="ms-when" style="font-size:13px;">
                        <option value="always">Always</option>
                        <option value="yes_only">YES only</option>
                        <option value="no_only">NO only</option>
                        <option value="both">BOTH (YES or NO)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ts-actions" style="justify-content:flex-end;">
            <button class="ts-btn ts-btn-secondary" onclick="closeModal('s-modal')">Cancel</button>
            <button class="ts-btn ts-btn-primary" onclick="saveStep()">Add Step</button>
        </div>
    </div>
</div>

<!-- ===== Add Terminal Modal ===== -->
<div class="ts-modal-overlay" id="t-modal">
    <div class="ts-modal">
        <button class="ts-modal-close" onclick="closeModal('t-modal')"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        <h3>Add Terminal Result</h3>
        <div class="ts-fg">
            <label class="ts-fl">Result Title *</label>
            <input type="text" class="ts-fi" id="mt-title" placeholder="e.g., Motherboard Defective — Escalate">
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Description</label>
            <textarea class="ts-ft" id="mt-desc" placeholder="Summary of the diagnostic path taken..."></textarea>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Result Type</label>
                <select class="ts-fs" id="mt-type">
                    <option value="solved">Solved</option><option value="escalation">Escalate</option><option value="hardware">Hardware</option>
                </select>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Device</label>
                <select class="ts-fs" id="mt-device">
                    <option value="all">All Devices</option>
                </select>
            </div>
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Solution Text *</label>
            <textarea class="ts-ft" id="mt-solution" placeholder="What was the solution or recommendation..."></textarea>
        </div>
        <div class="ts-actions" style="justify-content:flex-end;">
            <button class="ts-btn ts-btn-secondary" onclick="closeModal('t-modal')">Cancel</button>
            <button class="ts-btn ts-btn-primary" onclick="saveTerminal()">Add Result</button>
        </div>
    </div>
</div>

<script>
var questions = [];
var steps = [];
var terminals = [];
var nextId = 1;
var msGuideTexts = [];

// === POPULATE DEVICE DROPDOWNS ===
function populateDeviceDropdowns() {
    var devices = document.getElementById('sub-devices').value.split(',').map(function(d){return d.trim();}).filter(Boolean);
    ['mq-device','ms-device','mt-device'].forEach(function(id) {
        var sel = document.getElementById(id);
        if (!sel) return;
        sel.innerHTML = '<option value="all">All Devices</option>';
        devices.forEach(function(d) {
            sel.innerHTML += '<option value="'+d+'">'+d.charAt(0).toUpperCase()+d.slice(1)+'</option>';
        });
    });
}
document.getElementById('sub-devices').addEventListener('input', populateDeviceDropdowns);

// === MODALS ===
function openAddQuestion() {
    populateDeviceDropdowns();
    document.getElementById('mq-text').value = '';
    document.getElementById('mq-desc').value = '';
    document.getElementById('mq-why').value = '';
    document.getElementById('mq-risk').value = 'safe';
    document.getElementById('mq-device').value = 'all';
    document.getElementById('q-modal').classList.add('open');
    lucide.createIcons();
}

function openAddStep() {
    if (questions.length === 0) { showToast('Add at least one question first', 'error'); return; }
    populateDeviceDropdowns();
    document.getElementById('ms-title').value = '';
    document.getElementById('ms-desc').value = '';
    document.getElementById('ms-expected').value = '';
    document.getElementById('ms-tools').value = '';
    document.getElementById('ms-risk').value = 'safe';
    document.getElementById('ms-when').value = 'always';
    msGuideTexts = [];
    renderMsGuides();
    // Build question dropdown
    var sel = document.getElementById('ms-question');
    sel.innerHTML = questions.map(function(q,i) { return '<option value="'+q.id+'">'+esc(q.text)+'</option>'; }).join('');
    document.getElementById('s-modal').classList.add('open');
    lucide.createIcons();
}

function openAddTerminal() {
    populateDeviceDropdowns();
    document.getElementById('mt-title').value = '';
    document.getElementById('mt-desc').value = '';
    document.getElementById('mt-type').value = 'solved';
    document.getElementById('mt-solution').value = '';
    document.getElementById('mt-device').value = 'all';
    document.getElementById('t-modal').classList.add('open');
    lucide.createIcons();
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// === GUIDE GROUPS ===
function addMsGuide() { msGuideTexts.push(''); renderMsGuides(); }
function removeMsGuide(idx) { msGuideTexts.splice(idx, 1); renderMsGuides(); }
function renderMsGuides() {
    var el = document.getElementById('ms-guides-list');
    if (!msGuideTexts.length) { el.innerHTML = '<div style="text-align:center;padding:10px;color:#94a3b8;font-size:12px;">No guide steps yet.</div>'; return; }
    var html = '';
    msGuideTexts.forEach(function(g, i) {
        html += '<div class="ts-guide-group">';
        html += '<div class="ts-guide-head"><span class="ts-guide-num">'+(i+1)+'</span><label style="font-size:12px;font-weight:600;">Step '+(i+1)+'</label>';
        html += '<button class="ts-guide-remove" onclick="removeMsGuide('+i+')">&times;</button></div>';
        html += '<textarea class="ts-ft" style="min-height:40px;font-size:12px;" placeholder="What to do in this step..." oninput="msGuideTexts['+i+']=this.value">'+esc(g)+'</textarea>';
        html += '</div>';
    });
    el.innerHTML = html;
}

// === SAVE ===
function saveQuestion() {
    var text = document.getElementById('mq-text').value.trim();
    if (!text) { showToast('Enter a question', 'error'); return; }
    questions.push({
        id: nextId++,
        text: text,
        desc: document.getElementById('mq-desc').value.trim(),
        risk: document.getElementById('mq-risk').value,
        device: document.getElementById('mq-device').value || 'all',
        why: document.getElementById('mq-why').value.trim(),
    });
    closeModal('q-modal');
    renderAll();
}

function saveStep() {
    var title = document.getElementById('ms-title').value.trim();
    if (!title) { showToast('Enter a step title', 'error'); return; }
    var when = document.getElementById('ms-when').value;
    var linkedQ = when !== 'always' ? parseInt(document.getElementById('ms-question').value) : null;
    steps.push({
        id: nextId++,
        title: title,
        desc: document.getElementById('ms-desc').value.trim(),
        risk: document.getElementById('ms-risk').value,
        device: document.getElementById('ms-device').value || 'all',
        guides: msGuideTexts.filter(Boolean).slice(),
        expected: document.getElementById('ms-expected').value.trim(),
        tools: document.getElementById('ms-tools').value.trim(),
        when: when,
        linkedQ: linkedQ,
    });
    closeModal('s-modal');
    renderAll();
}

function saveTerminal() {
    var title = document.getElementById('mt-title').value.trim();
    if (!title) { showToast('Enter a result title', 'error'); return; }
    var solution = document.getElementById('mt-solution').value.trim();
    if (!solution) { showToast('Enter solution text', 'error'); return; }
    terminals.push({
        id: nextId++,
        title: title,
        desc: document.getElementById('mt-desc').value.trim(),
        type: document.getElementById('mt-type').value,
        device: document.getElementById('mt-device').value || 'all',
        solution: solution,
    });
    closeModal('t-modal');
    renderAll();
}

// === RENDER ===
function renderAll() {
    renderQuestions();
    renderStepsTable();
    renderTerminals();
}

function renderQuestions() {
    var html = '';
    questions.forEach(function(q, i) {
        html += '<div class="ts-item">';
        html += '<span class="ts-item-num q">'+(i+1)+'</span>';
        html += '<div class="ts-item-body">';
        html += '<div class="ts-item-title">'+esc(q.text)+'</div>';
        if (q.desc) html += '<div class="ts-item-desc">'+esc(q.desc)+'</div>';
        html += '<div class="ts-item-meta">';
        html += '<span class="ts-item-tag risk-'+q.risk+'">'+q.risk.charAt(0).toUpperCase()+q.risk.slice(1)+'</span>';
        if (q.device !== 'all') html += '<span class="ts-item-tag vis-always">'+q.device+'</span>';
        if (q.why) html += '<span style="font-size:10px;color:#64748b;font-style:italic;">Why: '+esc(q.why).substring(0,60)+'</span>';
        html += '</div></div>';
        html += '<button class="ts-item-del" onclick="removeQuestion('+q.id+')"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
        html += '</div>';
    });
    if (!html) html = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px;">No questions yet. Add your first diagnostic question above.</p>';
    document.getElementById('questions-list').innerHTML = html;
    lucide.createIcons();
}

function renderStepsTable() {
    if (steps.length === 0) {
        document.getElementById('steps-preview').innerHTML = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px;">No steps yet.</p>';
        return;
    }
    var yesSteps = steps.filter(function(s) { return s.when === 'yes_only'; });
    var noSteps = steps.filter(function(s) { return s.when === 'no_only'; });
    var bothSteps = steps.filter(function(s) { return s.when === 'both'; });
    var alwaysSteps = steps.filter(function(s) { return s.when === 'always'; });

    var html = '<table class="ts-table"><thead><tr>';
    html += '<th style="width:33%;">YES only ('+yesSteps.length+')</th>';
    html += '<th style="width:33%;">NO only ('+noSteps.length+')</th>';
    html += '<th style="width:34%;">BOTH ('+bothSteps.length+')</th>';
    html += '</tr></thead><tbody><tr>';

    // YES column
    html += '<td class="col-yes">';
    yesSteps.forEach(function(s) {
        html += '<div class="ts-step-tag yes">When '+getQName(s.linkedQ)+' → YES</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button><br>';
    });
    if (!yesSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td>';

    // NO column
    html += '<td class="col-no">';
    noSteps.forEach(function(s) {
        html += '<div class="ts-step-tag no">When '+getQName(s.linkedQ)+' → NO</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button><br>';
    });
    if (!noSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td>';

    // BOTH column
    html += '<td class="col-both">';
    bothSteps.forEach(function(s) {
        html += '<div class="ts-step-tag both">When '+getQName(s.linkedQ)+' → YES or NO</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button><br>';
    });
    if (!bothSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td></tr></tbody></table>';

    // Always steps
    if (alwaysSteps.length) {
        html += '<div style="margin-top:12px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">';
        html += '<div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:6px;">Always Visible ('+alwaysSteps.length+' steps)</div>';
        alwaysSteps.forEach(function(s) {
            html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;font-size:12px;">';
            html += '<span class="ts-step-tag always">Always</span>';
            html += '<span style="font-weight:600;color:#0f172a;">'+esc(s.title)+'</span>';
            if (s.tools) html += '<span style="font-size:10px;color:#94a3b8;">('+esc(s.tools)+')</span>';
            html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-left:auto;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
            html += '</div>';
        });
        html += '</div>';
    }

    document.getElementById('steps-preview').innerHTML = html;
    lucide.createIcons();
}

function renderTerminals() {
    var html = '';
    terminals.forEach(function(t, i) {
        html += '<div class="ts-item">';
        html += '<span class="ts-item-num t">'+(i+1)+'</span>';
        html += '<div class="ts-item-body">';
        html += '<div class="ts-item-title">'+esc(t.title)+'</div>';
        if (t.desc) html += '<div class="ts-item-desc">'+esc(t.desc)+'</div>';
        html += '<div class="ts-item-meta">';
        html += '<span class="ts-item-tag risk-'+(t.type==='solved'?'safe':t.type==='hardware'?'caution':'danger')+'">'+t.type+'</span>';
        if (t.device !== 'all') html += '<span class="ts-item-tag vis-always">'+t.device+'</span>';
        html += '</div>';
        if (t.solution) html += '<div style="font-size:11px;color:#16a34a;margin-top:4px;font-style:italic;">Solution: '+esc(t.solution).substring(0,100)+'</div>';
        html += '</div>';
        html += '<button class="ts-item-del" onclick="removeTerminal('+t.id+')"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
        html += '</div>';
    });
    if (!html) html = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px;">No terminal results yet.</p>';
    document.getElementById('terminals-list').innerHTML = html;
    lucide.createIcons();
}

function getQName(qId) {
    if (!qId) return '?';
    var q = questions.find(function(x) { return x.id === qId; });
    return q ? esc(q.text.substring(0, 30)) : '?';
}

function removeQuestion(id) {
    questions = questions.filter(function(q) { return q.id !== id; });
    steps = steps.filter(function(s) { return s.linkedQ !== id; });
    renderAll();
}
function removeStep(id) { steps = steps.filter(function(s) { return s.id !== id; }); renderAll(); }
function removeTerminal(id) { terminals = terminals.filter(function(t) { return t.id !== id; }); renderAll(); }

// === SUBMIT ===
async function submitForApproval() {
    var title = document.getElementById('sub-title').value.trim();
    if (!title) { showToast('Enter an issue title', 'error'); return; }
    if (questions.length === 0) { showToast('Add at least one question', 'error'); return; }
    if (steps.length === 0 && terminals.length === 0) { showToast('Add at least one step or result', 'error'); return; }

    var nodes = [];
    // Questions
    questions.forEach(function(q, i) {
        nodes.push({
            _temp_id: 'q'+q.id,
            node_type: 'question',
            question: q.text,
            description: q.desc,
            why_answer: q.why || null,
            risk: q.risk || 'safe',
            device_type: q.device || 'all',
            is_terminal: 0,
            step_order: (i + 1) * 10,
        });
    });

    // Steps
    steps.forEach(function(s, i) {
        var visQTempId = s.linkedQ ? 'q'+s.linkedQ : null;
        nodes.push({
            _temp_id: 's'+s.id,
            node_type: 'step',
            question: s.title,
            description: s.desc,
            risk: s.risk || 'safe',
            device_type: s.device || 'all',
            is_terminal: 0,
            step_order: (questions.length + i + 1) * 10,
            visual_guide: s.guides && s.guides.length ? s.guides.join('\n---\n') : null,
            visual_guide_images: s.guides && s.guides.length ? JSON.stringify(s.guides.map(function(g){return {text:g,image:null};})) : null,
            expected_result: s.expected || null,
            tools_needed: s.tools || null,
            visible_for_question_id: visQTempId,
            visibility_mode: s.when,
        });
    });

    // Terminals
    terminals.forEach(function(t, i) {
        nodes.push({
            _temp_id: 't'+t.id,
            node_type: 'terminal',
            question: t.title,
            description: t.desc,
            device_type: t.device || 'all',
            is_terminal: 1,
            step_order: (questions.length + steps.length + i + 1) * 10,
            result_type: t.type,
            result_solution: t.solution,
        });
    });

    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                title: title,
                submission_type: 'new_issue',
                description: document.getElementById('sub-desc').value.trim(),
                category_id: document.getElementById('sub-category').value,
                severity: document.getElementById('sub-severity').value,
                device_type: document.getElementById('sub-devices').value.trim(),
                nodes_data: nodes,
            }),
        });
        var data = await res.json();
        if (data.error) { showToast(data.error, 'error'); return; }
        showToast('Submitted for approval!', 'success');
        clearForm();
        loadMySubmissions();
    } catch(e) { showToast('Failed: ' + e.message, 'error'); }
}

function clearForm() {
    document.getElementById('sub-title').value = '';
    document.getElementById('sub-desc').value = '';
    document.getElementById('sub-devices').value = '';
    questions = []; steps = []; terminals = []; nextId = 1;
    renderAll();
}

async function loadMySubmissions() {
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php?mine=1');
        var data = await res.json();
        var html = '';
        if (!data.length) {
            html = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:20px;">No submissions yet.</p>';
        } else {
            data.forEach(function(s) {
                var nodes = s.nodes_data ? JSON.parse(s.nodes_data) : [];
                var qCount = nodes.filter(function(n){return (n.node_type||'question')==='question';}).length;
                var sCount = nodes.filter(function(n){return (n.node_type||'question')==='step';}).length;
                var tCount = nodes.filter(function(n){return n.is_terminal;}).length;
                html += '<div class="ts-sub-item">';
                html += '<div><div class="ts-sub-title">'+esc(s.title)+'</div>';
                html += '<div class="ts-sub-meta">'+qCount+'Q + '+sCount+'S + '+tCount+'T &bull; '+formatDate(s.created_at);
                if (s.admin_notes) html += ' &bull; <em>'+esc(s.admin_notes)+'</em>';
                html += '</div></div>';
                html += '<span class="ts-sub-status '+s.status+'">'+s.status.charAt(0).toUpperCase()+s.status.slice(1)+'</span>';
                html += '</div>';
            });
        }
        document.getElementById('my-submissions').innerHTML = html;
    } catch(e) {}
}

function formatDate(d) {
    if (!d) return '';
    var diff = (new Date() - new Date(d)) / 1000;
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}
function esc(s) { return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }
function showToast(msg, type) {
    var t = document.createElement('div'); t.className = 'toast ' + type;
    t.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var c = document.getElementById('toast-container'); if (c) c.appendChild(t);
    setTimeout(function() { t.remove(); }, 4000);
}

loadMySubmissions();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
