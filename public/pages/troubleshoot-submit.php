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
    font-size: 13px; font-family: inherit;
}
.ts-fi:focus, .ts-fs:focus, .ts-ft:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.dark .ts-fi, .dark .ts-fs, .dark .ts-ft { background: #0f172a; border-color: #334155; color: #f1f5f9; }
.ts-ft { min-height: 50px; resize: vertical; }
.ts-fr { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ts-fh { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

/* Simple list items */
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
.ts-item-body { flex: 1; min-width: 0; }
.ts-item-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.dark .ts-item-title { color: #f1f5f9; }
.ts-item-desc { font-size: 11px; color: #64748b; margin-top: 2px; }
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

/* Table view */
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
.ts-table .col-both { background: rgba(37,99,235,0.04); }
.ts-step-tag {
    display: inline-block; padding: 2px 8px; border-radius: 6px;
    font-size: 11px; font-weight: 600; margin-bottom: 4px;
}
.ts-step-tag.yes { background: #dcfce7; color: #16a34a; }
.ts-step-tag.no { background: #fee2e2; color: #dc2626; }
.ts-step-tag.both { background: #dbeafe; color: #2563eb; }
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
    background: #fff; border-radius: 16px; padding: 24px; width: 90%; max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 80vh; overflow-y: auto;
}
.dark .ts-modal { background: #1e293b; }
.ts-modal h3 { font-size: 16px; font-weight: 700; margin-bottom: 16px; color: #0f172a; }
.dark .ts-modal h3 { color: #f1f5f9; }
.ts-modal-close {
    float: right; background: none; border: none; cursor: pointer;
    color: #94a3b8; padding: 4px;
}
.ts-modal-close:hover { color: #dc2626; }

@media (max-width: 640px) { .ts-fr, .ts-fh { grid-template-columns: 1fr; } }
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
                    <option value="1">Display</option><option value="2">Power</option>
                    <option value="3">Sound</option><option value="4">Network</option>
                    <option value="5">Printer</option><option value="6">CCTV</option>
                    <option value="7">Software</option>
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
                <input type="text" class="ts-fi" id="sub-devices" placeholder="e.g., Desktop, Laptop">
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

    <!-- Steps with YES/NO/BOTH table -->
    <div class="ts-card">
        <h3><i data-lucide="wrench" style="width:18px;height:18px;color:#16a34a;"></i> Troubleshooting Steps</h3>
        <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Each step is tied to a question. Choose when it appears: on YES, NO, or BOTH answers.</p>
        <div id="steps-preview"></div>
        <button class="ts-add-btn" onclick="openAddStep()" style="margin-top:8px;">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Step
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

<!-- Add Question Modal -->
<div class="ts-modal-overlay" id="q-modal">
    <div class="ts-modal">
        <button class="ts-modal-close" onclick="closeModal('q-modal')"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        <h3>Add Question</h3>
        <div class="ts-fg">
            <label class="ts-fl">Question *</label>
            <input type="text" class="ts-fi" id="mq-text" placeholder="e.g., Is the power cable connected?">
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Description (optional)</label>
            <textarea class="ts-ft" id="mq-desc" placeholder="What the technician should check..."></textarea>
        </div>
        <div class="ts-actions" style="justify-content:flex-end;">
            <button class="ts-btn ts-btn-secondary" onclick="closeModal('q-modal')">Cancel</button>
            <button class="ts-btn ts-btn-primary" onclick="saveQuestion()">Add Question</button>
        </div>
    </div>
</div>

<!-- Add Step Modal -->
<div class="ts-modal-overlay" id="s-modal">
    <div class="ts-modal">
        <button class="ts-modal-close" onclick="closeModal('s-modal')"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        <h3>Add Step</h3>
        <div class="ts-fg">
            <label class="ts-fl">Step Title *</label>
            <input type="text" class="ts-fi" id="ms-title" placeholder="e.g., Reseat the Graphics Card">
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Instructions</label>
            <textarea class="ts-ft" id="ms-desc" placeholder="What the technician should do..."></textarea>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Visual Guide</label>
                <input type="text" class="ts-fi" id="ms-visual" placeholder="Where to look, what to see">
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Tools Needed</label>
                <input type="text" class="ts-fi" id="ms-tools" placeholder="Screwdriver, multimeter">
            </div>
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Expected Result</label>
                <input type="text" class="ts-fi" id="ms-expected" placeholder="What should happen">
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Risk Level</label>
                <select class="ts-fs" id="ms-risk">
                    <option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option>
                </select>
            </div>
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Show this step when answer is *</label>
            <div style="display:flex;gap:8px;margin-top:4px;">
                <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;padding:6px 12px;border:2px solid #e2e8f0;border-radius:8px;">
                    <input type="radio" name="ms-when" value="always" checked> Always
                </label>
                <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;padding:6px 12px;border:2px solid #dcfce7;border-radius:8px;">
                    <input type="radio" name="ms-when" value="yes_only"> YES only
                </label>
                <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;padding:6px 12px;border:2px solid #fee2e2;border-radius:8px;">
                    <input type="radio" name="ms-when" value="no_only"> NO only
                </label>
                <label style="display:flex;align-items:center;gap:4px;font-size:13px;cursor:pointer;padding:6px 12px;border:2px solid #dbeafe;border-radius:8px;">
                    <input type="radio" name="ms-when" value="both"> BOTH
                </label>
            </div>
        </div>
        <div class="ts-fg" id="ms-question-wrap" style="display:none;">
            <label class="ts-fl">Linked to Question *</label>
            <select class="ts-fs" id="ms-question"></select>
        </div>
        <div class="ts-actions" style="justify-content:flex-end;">
            <button class="ts-btn ts-btn-secondary" onclick="closeModal('s-modal')">Cancel</button>
            <button class="ts-btn ts-btn-primary" onclick="saveStep()">Add Step</button>
        </div>
    </div>
</div>

<script>
var questions = [];
var steps = [];
var nextId = 1;

// === MODALS ===
function openAddQuestion() {
    document.getElementById('mq-text').value = '';
    document.getElementById('mq-desc').value = '';
    document.getElementById('q-modal').classList.add('open');
    lucide.createIcons();
}
function openAddStep() {
    if (questions.length === 0) { showToast('Add at least one question first', 'error'); return; }
    document.getElementById('ms-title').value = '';
    document.getElementById('ms-desc').value = '';
    document.getElementById('ms-visual').value = '';
    document.getElementById('ms-tools').value = '';
    document.getElementById('ms-expected').value = '';
    document.getElementById('ms-risk').value = 'safe';
    document.querySelector('input[name="ms-when"][value="always"]').checked = true;
    // Build question dropdown
    var sel = document.getElementById('ms-question');
    sel.innerHTML = questions.map(function(q) { return '<option value="'+q.id+'">'+esc(q.text)+'</option>'; }).join('');
    document.getElementById('ms-question-wrap').style.display = 'none';
    document.getElementById('s-modal').classList.add('open');
    lucide.createIcons();
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Show/hide question selector based on visibility radio
document.querySelectorAll('input[name="ms-when"]').forEach(function(r) {
    r.addEventListener('change', function() {
        document.getElementById('ms-question-wrap').style.display = 
            this.value === 'always' ? 'none' : '';
    });
});

// === SAVE ===
function saveQuestion() {
    var text = document.getElementById('mq-text').value.trim();
    if (!text) { showToast('Enter a question', 'error'); return; }
    questions.push({ id: nextId++, text: text, desc: document.getElementById('mq-desc').value.trim() });
    closeModal('q-modal');
    renderAll();
}

function saveStep() {
    var title = document.getElementById('ms-title').value.trim();
    if (!title) { showToast('Enter a step title', 'error'); return; }
    var when = document.querySelector('input[name="ms-when"]:checked').value;
    var linkedQ = when !== 'always' ? parseInt(document.getElementById('ms-question').value) : null;
    steps.push({
        id: nextId++, title: title, desc: document.getElementById('ms-desc').value.trim(),
        visual: document.getElementById('ms-visual').value.trim(),
        tools: document.getElementById('ms-tools').value.trim(),
        expected: document.getElementById('ms-expected').value.trim(),
        risk: document.getElementById('ms-risk').value,
        when: when, linkedQ: linkedQ,
    });
    closeModal('s-modal');
    renderAll();
}

// === RENDER ===
function renderAll() {
    renderQuestions();
    renderStepsTable();
}

function renderQuestions() {
    var html = '';
    questions.forEach(function(q, i) {
        html += '<div class="ts-item">';
        html += '<span class="ts-item-num q">'+(i+1)+'</span>';
        html += '<div class="ts-item-body"><div class="ts-item-title">'+esc(q.text)+'</div>';
        if (q.desc) html += '<div class="ts-item-desc">'+esc(q.desc)+'</div>';
        html += '</div>';
        html += '<button class="ts-item-del" onclick="removeQuestion('+q.id+')"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';
        html += '</div>';
    });
    if (!html) html = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px;">No questions yet. Add your first diagnostic question above.</p>';
    document.getElementById('questions-list').innerHTML = html;
    lucide.createIcons();
}

function renderStepsTable() {
    if (steps.length === 0) {
        document.getElementById('steps-preview').innerHTML = '<p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px;">No steps yet. Add troubleshooting steps above.</p>';
        return;
    }
    
    // Group steps by visibility
    var yesSteps = steps.filter(function(s) { return s.when === 'yes_only'; });
    var noSteps = steps.filter(function(s) { return s.when === 'no_only'; });
    var bothSteps = steps.filter(function(s) { return s.when === 'both'; });
    var alwaysSteps = steps.filter(function(s) { return s.when === 'always'; });
    
    var html = '<table class="ts-table">';
    html += '<thead><tr>';
    html += '<th style="width:33%;">✅ YES only ('+yesSteps.length+')</th>';
    html += '<th style="width:33%;">❌ NO only ('+noSteps.length+')</th>';
    html += '<th style="width:34%;">🔄 BOTH ('+bothSteps.length+')</th>';
    html += '</tr></thead><tbody><tr>';
    
    // YES column
    html += '<td class="col-yes">';
    yesSteps.forEach(function(s) {
        var qName = getQuestionName(s.linkedQ);
        html += '<div class="ts-step-tag yes">When '+qName+' → YES</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
        html += '<br>';
    });
    if (!yesSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td>';
    
    // NO column
    html += '<td class="col-no">';
    noSteps.forEach(function(s) {
        var qName = getQuestionName(s.linkedQ);
        html += '<div class="ts-step-tag no">When '+qName+' → NO</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
        html += '<br>';
    });
    if (!noSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td>';
    
    // BOTH column
    html += '<td class="col-both">';
    bothSteps.forEach(function(s) {
        var qName = getQuestionName(s.linkedQ);
        html += '<div class="ts-step-tag both">When '+qName+' → YES or NO</div>';
        html += '<div style="font-weight:600;font-size:12px;color:#0f172a;">'+esc(s.title)+'</div>';
        if (s.desc) html += '<div style="font-size:11px;color:#64748b;margin-top:2px;">'+esc(s.desc).substring(0,80)+'</div>';
        html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-top:4px;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
        html += '<br>';
    });
    if (!bothSteps.length) html += '<div style="font-size:11px;color:#94a3b8;">No steps</div>';
    html += '</td>';
    
    html += '</tr></tbody></table>';
    
    // Always-visible steps
    if (alwaysSteps.length) {
        html += '<div style="margin-top:12px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">';
        html += '<div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:6px;">📋 Always Visible ('+alwaysSteps.length+' steps)</div>';
        alwaysSteps.forEach(function(s) {
            html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 0;font-size:12px;">';
            html += '<span class="ts-step-tag always">Always</span>';
            html += '<span style="font-weight:600;color:#0f172a;">'+esc(s.title)+'</span>';
            html += '<button class="ts-item-del" onclick="removeStep('+s.id+')" style="position:static;margin-left:auto;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
            html += '</div>';
        });
        html += '</div>';
    }
    
    document.getElementById('steps-preview').innerHTML = html;
    lucide.createIcons();
}

function getQuestionName(qId) {
    if (!qId) return '?';
    var q = questions.find(function(x) { return x.id === qId; });
    return q ? esc(q.text.substring(0, 40)) : '?';
}

function removeQuestion(id) {
    questions = questions.filter(function(q) { return q.id !== id; });
    // Remove steps linked to this question
    steps = steps.filter(function(s) { return s.linkedQ !== id; });
    renderAll();
}
function removeStep(id) { steps = steps.filter(function(s) { return s.id !== id; }); renderAll(); }

// === SUBMIT ===
async function submitForApproval() {
    var title = document.getElementById('sub-title').value.trim();
    if (!title) { showToast('Enter an issue title', 'error'); return; }
    if (questions.length === 0) { showToast('Add at least one question', 'error'); return; }
    if (steps.length === 0) { showToast('Add at least one step', 'error'); return; }
    
    // Build nodes array for API
    var nodes = [];
    var qIdMap = {}; // form question ID => order index
    questions.forEach(function(q, i) { qIdMap[q.id] = i; });
    
    // Add questions as nodes
    questions.forEach(function(q, i) {
        nodes.push({
            _temp_id: 'q'+q.id,
            node_type: 'question',
            question: q.text,
            description: q.desc,
            is_terminal: 0,
            step_order: (i + 1) * 10,
            visibility_mode: 'always',
        });
    });
    
    // Add steps as nodes
    steps.forEach(function(s, i) {
        var visQTempId = s.linkedQ ? 'q'+s.linkedQ : null;
        nodes.push({
            _temp_id: 's'+s.id,
            node_type: 'step',
            question: s.title,
            description: s.desc,
            is_terminal: 0,
            step_order: (questions.length + i + 1) * 10,
            visual_guide: s.visual || null,
            expected_result: s.expected || null,
            tools_needed: s.tools || null,
            risk: s.risk,
            visible_for_question_id: visQTempId,
            visibility_mode: s.when,
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
    questions = []; steps = []; nextId = 1;
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
                html += '<div class="ts-sub-item">';
                html += '<div><div class="ts-sub-title">'+esc(s.title)+'</div>';
                html += '<div class="ts-sub-meta">'+qCount+' questions, '+sCount+' steps &bull; '+formatDate(s.created_at);
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
