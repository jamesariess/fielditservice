<?php
$page_title = 'Troubleshooting Manager';
$active_menu = 'admin-troubleshoot';
require APP_ROOT . '/includes/layout_header.php';

// Only admins can access
$roleName = strtolower($_SESSION['role_name'] ?? '');
if (!in_array($roleName, ['admin', 'super admin', 'super_admin'])) {
    header('Location: ' . $urlBase . 'dashboard');
    exit;
}
?>

<style>
.tm-page { max-width: 1200px; margin: 0 auto; }
.tm-header { margin-bottom: 24px; }
.tm-header h1 { font-size: 24px; font-weight: 800; color: #111827; }
.dark .tm-header h1 { color: #f1f5f9; }
.tm-header p { font-size: 14px; color: #64748b; margin-top: 4px; }

/* Tabs */
.tm-tabs { display: flex; gap: 4px; background: #f1f5f9; border-radius: 12px; padding: 4px; margin-bottom: 24px; }
.dark .tm-tabs { background: #1e293b; }
.tm-tab {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; color: #64748b; background: transparent; border: none;
}
.tm-tab:hover { color: #374151; background: rgba(255,255,255,0.5); }
.tm-tab.active { background: #fff; color: #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.dark .tm-tab:hover { color: #e2e8f0; background: rgba(255,255,255,0.05); }
.dark .tm-tab.active { background: #334155; color: #60a5fa; }
.tm-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px;
    font-size: 11px; font-weight: 700; background: #fee2e2; color: #dc2626;
}
.tm-tab-badge.empty { display: none; }

/* Cards */
.tm-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    margin-bottom: 12px; overflow: hidden; transition: all 0.2s;
}
.tm-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.dark .tm-card { background: #1e293b; border-color: #334155; }

.tm-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; cursor: pointer;
}
.tm-card-title { font-size: 15px; font-weight: 700; color: #111827; }
.dark .tm-card-title { color: #f1f5f9; }
.tm-card-meta { font-size: 12px; color: #94a3b8; margin-top: 2px; }

.tm-status {
    display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px;
    border-radius: 20px; font-size: 12px; font-weight: 600;
}
.tm-status.pending { background: #fef3c7; color: #d97706; }
.tm-status.approved { background: #dcfce7; color: #16a34a; }
.tm-status.rejected { background: #fee2e2; color: #dc2626; }

.tm-card-body { padding: 0 20px 16px; display: none; }
.tm-card-body.open { display: block; }

.tm-node {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 8px; font-size: 13px;
}
.dark .tm-node { background: #0f172a; border-color: #1e293b; }
.tm-node-q { font-weight: 600; color: #1e293b; margin-bottom: 4px; }
.dark .tm-node-q { color: #e2e8f0; }
.tm-node-d { color: #64748b; font-size: 12px; }
.tm-node-risk {
    display: inline-block; padding: 2px 8px; border-radius: 10px;
    font-size: 11px; font-weight: 600; margin-top: 4px;
}
.tm-node-risk.safe { background: #dcfce7; color: #16a34a; }
.tm-node-risk.caution { background: #fef3c7; color: #d97706; }
.tm-node-risk.danger { background: #fee2e2; color: #dc2626; }

/* Actions */
.tm-actions { display: flex; gap: 8px; margin-top: 12px; }
.tm-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
    border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.2s;
}
.tm-btn-approve { background: #16a34a; color: #fff; }
.tm-btn-approve:hover { background: #15803d; }
.tm-btn-reject { background: #dc2626; color: #fff; }
.tm-btn-reject:hover { background: #b91c1c; }
.tm-btn-view { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.tm-btn-view:hover { background: #e2e8f0; }
.dark .tm-btn-view { background: #334155; border-color: #475569; color: #94a3b8; }

/* Notes input */
.tm-notes {
    width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb;
    border-radius: 8px; font-size: 13px; margin-top: 8px; resize: vertical;
    min-height: 60px; font-family: inherit;
}
.tm-notes:focus { outline: none; border-color: #2563eb; }

/* Issue tree view */
.tm-issue-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
.tm-issue-item {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 16px; transition: all 0.2s;
}
.tm-issue-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.dark .tm-issue-item { background: #1e293b; border-color: #334155; }
.tm-issue-name { font-size: 15px; font-weight: 700; color: #111827; }
.dark .tm-issue-name { color: #f1f5f9; }
.tm-issue-slug { font-size: 12px; color: #94a3b8; font-family: monospace; }
.tm-issue-steps { font-size: 12px; color: #64748b; margin-top: 6px; }

/* Empty state */
.tm-empty { text-align: center; padding: 48px 24px; color: #94a3b8; }
.tm-empty i { width: 48px; height: 48px; margin-bottom: 12px; color: #cbd5e1; }
.tm-empty p { font-size: 14px; }

/* Modal */
.tm-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000;
    display: none; align-items: center; justify-content: center;
}
.tm-modal-overlay.open { display: flex; }
.tm-modal {
    background: #fff; border-radius: 16px; width: 90%; max-width: 600px;
    max-height: 80vh; overflow-y: auto; padding: 24px;
}
.dark .tm-modal { background: #1e293b; }
.tm-modal h3 { font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #111827; }
.dark .tm-modal h3 { color: #f1f5f9; }
.tm-form-group { margin-bottom: 14px; }
.tm-form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.dark .tm-form-label { color: #94a3b8; }
.tm-form-input, .tm-form-select, .tm-form-textarea {
    width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px;
    font-size: 14px; font-family: inherit; background: #fff; color: #111827;
}
.dark .tm-form-input, .dark .tm-form-select, .dark .tm-form-textarea {
    background: #0f172a; border-color: #334155; color: #f1f5f9;
}
.tm-form-textarea { min-height: 80px; resize: vertical; }
.tm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.tm-modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; }
</style>

<div class="tm-page">
    <div class="tm-header">
        <h1><i data-lucide="settings-2" style="width:24px;height:24px;vertical-align:middle;margin-right:8px;"></i>Troubleshooting Manager</h1>
        <p>Manage troubleshooting issues, review submissions, and approve content before it goes live.</p>
    </div>

    <!-- Tabs -->
    <div class="tm-tabs" id="tm-tabs">
        <button class="tm-tab active" data-tab="submissions" onclick="switchTab('submissions')">
            <i data-lucide="inbox" style="width:16px;height:16px;"></i> Submissions
            <span class="tm-tab-badge" id="pending-count">0</span>
        </button>
        <button class="tm-tab" data-tab="issues" onclick="switchTab('issues')">
            <i data-lucide="list" style="width:16px;height:16px;"></i> All Issues
        </button>
        <button class="tm-tab" data-tab="add" onclick="switchTab('add')">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add New
        </button>
        <button class="tm-tab" data-tab="tree" onclick="switchTab('tree')">
            <i data-lucide="git-branch" style="width:16px;height:16px;"></i> Tree Editor
        </button>
    </div>

    <!-- Submissions Tab -->
    <div id="tab-submissions" class="tm-tab-content">
        <div id="submissions-list"></div>
    </div>

    <!-- All Issues Tab -->
    <div id="tab-issues" class="tm-tab-content" style="display:none;">
        <div id="issues-list" class="tm-issue-grid"></div>
    </div>

    <!-- Add New Tab -->
    <div id="tab-add" class="tm-tab-content" style="display:none;">
        <div class="tm-card">
            <div class="tm-card-header">
                <div>
                    <div class="tm-card-title">Submit New Troubleshooting Issue</div>
                    <div class="tm-card-meta">Your submission will be reviewed by an admin before going live.</div>
                </div>
            </div>
            <div class="tm-card-body open">
                <div class="tm-form-group">
                    <label class="tm-form-label">Issue Title *</label>
                    <input type="text" class="tm-form-input" id="new-title" placeholder="e.g., Projector Not Turning On">
                </div>
                <div class="tm-form-row">
                    <div class="tm-form-group">
                        <label class="tm-form-label">Category</label>
                        <select class="tm-form-select" id="new-category">
                            <option value="1">Display</option>
                            <option value="2">Power</option>
                            <option value="3">Sound</option>
                            <option value="4">Network</option>
                            <option value="5">Printer</option>
                            <option value="6">CCTV</option>
                            <option value="7">Software</option>
                        </select>
                    </div>
                    <div class="tm-form-group">
                        <label class="tm-form-label">Severity</label>
                        <select class="tm-form-select" id="new-severity">
                            <option value="high">High</option>
                            <option value="medium" selected>Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
                <div class="tm-form-group">
                    <label class="tm-form-label">Description</label>
                    <textarea class="tm-form-textarea" id="new-desc" placeholder="Brief description of the issue..."></textarea>
                </div>
                <div class="tm-form-group">
                    <label class="tm-form-label">Applicable Device Types</label>
                    <input type="text" class="tm-form-input" id="new-devices" placeholder="e.g., Desktop, Laptop, Server">
                </div>
                
                <h4 style="font-size:14px;font-weight:700;color:#374151;margin:20px 0 12px;">Troubleshooting Steps (Decision Tree)</h4>
                <p style="font-size:12px;color:#94a3b8;margin-bottom:12px;">Add the YES/NO diagnostic steps. Start with the first question.</p>
                
                <div id="new-nodes"></div>
                <button class="tm-btn tm-btn-view" onclick="addNodeField()" style="margin-top:8px;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Step
                </button>
                
                <div class="tm-modal-actions">
                    <button class="tm-btn tm-btn-view" onclick="resetNewForm()">Clear</button>
                    <button class="tm-btn tm-btn-approve" onclick="submitNewIssue()">Submit for Review</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tree Editor Tab -->
    <div id="tab-tree" class="tm-tab-content" style="display:none;">
        <div style="margin-bottom:16px;">
            <label class="tm-form-label">Select Issue to Edit</label>
            <div style="display:flex;gap:8px;">
                <select class="tm-form-select" id="tree-issue-select" style="flex:1;" onchange="loadTreeNodes(this.value)">
                    <option value="">Choose an issue...</option>
                    <option value="1">No Display</option>
                    <option value="2">No Power</option>
                    <option value="3">No Sound</option>
                    <option value="4">No Internet</option>
                    <option value="5">WiFi Not Connecting</option>
                    <option value="6">Printer Offline</option>
                    <option value="7">Camera Offline</option>
                    <option value="8">Blue Screen</option>
                    <option value="9">Slow Performance</option>
                    <option value="10">Network Slow</option>
                    <option value="11">Random Shutdowns</option>
                    <option value="12">Overheating</option>
                    <option value="13">Application Crash</option>
                    <option value="14">Paper Jam</option>
                    <option value="15">Windows Update Fails</option>
                    <option value="16">No Recording</option>
                    <option value="17">DNS Issues</option>
                    <option value="18">Flickering Display</option>
                    <option value="19">BIOS Issues</option>
                    <option value="20">No Display and Power</option>
                    <option value="21">Projector Not Turning On</option>
                </select>
                <button class="tm-btn tm-btn-approve" onclick="addNewNodeToTree()" style="white-space:nowrap;"><i data-lucide="plus" style="width:14px;height:14px;"></i> Add Node</button>
            </div>
        </div>
        <div id="tree-editor-content">
            <div class="tm-empty"><i data-lucide="git-branch"></i><p>Select an issue above to edit its decision tree</p></div>
        </div>
    </div>
</div>

<!-- Approve/Reject Modal -->
<div class="tm-modal-overlay" id="review-modal">
    <div class="tm-modal">
        <h3 id="review-title">Review Submission</h3>
        <div id="review-content"></div>
        <div class="tm-form-group">
            <label class="tm-form-label">Admin Notes (optional)</label>
            <textarea class="tm-form-textarea tm-notes" id="review-notes" placeholder="Reason for approval/rejection..."></textarea>
        </div>
        <div class="tm-modal-actions">
            <button class="tm-btn tm-btn-view" onclick="closeModal()">Cancel</button>
            <button class="tm-btn tm-btn-reject" onclick="rejectSubmission()">Reject</button>
            <button class="tm-btn tm-btn-approve" onclick="approveSubmission()">Approve & Publish</button>
        </div>
    </div>
</div>

<script>
let currentTab = 'submissions';
let currentReviewId = null;
let nodeCount = 0;

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tm-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('[data-tab="'+tab+'"]').classList.add('active');
    document.querySelectorAll('.tm-tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-'+tab).style.display = '';
    
    if (tab === 'submissions') loadSubmissions();
    if (tab === 'issues') loadIssues();
    if (tab === 'add' && nodeCount === 0) addNodeField();
    if (tab === 'tree') { /* tree loads on selection */ }
}

// Load submissions
async function loadSubmissions() {
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php?all=1');
        var data = await res.json();
        
        var pending = data.filter(s => s.status === 'pending').length;
        var badge = document.getElementById('pending-count');
        badge.textContent = pending;
        badge.className = 'tm-tab-badge' + (pending === 0 ? ' empty' : '');
        
        var html = '';
        if (data.length === 0) {
            html = '<div class="tm-empty"><i data-lucide="inbox"></i><p>No submissions yet</p></div>';
        } else {
            data.forEach(function(s) {
                var nodes = s.nodes_data ? JSON.parse(s.nodes_data) : [];
                html += '<div class="tm-card" id="sub-'+s.id+'">';
                html += '<div class="tm-card-header" onclick="toggleCard('+s.id+')">';
                html += '<div><div class="tm-card-title">'+escapeHtml(s.title)+'</div>';
                html += '<div class="tm-card-meta">by '+escapeHtml(s.submitter_name)+' • '+s.submission_type+' • '+formatDate(s.created_at)+'</div></div>';
                html += '<span class="tm-status '+s.status+'">'+s.status.charAt(0).toUpperCase()+s.status.slice(1)+'</span>';
                html += '</div>';
                html += '<div class="tm-card-body" id="body-'+s.id+'">';
                if (s.description) html += '<p style="font-size:13px;color:#64748b;margin-bottom:12px;">'+escapeHtml(s.description)+'</p>';
                if (s.device_type) html += '<p style="font-size:12px;color:#94a3b8;">Devices: '+escapeHtml(s.device_type)+'</p>';
                if (nodes.length > 0) {
                    html += '<h4 style="font-size:13px;font-weight:700;margin:12px 0 8px;">Decision Tree Steps ('+nodes.length+')</h4>';
                    nodes.forEach(function(n, i) {
                        html += '<div class="tm-node">';
                        html += '<div class="tm-node-q">Step '+(i+1)+': '+escapeHtml(n.question)+'</div>';
                        if (n.description) html += '<div class="tm-node-d">'+escapeHtml(n.description)+'</div>';
                        html += '<span class="tm-node-risk '+(n.risk||'safe')+'">'+(n.risk||'safe')+'</span>';
                        html += '</div>';
                    });
                }
                if (s.admin_notes) {
                    html += '<div style="margin-top:8px;padding:8px 12px;background:#f8fafc;border-radius:8px;font-size:12px;color:#64748b;"><strong>Admin notes:</strong> '+escapeHtml(s.admin_notes)+'</div>';
                }
                if (s.status === 'pending') {
                    html += '<div class="tm-actions">';
                    html += '<button class="tm-btn tm-btn-approve" onclick="openReview('+s.id+',\'approve\')"><i data-lucide="check" style="width:14px;height:14px;"></i> Approve</button>';
                    html += '<button class="tm-btn tm-btn-reject" onclick="openReview('+s.id+',\'reject\')"><i data-lucide="x" style="width:14px;height:14px;"></i> Reject</button>';
                    html += '</div>';
                }
                html += '</div></div>';
            });
        }
        document.getElementById('submissions-list').innerHTML = html;
        lucide.createIcons();
    } catch(e) {
        console.error('Failed to load submissions:', e);
    }
}

// Load all issues
async function loadIssues() {
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/decision.php?issue=no-display');
        // Just get the issue list from troubleshoot page data
        var html = '';
        var issues = [
            {title:'No Display',slug:'no-display',nodes:19,cat:'Display'},
            {title:'No Power',slug:'no-power',nodes:15,cat:'Power'},
            {title:'No Sound',slug:'no-sound',nodes:16,cat:'Sound'},
            {title:'No Internet',slug:'no-internet',nodes:17,cat:'Network'},
            {title:'WiFi Not Connecting',slug:'wifi-not-connecting',nodes:11,cat:'Network'},
            {title:'Printer Offline',slug:'printer-offline',nodes:15,cat:'Printer'},
            {title:'Camera Offline',slug:'camera-offline',nodes:11,cat:'CCTV'},
            {title:'Blue Screen',slug:'bsod',nodes:16,cat:'Software'},
            {title:'Slow Performance',slug:'slow-performance',nodes:22,cat:'Software'},
            {title:'Network Slow',slug:'network-slow',nodes:10,cat:'Network'},
            {title:'Random Shutdowns',slug:'random-shutdowns',nodes:10,cat:'Power'},
            {title:'Overheating',slug:'overheating',nodes:8,cat:'Power'},
            {title:'Application Crash',slug:'application-crash',nodes:12,cat:'Software'},
            {title:'Paper Jam',slug:'paper-jam',nodes:15,cat:'Printer'},
            {title:'Windows Update Fails',slug:'windows-update-fails',nodes:8,cat:'Software'},
            {title:'No Recording',slug:'no-recording',nodes:6,cat:'CCTV'},
            {title:'DNS Issues',slug:'dns-issues',nodes:7,cat:'Network'},
            {title:'Flickering Display',slug:'flickering-display',nodes:9,cat:'Display'},
            {title:'BIOS Issues',slug:'bios-issues',nodes:5,cat:'Software'},
            {title:'No Display and Power',slug:'no-display-and-no-power',nodes:10,cat:'Power'},
        ];
        issues.forEach(function(i) {
            html += '<div class="tm-issue-item">';
            html += '<div class="tm-issue-name">'+i.title+'</div>';
            html += '<div class="tm-issue-slug">'+i.slug+'</div>';
            html += '<div class="tm-issue-steps">'+i.nodes+' decision steps • '+i.cat+'</div>';
            html += '</div>';
        });
        document.getElementById('issues-list').innerHTML = html;
    } catch(e) {
        console.error('Failed to load issues:', e);
    }
}

// Node field management
function addNodeField() {
    nodeCount++;
    var html = '<div class="tm-node" id="node-'+nodeCount+'">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">';
    html += '<strong style="font-size:13px;color:#374151;">Step '+nodeCount+'</strong>';
    html += '<button class="tm-btn tm-btn-reject" style="padding:4px 8px;font-size:11px;" onclick="removeNode('+nodeCount+')">Remove</button>';
    html += '</div>';
    html += '<div class="tm-form-group"><input class="tm-form-input" id="nq-'+nodeCount+'" placeholder="Question to ask the technician..."></div>';
    html += '<div class="tm-form-group"><textarea class="tm-form-textarea" id="nd-'+nodeCount+'" placeholder="Instructions/description..." style="min-height:50px;"></textarea></div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">Risk Level</label><select class="tm-form-select" id="nr-'+nodeCount+'"><option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option></select></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Result (if terminal)</label><select class="tm-form-select" id="nt-'+nodeCount+'"><option value="">Not terminal</option><option value="solved">Solved</option><option value="escalation">Escalate</option><option value="hardware">Hardware Issue</option></select></div>';
    html += '</div>';
    html += '<div class="tm-form-group"><textarea class="tm-form-textarea" id="ns-'+nodeCount+'" placeholder="Solution text (if terminal)..." style="min-height:40px;"></textarea></div>';
    html += '</div>';
    document.getElementById('new-nodes').insertAdjacentHTML('beforeend', html);
}

function removeNode(n) {
    var el = document.getElementById('node-'+n);
    if (el) el.remove();
}

function resetNewForm() {
    document.getElementById('new-title').value = '';
    document.getElementById('new-desc').value = '';
    document.getElementById('new-devices').value = '';
    document.getElementById('new-nodes').innerHTML = '';
    nodeCount = 0;
    addNodeField();
}

// Submit new issue
async function submitNewIssue() {
    var title = document.getElementById('new-title').value.trim();
    if (!title) { showToast('Please enter an issue title', 'error'); return; }
    
    var nodes = [];
    document.querySelectorAll('#new-nodes .tm-node').forEach(function(el) {
        var id = el.id.replace('node-', '');
        var q = document.getElementById('nq-'+id)?.value.trim();
        if (q) {
            nodes.push({
                question: q,
                description: document.getElementById('nd-'+id)?.value.trim() || '',
                risk: document.getElementById('nr-'+id)?.value || 'safe',
                is_terminal: document.getElementById('nt-'+id)?.value ? 1 : 0,
                result_type: document.getElementById('nt-'+id)?.value || null,
                result_solution: document.getElementById('ns-'+id)?.value.trim() || null,
            });
        }
    });
    
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                title: title,
                submission_type: 'new_issue',
                description: document.getElementById('new-desc').value.trim(),
                category_id: document.getElementById('new-category').value,
                severity: document.getElementById('new-severity').value,
                device_type: document.getElementById('new-devices').value.trim(),
                nodes_data: nodes,
            }),
        });
        var data = await res.json();
        if (data.error) { showToast(data.error, 'error'); return; }
        showToast('Submission created! Waiting for admin approval.', 'success');
        resetNewForm();
        switchTab('submissions');
    } catch(e) {
        showToast('Failed to submit: ' + e.message, 'error');
    }
}

// Review modal
function openReview(id, action) {
    currentReviewId = id;
    document.getElementById('review-modal').classList.add('open');
    document.getElementById('review-notes').value = '';
    document.getElementById('review-title').textContent = action === 'approve' ? 'Approve Submission' : 'Reject Submission';
}

function closeModal() {
    document.getElementById('review-modal').classList.remove('open');
    currentReviewId = null;
}

async function approveSubmission() {
    if (!currentReviewId) return;
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: currentReviewId, action: 'approve', admin_notes: document.getElementById('review-notes').value.trim() }),
        });
        var data = await res.json();
        showToast('Submission approved and published!', 'success');
        closeModal();
        loadSubmissions();
    } catch(e) { showToast('Error: ' + e.message, 'error'); }
}

async function rejectSubmission() {
    if (!currentReviewId) return;
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/submissions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: currentReviewId, action: 'reject', admin_notes: document.getElementById('review-notes').value.trim() }),
        });
        var data = await res.json();
        showToast('Submission rejected', 'info');
        closeModal();
        loadSubmissions();
    } catch(e) { showToast('Error: ' + e.message, 'error'); }
}

// Utility
function toggleCard(id) {
    var body = document.getElementById('body-'+id);
    if (body) body.classList.toggle('open');
}

function formatDate(d) {
    if (!d) return '';
    var dt = new Date(d);
    var now = new Date();
    var diff = (now - dt) / 1000;
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

function escapeHtml(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type) {
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var container = document.getElementById('toast-container');
    if (container) container.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
}

// ============ TREE EDITOR ============
var treeNodes = [];
var treeIssueId = null;

async function loadTreeNodes(issueId) {
    if (!issueId) { document.getElementById('tree-editor-content').innerHTML = '<div class="tm-empty"><p>Select an issue above</p></div>'; return; }
    treeIssueId = parseInt(issueId);
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/nodes.php?issue_id=' + issueId);
        var data = await res.json();
        if (data.error) { showToast(data.error, 'error'); return; }
        treeNodes = data.nodes || [];
        renderTreeEditor();
    } catch(e) { showToast('Failed to load nodes', 'error'); }
}

function renderTreeEditor() {
    var html = '';
    if (treeNodes.length === 0) {
        html = '<div class="tm-empty"><p>No nodes found. Click "Add Node" to create the first step.</p></div>';
    } else {
        // Build a visual tree
        var rootNodes = treeNodes.filter(function(n) { return !n.parent_id; });
        var otherNodes = treeNodes.filter(function(n) { return n.parent_id; });
        
        html += '<div style="margin-bottom:12px;font-size:13px;color:#64748b;">' + treeNodes.length + ' nodes total. Click a node to edit it.</div>';
        
        // Render root nodes first
        rootNodes.forEach(function(n) { html += renderTreeNode(n, 0); });
        // Then orphaned nodes
        otherNodes.forEach(function(n) { html += renderTreeNode(n, 1); });
    }
    document.getElementById('tree-editor-content').innerHTML = html;
    lucide.createIcons();
}

function renderTreeNode(node, depth) {
    var indent = depth * 20;
    var riskColors = {safe:'#16a34a',caution:'#d97706',danger:'#dc2626'};
    var riskColor = riskColors[node.risk] || '#64748b';
    var html = '<div class="tm-node" style="margin-left:' + indent + 'px;margin-bottom:8px;position:relative;">';
    html += '<div style="display:flex;justify-content:space-between;align-items:flex-start;">';
    html += '<div style="flex:1;">';
    html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">';
    html += '<span style="font-size:11px;font-weight:700;color:#94a3b8;">#' + node.id + '</span>';
    html += '<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:' + riskColor + '20;color:' + riskColor + ';font-weight:600;">' + node.risk + '</span>';
    if (node.is_terminal) html += '<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#dcfce7;color:#16a34a;font-weight:600;">terminal: ' + (node.result_type || 'solved') + '</span>';
    html += '</div>';
    html += '<div style="font-size:14px;font-weight:600;color:#111827;margin-bottom:4px;">' + escapeHtml(node.question) + '</div>';
    if (node.description) html += '<div style="font-size:12px;color:#64748b;margin-bottom:4px;">' + escapeHtml(node.description).substring(0, 100) + '</div>';
    if (node.yes_next) html += '<span style="font-size:11px;color:#16a34a;">YES → #' + node.yes_next + '</span> ';
    if (node.no_next) html += '<span style="font-size:11px;color:#dc2626;">NO → #' + node.no_next + '</span>';
    html += '</div>';
    html += '<div style="display:flex;gap:4px;flex-shrink:0;">';
    html += '<button class="tm-btn tm-btn-view" style="padding:4px 8px;font-size:11px;" onclick="editNode(' + node.id + ')" title="Edit"><i data-lucide="pencil" style="width:12px;height:12px;"></i></button>';
    html += '<button class="tm-btn tm-btn-reject" style="padding:4px 8px;font-size:11px;" onclick="deleteNode(' + node.id + ')" title="Delete"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
    html += '</div>';
    html += '</div></div>';
    return html;
}

function editNode(nodeId) {
    var node = treeNodes.find(function(n) { return n.id === nodeId; });
    if (!node) return;
    
    var modal = document.getElementById('review-modal');
    document.getElementById('review-title').textContent = 'Edit Node #' + node.id;
    
    var html = '<div class="tm-form-group"><label class="tm-form-label">Question *</label>';
    html += '<input class="tm-form-input" id="en-question" value="' + escapeHtml(node.question).replace(/"/g, '&quot;') + '"></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Description</label>';
    html += '<textarea class="tm-form-textarea" id="en-desc">' + escapeHtml(node.description || '') + '</textarea></div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">Risk Level</label>';
    html += '<select class="tm-form-select" id="en-risk"><option value="safe"' + (node.risk === 'safe' ? ' selected' : '') + '>Safe</option><option value="caution"' + (node.risk === 'caution' ? ' selected' : '') + '>Caution</option><option value="danger"' + (node.risk === 'danger' ? ' selected' : '') + '>Danger</option></select></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Terminal?</label>';
    html += '<select class="tm-form-select" id="en-terminal"><option value="0"' + (!node.is_terminal ? ' selected' : '') + '>No</option><option value="1"' + (node.is_terminal ? ' selected' : '') + '>Yes</option></select></div>';
    html += '</div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">Result Type</label>';
    html += '<select class="tm-form-select" id="en-result"><option value=""' + (!node.result_type ? ' selected' : '') + '>None</option><option value="solved"' + (node.result_type === 'solved' ? ' selected' : '') + '>Solved</option><option value="escalation"' + (node.result_type === 'escalation' ? ' selected' : '') + '>Escalate</option><option value="hardware"' + (node.result_type === 'hardware' ? ' selected' : '') + '>Hardware</option></select></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Solution</label>';
    html += '<textarea class="tm-form-textarea" id="en-solution" style="min-height:50px;">' + escapeHtml(node.result_solution || '') + '</textarea></div>';
    html += '</div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">YES → Node ID</label>';
    html += '<input class="tm-form-input" id="en-yes" type="number" value="' + (node.yes_next || '') + '"></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">NO → Node ID</label>';
    html += '<input class="tm-form-input" id="en-no" type="number" value="' + (node.no_next || '') + '"></div>';
    html += '</div>';
    
    document.getElementById('review-content').innerHTML = html;
    document.querySelector('#review-modal .tm-btn-approve').onclick = function() { saveNodeEdit(nodeId); };
    document.querySelector('#review-modal .tm-btn-reject').style.display = 'none';
    modal.classList.add('open');
}

async function saveNodeEdit(nodeId) {
    try {
        var data = {
            id: nodeId,
            question: document.getElementById('en-question').value,
            description: document.getElementById('en-desc').value,
            risk: document.getElementById('en-risk').value,
            is_terminal: parseInt(document.getElementById('en-terminal').value),
            result_type: document.getElementById('en-result').value || null,
            result_solution: document.getElementById('en-solution').value || null,
            yes_next: parseInt(document.getElementById('en-yes').value) || null,
            no_next: parseInt(document.getElementById('en-no').value) || null,
        };
        var res = await fetch(APP_BASE + 'api/troubleshooting/nodes.php', {
            method: 'PUT',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data),
        });
        var result = await res.json();
        if (result.error) { showToast(result.error, 'error'); return; }
        showToast('Node updated!', 'success');
        closeModal();
        loadTreeNodes(treeIssueId);
    } catch(e) { showToast('Failed to save: ' + e.message, 'error'); }
}

async function deleteNode(nodeId) {
    if (!confirm('Delete node #' + nodeId + '? This cannot be undone.')) return;
    try {
        var res = await fetch(APP_BASE + 'api/troubleshooting/nodes.php?id=' + nodeId, { method: 'DELETE' });
        var data = await res.json();
        if (data.error) { showToast(data.error, 'error'); return; }
        showToast('Node deleted', 'success');
        loadTreeNodes(treeIssueId);
    } catch(e) { showToast('Failed: ' + e.message, 'error'); }
}

function addNewNodeToTree() {
    if (!treeIssueId) { showToast('Select an issue first', 'error'); return; }
    var modal = document.getElementById('review-modal');
    document.getElementById('review-title').textContent = 'Add New Node';
    var html = '<div class="tm-form-group"><label class="tm-form-label">Question *</label>';
    html += '<input class="tm-form-input" id="en-question" placeholder="e.g., Is the cable connected?"></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Description</label>';
    html += '<textarea class="tm-form-textarea" id="en-desc" placeholder="Instructions for the technician..."></textarea></div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">Risk Level</label>';
    html += '<select class="tm-form-select" id="en-risk"><option value="safe">Safe</option><option value="caution">Caution</option><option value="danger">Danger</option></select></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Terminal?</label>';
    html += '<select class="tm-form-select" id="en-terminal"><option value="0">No</option><option value="1">Yes</option></select></div>';
    html += '</div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">Result Type</label>';
    html += '<select class="tm-form-select" id="en-result"><option value="">None</option><option value="solved">Solved</option><option value="escalation">Escalate</option><option value="hardware">Hardware</option></select></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">Solution</label>';
    html += '<textarea class="tm-form-textarea" id="en-solution" style="min-height:50px;"></textarea></div>';
    html += '</div>';
    html += '<div class="tm-form-row">';
    html += '<div class="tm-form-group"><label class="tm-form-label">YES → Node ID</label>';
    html += '<input class="tm-form-input" id="en-yes" type="number" placeholder="Node ID"></div>';
    html += '<div class="tm-form-group"><label class="tm-form-label">NO → Node ID</label>';
    html += '<input class="tm-form-input" id="en-no" type="number" placeholder="Node ID"></div>';
    html += '</div>';
    
    document.getElementById('review-content').innerHTML = html;
    document.querySelector('#review-modal .tm-btn-approve').onclick = saveNewNode;
    document.querySelector('#review-modal .tm-btn-reject').style.display = 'none';
    modal.classList.add('open');
}

async function saveNewNode() {
    try {
        var data = {
            issue_id: treeIssueId,
            question: document.getElementById('en-question').value,
            description: document.getElementById('en-desc').value,
            risk: document.getElementById('en-risk').value,
            is_terminal: parseInt(document.getElementById('en-terminal').value),
            result_type: document.getElementById('en-result').value || null,
            result_solution: document.getElementById('en-solution').value || null,
            yes_next: parseInt(document.getElementById('en-yes').value) || null,
            no_next: parseInt(document.getElementById('en-no').value) || null,
        };
        var res = await fetch(APP_BASE + 'api/troubleshooting/nodes.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data),
        });
        var result = await res.json();
        if (result.error) { showToast(result.error, 'error'); return; }
        showToast('Node created! ID: ' + result.id, 'success');
        closeModal();
        loadTreeNodes(treeIssueId);
    } catch(e) { showToast('Failed: ' + e.message, 'error'); }
}

// Init
loadSubmissions();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
