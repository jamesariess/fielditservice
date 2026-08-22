<?php
$page_title = 'Submit Troubleshooting';
$active_menu = 'troubleshoot';
require APP_ROOT . '/includes/layout_header.php';
?>

<style>
.ts-submit-page { max-width: 800px; margin: 0 auto; }
.ts-submit-header { margin-bottom: 24px; }
.ts-submit-header h1 { font-size: 24px; font-weight: 800; color: #111827; }
.dark .ts-submit-header h1 { color: #f1f5f9; }
.ts-submit-header p { font-size: 14px; color: #64748b; margin-top: 4px; }

.ts-info-banner {
    display: flex; align-items: flex-start; gap: 12px; padding: 16px 20px;
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
    margin-bottom: 24px; font-size: 13px; color: #1e40af; line-height: 1.6;
}
.ts-info-banner i { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; color: #2563eb; }

.ts-form-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    padding: 24px; margin-bottom: 20px;
}
.dark .ts-form-card { background: #1e293b; border-color: #334155; }
.ts-form-card h3 { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 16px; }
.dark .ts-form-card h3 { color: #f1f5f9; }

.ts-fg { margin-bottom: 16px; }
.ts-fl { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.dark .ts-fl { color: #94a3b8; }
.ts-fi, .ts-fs, .ts-ft {
    width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 10px;
    font-size: 14px; font-family: inherit; transition: border-color 0.2s;
}
.ts-fi:focus, .ts-fs:focus, .ts-ft:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.dark .ts-fi, .dark .ts-fs, .dark .ts-ft { background: #0f172a; border-color: #334155; color: #f1f5f9; }
.ts-ft { min-height: 80px; resize: vertical; }
.ts-fr { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* Step builder */
.ts-step {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 16px; margin-bottom: 12px; position: relative;
}
.dark .ts-step { background: #0f172a; border-color: #1e293b; }
.ts-step-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%; background: #2563eb;
    color: #fff; font-size: 12px; font-weight: 700; margin-bottom: 10px;
}
.ts-step-remove {
    position: absolute; top: 12px; right: 12px; background: none; border: none;
    cursor: pointer; color: #94a3b8; padding: 4px;
}
.ts-step-remove:hover { color: #dc2626; }

.ts-add-step {
    display: flex; align-items: center; gap: 8px; padding: 12px 16px;
    border: 2px dashed #d1d5db; border-radius: 12px; background: transparent;
    cursor: pointer; font-size: 14px; font-weight: 600; color: #64748b;
    width: 100%; justify-content: center; transition: all 0.2s;
}
.ts-add-step:hover { border-color: #2563eb; color: #2563eb; background: #f0f9ff; }

.ts-submit-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }
.ts-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
    border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.2s;
}
.ts-btn-primary { background: #2563eb; color: #fff; }
.ts-btn-primary:hover { background: #1d4ed8; }
.ts-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.ts-btn-secondary:hover { background: #e2e8f0; }

/* My submissions */
.ts-my-subs { margin-top: 32px; }
.ts-my-subs h2 { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 16px; }
.dark .ts-my-subs h2 { color: #f1f5f9; }
.ts-sub-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; margin-bottom: 8px;
}
.dark .ts-sub-item { background: #1e293b; border-color: #334155; }
.ts-sub-title { font-size: 14px; font-weight: 600; color: #111827; }
.dark .ts-sub-title { color: #f1f5f9; }
.ts-sub-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }
.ts-sub-status {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
    border-radius: 20px; font-size: 12px; font-weight: 600; flex-shrink: 0;
}
.ts-sub-status.pending { background: #fef3c7; color: #d97706; }
.ts-sub-status.approved { background: #dcfce7; color: #16a34a; }
.ts-sub-status.rejected { background: #fee2e2; color: #dc2626; }
</style>

<div class="ts-submit-page">
    <div class="ts-submit-header">
        <h1><i data-lucide="file-plus" style="width:24px;height:24px;vertical-align:middle;margin-right:8px;"></i>Submit Troubleshooting Steps</h1>
        <p>Share your troubleshooting knowledge with the team. Submissions are reviewed before going live.</p>
    </div>

    <div class="ts-info-banner">
        <i data-lucide="info"></i>
        <div>
            <strong>How it works:</strong> Fill out the form below with the issue details and troubleshooting steps. 
            An admin will review your submission. Once approved, it will be visible to all technicians in the Troubleshoot section.
            <strong>You can see your own submissions</strong> in "My Submissions" below.
        </div>
    </div>

    <!-- Issue Details -->
    <div class="ts-form-card">
        <h3>Issue Details</h3>
        <div class="ts-fg">
            <label class="ts-fl">Issue Title *</label>
            <input type="text" class="ts-fi" id="sub-title" placeholder="e.g., Projector Not Turning On">
        </div>
        <div class="ts-fr">
            <div class="ts-fg">
                <label class="ts-fl">Category</label>
                <select class="ts-fs" id="sub-category">
                    <option value="1">Display</option>
                    <option value="2">Power</option>
                    <option value="3">Sound</option>
                    <option value="4">Network</option>
                    <option value="5">Printer</option>
                    <option value="6">CCTV</option>
                    <option value="7">Software</option>
                </select>
            </div>
            <div class="ts-fg">
                <label class="ts-fl">Severity</label>
                <select class="ts-fs" id="sub-severity">
                    <option value="high">High (Critical)</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Description</label>
            <textarea class="ts-ft" id="sub-desc" placeholder="Brief description of the problem and when it occurs..."></textarea>
        </div>
        <div class="ts-fg">
            <label class="ts-fl">Device Types (comma separated)</label>
            <input type="text" class="ts-fi" id="sub-devices" placeholder="e.g., Desktop, Laptop, Server">
        </div>
    </div>

    <!-- Troubleshooting Steps -->
    <div class="ts-form-card">
        <h3>Troubleshooting Steps (Decision Tree)</h3>
        <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
            Add each YES/NO question the technician should answer. The first step should be the initial check.
        </p>
        <div id="steps-container"></div>
        <button class="ts-add-step" onclick="addStep()">
            <i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Add Another Step
        </button>
    </div>

    <!-- Actions -->
    <div class="ts-submit-actions">
        <button class="ts-btn ts-btn-secondary" onclick="clearForm()">Clear Form</button>
        <button class="ts-btn ts-btn-primary" onclick="submitForApproval()">
            <i data-lucide="send" style="width:16px;height:16px;"></i> Submit for Approval
        </button>
    </div>

    <!-- My Submissions -->
    <div class="ts-my-subs">
        <h2>My Submissions</h2>
        <div id="my-submissions"></div>
    </div>
</div>

<script>
let stepCount = 0;

function addStep() {
    stepCount++;
    var html = '<div class="ts-step" id="step-'+stepCount+'">';
    html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">';
    html += '<span class="ts-step-num">'+stepCount+'</span>';
    html += '<strong style="font-size:14px;color:#374151;">Step '+stepCount+'</strong>';
    html += '<button class="ts-step-remove" onclick="removeStep('+stepCount+')" title="Remove step"><i data-lucide="x" style="width:16px;height:16px;"></i></button>';
    html += '</div>';
    html += '<div class="ts-fg"><label class="ts-fl">Question *</label>';
    html += '<input type="text" class="ts-fi" id="sq-'+stepCount+'" placeholder="e.g., Is the power cable connected?"></div>';
    html += '<div class="ts-fg"><label class="ts-fl">Instructions</label>';
    html += '<textarea class="ts-ft" id="si-'+stepCount+'" placeholder="What the technician should check or do..." style="min-height:60px;"></textarea></div>';
    html += '<div class="ts-fr">';
    html += '<div class="ts-fg"><label class="ts-fl">Risk Level</label>';
    html += '<select class="ts-fs" id="sr-'+stepCount+'"><option value="safe">🟢 Safe</option><option value="caution">🟡 Caution</option><option value="danger">🔴 High Risk</option></select></div>';
    html += '<div class="ts-fg"><label class="ts-fl">Result Type</label>';
    html += '<select class="ts-fs" id="st-'+stepCount+'"><option value="">Continue (not terminal)</option><option value="solved">✅ Solved</option><option value="escalation">⚠️ Escalate</option><option value="hardware">🔧 Hardware Issue</option></select></div>';
    html += '</div>';
    html += '<div class="ts-fg"><label class="ts-fl">Solution (if terminal)</label>';
    html += '<textarea class="ts-ft" id="ss-'+stepCount+'" placeholder="What was the solution..." style="min-height:50px;"></textarea></div>';
    html += '</div>';
    document.getElementById('steps-container').insertAdjacentHTML('beforeend', html);
    lucide.createIcons();
}

function removeStep(n) {
    var el = document.getElementById('step-'+n);
    if (el) el.remove();
    renumberSteps();
}

function renumberSteps() {
    var steps = document.querySelectorAll('#steps-container .ts-step');
    steps.forEach(function(s, i) {
        s.querySelector('.ts-step-num').textContent = i + 1;
        s.querySelector('strong').textContent = 'Step ' + (i + 1);
    });
}

async function submitForApproval() {
    var title = document.getElementById('sub-title').value.trim();
    if (!title) { showToast('Please enter an issue title', 'error'); return; }

    var nodes = [];
    document.querySelectorAll('#steps-container .ts-step').forEach(function(s) {
        var id = s.id.replace('step-', '');
        var q = document.getElementById('sq-'+id)?.value.trim();
        if (q) {
            nodes.push({
                question: q,
                description: document.getElementById('si-'+id)?.value.trim() || '',
                risk: document.getElementById('sr-'+id)?.value || 'safe',
                is_terminal: document.getElementById('st-'+id)?.value ? 1 : 0,
                result_type: document.getElementById('st-'+id)?.value || null,
                result_solution: document.getElementById('ss-'+id)?.value.trim() || null,
            });
        }
    });

    if (nodes.length === 0) { showToast('Add at least one troubleshooting step', 'error'); return; }

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
        showToast('Submitted! Admin will review your troubleshooting steps.', 'success');
        clearForm();
        loadMySubmissions();
    } catch(e) { showToast('Failed: ' + e.message, 'error'); }
}

function clearForm() {
    document.getElementById('sub-title').value = '';
    document.getElementById('sub-desc').value = '';
    document.getElementById('sub-devices').value = '';
    document.getElementById('steps-container').innerHTML = '';
    stepCount = 0;
    addStep();
}

async function loadMySubmissions() {
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php?mine=1');
        var data = await res.json();
        var html = '';
        if (data.length === 0) {
            html = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:24px;">No submissions yet. Submit your first troubleshooting guide above!</p>';
        } else {
            data.forEach(function(s) {
                var nodes = s.nodes_data ? JSON.parse(s.nodes_data) : [];
                html += '<div class="ts-sub-item">';
                html += '<div><div class="ts-sub-title">'+escapeHtml(s.title)+'</div>';
                html += '<div class="ts-sub-meta">'+nodes.length+' steps • '+formatDate(s.created_at);
                if (s.admin_notes) html += ' • <em>'+escapeHtml(s.admin_notes)+'</em>';
                html += '</div></div>';
                html += '<span class="ts-sub-status '+s.status+'">'+s.status.charAt(0).toUpperCase()+s.status.slice(1)+'</span>';
                html += '</div>';
            });
        }
        document.getElementById('my-submissions').innerHTML = html;
    } catch(e) { console.error(e); }
}

function formatDate(d) {
    if (!d) return '';
    var dt = new Date(d);
    var now = new Date();
    var diff = (now - dt) / 1000;
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

function escapeHtml(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showToast(msg, type) {
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var container = document.getElementById('toast-container');
    if (container) container.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}

// Init
addStep();
loadMySubmissions();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
