<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'AI Training Center';
$active_menu = 'admin-ai';
$required_permission = 'ai.manage';
require APP_ROOT . '/includes/admin_guard.php';
require APP_ROOT . '/includes/layout_header.php';
?>

<style>
.ai-wrap { max-width: 1000px; margin: 0 auto; padding: 0 4px; }
.ai-header { margin-bottom: 20px; }
.ai-header h1 { font-size: 22px; font-weight: 800; color: #0f172a; }
.dark .ai-header h1 { color: #f1f5f9; }
.ai-header p { font-size: 13px; color: #64748b; margin-top: 4px; }

.ai-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
    padding: 20px; margin-bottom: 16px;
}
.dark .ai-card { background: #1e293b; border-color: #334155; }
.ai-card h3 { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.dark .ai-card h3 { color: #f1f5f9; }

.ai-fg { margin-bottom: 12px; }
.ai-fl { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.dark .ai-fl { color: #94a3b8; }
.ai-fi, .ai-fs, .ai-ft {
    width: 100%; padding: 9px 12px; border: 1px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; font-family: inherit; box-sizing: border-box;
}
.ai-fi:focus, .ai-fs:focus, .ai-ft:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
.dark .ai-fi, .dark .ai-fs, .dark .ai-ft { background: #0f172a; border-color: #334155; color: #f1f5f9; }
.ai-ft { min-height: 60px; resize: vertical; }
.ai-fr { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.ai-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px;
    border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.2s; font-family: inherit;
}
.ai-btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.25); }
.ai-btn-primary:hover { transform: translateY(-1px); }
.ai-btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.ai-btn-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

.ai-tabs { display: flex; gap: 4px; margin-bottom: 16px; background: #f1f5f9; border-radius: 10px; padding: 4px; }
.dark .ai-tabs { background: #0f172a; }
.ai-tab {
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; border: none; background: transparent; color: #64748b; transition: all 0.2s;
}
.ai-tab.active { background: #fff; color: #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.dark .ai-tab.active { background: #1e293b; color: #60a5fa; }

.ai-tab-content { display: none; }
.ai-tab-content.active { display: block; }

/* Training file items */
.tf-item {
    display: flex; align-items: flex-start; gap: 12px; padding: 14px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
    margin-bottom: 8px;
}
.dark .tf-item { background: #0f172a; border-color: #1e293b; }
.tf-item-icon {
    width: 36px; height: 36px; border-radius: 10px; display: flex;
    align-items: center; justify-content: center; flex-shrink: 0;
    background: #eff6ff; color: #2563eb;
}
.dark .tf-item-icon { background: #1e3a5f; }
.tf-item-body { flex: 1; min-width: 0; }
.tf-item-title { font-size: 13px; font-weight: 600; color: #0f172a; }
.dark .tf-item-title { color: #f1f5f9; }
.tf-item-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.tf-item-content { font-size: 12px; color: #64748b; margin-top: 4px; max-height: 40px; overflow: hidden; text-overflow: ellipsis; }
.tf-item-actions { display: flex; gap: 4px; flex-shrink: 0; }

/* Conversation log items */
.cl-item {
    padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px;
    margin-bottom: 8px; background: #f8fafc;
}
.dark .cl-item { background: #0f172a; border-color: #1e293b; }
.cl-msg { display: flex; gap: 8px; margin-bottom: 8px; }
.cl-role { font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; flex-shrink: 0; }
.cl-role.user { background: #dbeafe; color: #2563eb; }
.cl-role.bot { background: #f3e8ff; color: #7c3aed; }
.cl-text { font-size: 12px; color: #374151; line-height: 1.5; }
.dark .cl-text { color: #cbd5e1; }
.cl-meta { font-size: 10px; color: #94a3b8; }

/* Stats */
.stat-card {
    padding: 16px; border-radius: 12px; text-align: center;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
}
.dark .stat-card { background: linear-gradient(135deg, #1e293b, #0f172a); border-color: #334155; }
.stat-num { font-size: 28px; font-weight: 800; color: #2563eb; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 4px; }

@media (max-width: 640px) { .ai-fr { grid-template-columns: 1fr; } }
</style>

<div class="ai-wrap">
    <div class="page-hero fx-reveal">
        <div>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="page-hero-ico violet"><i data-lucide="brain"></i></div>
                <div>
                    <h1 class="page-hero-title">AI Training Center</h1>
                    <p class="page-hero-sub">Train IT Bot with your organization's knowledge. The more you add, the smarter it gets.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;" id="stats-grid">
        <div class="stat-card-premium fx-reveal" style="--fx-delay:0ms;--stat-color:#2563eb;--stat-bg:#eff6ff;--stat-bg2:#dbeafe;"><div class="stat-num" id="stat-files">0</div><div class="stat-label">Training Files</div></div>
        <div class="stat-card-premium fx-reveal" style="--fx-delay:60ms;--stat-color:#7c3aed;--stat-bg:#f5f3ff;--stat-bg2:#ede9fe;"><div class="stat-num" id="stat-conversations">0</div><div class="stat-label">Conversations</div></div>
        <div class="stat-card-premium fx-reveal" style="--fx-delay:120ms;--stat-color:#059669;--stat-bg:#ecfdf5;--stat-bg2:#d1fae5;"><div class="stat-num" id="stat-messages">0</div><div class="stat-label">Messages</div></div>
        <div class="stat-card-premium fx-reveal" style="--fx-delay:180ms;--stat-color:#d97706;--stat-bg:#fffbeb;--stat-bg2:#fef3c7;"><div class="stat-num" id="stat-sources">6</div><div class="stat-label">Data Sources</div></div>
    </div>

    <!-- Tabs -->
    <div class="ai-tabs">
        <button class="ai-tab active" onclick="switchAiTab('personality')">🤖 Bot Personality</button>
        <button class="ai-tab" onclick="switchAiTab('training')">📚 Training Data</button>
        <button class="ai-tab" onclick="switchAiTab('conversations')">💬 Conversations</button>
    </div>

    <!-- Tab: Personality -->
    <div id="tab-personality" class="ai-tab-content active">
        <div class="ai-card">
            <h3><i data-lucide="sparkles" style="width:18px;height:18px;color:#8b5cf6;"></i> Bot Identity</h3>
            <div class="ai-fr">
                <div class="ai-fg">
                    <label class="ai-fl">Bot Name</label>
                    <input type="text" class="ai-fi" id="p-name" value="IT Bot" placeholder="e.g., IT Bot, TechHelper">
                </div>
                <div class="ai-fg">
                    <label class="ai-fl">Personality Tone</label>
                    <select class="ai-fs" id="p-tone">
                        <option value="friendly">Friendly & Casual</option>
                        <option value="professional" selected>Professional & Clear</option>
                        <option value="technical">Technical & Detailed</option>
                    </select>
                </div>
            </div>
            <div class="ai-fg">
                <label class="ai-fl">Welcome Message</label>
                <textarea class="ai-ft" id="p-greeting" style="min-height:80px;">Hi there! 👋 I'm **IT Bot**, your personal IT support assistant. I'm here to help you troubleshoot any technical issue — from hardware problems to software errors, network issues, and more.

What can I help you with today?</textarea>
            </div>
            <div class="ai-fg">
                <label class="ai-fl">System Instructions (what the bot should do and how it should behave)</label>
                <textarea class="ai-ft" id="p-system" style="min-height:80px;">You are IT Bot, a friendly and professional IT support assistant. You help field technicians and users troubleshoot technical issues. You are knowledgeable about hardware, software, networking, printers, CCTV, and Windows systems. You always provide step-by-step guidance and explain things clearly.</textarea>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="ai-btn ai-btn-primary" id="ai-personality-save-btn" onclick="savePersonality()"><i data-lucide="save" style="width:14px;height:14px;"></i> Save Personality</button>
            </div>
        </div>
    </div>

    <!-- Tab: Training Data -->
    <div id="tab-training" class="ai-tab-content">
        <div class="ai-card">
            <h3><i data-lucide="file-plus" style="width:18px;height:18px;color:#2563eb;"></i> Add Training Content</h3>
            <div class="ai-fg">
                <label class="ai-fl">Title *</label>
                <input type="text" class="ai-fi" id="tf-title" placeholder="e.g., How to Fix Printer Offline Error">
            </div>
            <div class="ai-fr">
                <div class="ai-fg">
                    <label class="ai-fl">Category</label>
                    <select class="ai-fs" id="tf-category">
                        <option value="general">General</option>
                        <option value="troubleshooting">Troubleshooting</option>
                        <option value="error_codes">Error Codes</option>
                        <option value="commands">Commands</option>
                        <option value="procedures">Procedures</option>
                        <option value="policies">Policies</option>
                    </select>
                </div>
                <div class="ai-fg">
                    <label class="ai-fl">Tags (comma separated)</label>
                    <input type="text" class="ai-fi" id="tf-tags" placeholder="printer, offline, fix">
                </div>
            </div>
            <div class="ai-fg">
                <label class="ai-fl">Content / Knowledge *</label>
                <textarea class="ai-ft" id="tf-content" style="min-height:120px;" placeholder="Paste the knowledge content here. This can be troubleshooting steps, procedures, reference info, error code explanations, etc. The AI will use this to answer questions."></textarea>
                <div style="font-size:11px;color:#94a3b8;margin-top:4px;">💡 Paste content from PDFs, manuals, procedures, or type your own knowledge. The more detailed, the better the AI can help.</div>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="ai-btn ai-btn-primary" id="ai-tf-save-btn" onclick="saveTrainingFile()"><i data-lucide="plus" style="width:14px;height:14px;"></i> Add Training Content</button>
                <label class="ai-btn ai-btn-secondary" style="cursor:pointer;">
                    <i data-lucide="upload" style="width:14px;height:14px;"></i> Upload Text File
                    <input type="file" accept=".txt,.md,.csv" style="display:none;" onchange="handleFileUpload(event)">
                </label>
            </div>
        </div>

        <!-- Training files list -->
        <div class="ai-card">
            <h3><i data-lucide="library" style="width:18px;height:18px;color:#16a34a;"></i> Training Library <span id="tf-count" style="font-size:12px;color:#94a3b8;font-weight:400;"></span></h3>
            <div id="training-list"></div>
        </div>
    </div>

    <!-- Tab: Conversations -->
    <div id="tab-conversations" class="ai-tab-content">
        <div class="ai-card">
            <h3><i data-lucide="message-square" style="width:18px;height:18px;color:#d97706;"></i> Recent Conversations</h3>
            <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Review how IT Bot is responding. Use this to improve training data.</p>
            <div id="conversations-list"></div>
        </div>
    </div>
</div>

<script>
// === TAB SWITCHING ===
function switchAiTab(tab) {
    document.querySelectorAll('.ai-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.ai-tab-content').forEach(function(c) { c.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
    if (tab === 'training') loadTrainingFiles();
    if (tab === 'conversations') loadConversations();
}

// === PERSONALITY ===
async function savePersonality() {
    var saveBtn = document.getElementById('ai-personality-save-btn');
    if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, true, 'Saving…');
    var data = {
        bot_name: document.getElementById('p-name').value.trim(),
        greeting: document.getElementById('p-greeting').value.trim(),
        personality: document.getElementById('p-tone').value,
        system_prompt: document.getElementById('p-system').value.trim(),
    };
    try {
        var res = await fetch(APP_BASE + 'api/ai/training.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'save_personality', data: data })
        });
        var result = await res.json();
        showToast(result.message || result.error, result.error ? 'error' : 'success');
    } catch(e) { showToast('Failed to save', 'error'); }
    finally { if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, false); }
}

// === TRAINING FILES ===
async function loadTrainingFiles() {
    var listEl = document.getElementById('training-list');
    if (listEl && typeof skeletonFill === 'function') skeletonFill(listEl, 4, 2);
    try {
        var res = await fetch(APP_BASE + 'api/ai/training.php?action=list');
        var data = await res.json();
        document.getElementById('stat-files').textContent = data.length;
        document.getElementById('tf-count').textContent = '(' + data.length + ' files)';
        var html = '';
        if (!data.length) {
            html = '<div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;">No training files yet. Add your first training content above.</div>';
        } else {
            data.forEach(function(tf) {
                html += '<div class="tf-item">';
                html += '<div class="tf-item-icon"><i data-lucide="file-text" style="width:18px;height:18px;"></i></div>';
                html += '<div class="tf-item-body">';
                html += '<div class="tf-item-title">' + esc(tf.title) + '</div>';
                html += '<div class="tf-item-meta">' + esc(tf.category) + ' • ' + (tf.tags || 'no tags') + ' • ' + formatDate(tf.created_at) + '</div>';
                html += '<div class="tf-item-content">' + esc(tf.content).substring(0, 150) + '...</div>';
                html += '</div>';
                html += '<div class="tf-item-actions">';
                html += '<button class="ai-btn ai-btn-danger" style="padding:5px 10px;font-size:11px;" onclick="deleteTrainingFile(' + tf.id + ')"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button>';
                html += '</div></div>';
            });
        }
        document.getElementById('training-list').innerHTML = html;
        lucide.createIcons();
    } catch(e) { console.error(e); }
}

async function saveTrainingFile() {
    var title = document.getElementById('tf-title').value.trim();
    var content = document.getElementById('tf-content').value.trim();
    if (!title || !content) { showToast('Title and content are required', 'error'); return; }
    var saveBtn = document.getElementById('ai-tf-save-btn');
    if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, true, 'Adding…');
    try {
        var res = await fetch(APP_BASE + 'api/ai/training.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_file',
                title: title,
                content: content,
                category: document.getElementById('tf-category').value,
                tags: document.getElementById('tf-tags').value.trim(),
            })
        });
        var result = await res.json();
        if (result.error) { showToast(result.error, 'error'); return; }
        showToast('Training content added!', 'success');
        document.getElementById('tf-title').value = '';
        document.getElementById('tf-content').value = '';
        document.getElementById('tf-tags').value = '';
        loadTrainingFiles();
    } catch(e) { showToast('Failed to save', 'error'); }
    finally { if (typeof setButtonLoading === 'function') setButtonLoading(saveBtn, false); }
}

function handleFileUpload(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('tf-content').value = e.target.result;
        document.getElementById('tf-title').value = file.name.replace(/\.[^.]+$/, '');
        showToast('File loaded! Review and click "Add Training Content" to save.', 'success');
    };
    reader.readAsText(file);
    event.target.value = '';
}

async function deleteTrainingFile(id) {
    swalConfirm('Delete Training File?', 'This training data will be permanently removed.', async function() {
        try {
            var res = await fetch(APP_BASE + 'api/ai/training.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'delete_file', id: id })
            });
            var result = await res.json();
            if (result.error) { swalError('Error', result.error); } else { swalSuccess('Deleted!', result.message || 'Training file removed.'); }
            loadTrainingFiles();
        } catch(e) { swalError('Failed', 'Could not delete training file.'); }
    });
}

// === CONVERSATIONS ===
async function loadConversations() {
    var listEl = document.getElementById('conversations-list');
    if (listEl && typeof skeletonFill === 'function') skeletonFill(listEl, 4, 3);
    try {
        var res = await fetch(APP_BASE + 'api/ai/training.php?action=conversations');
        var data = await res.json();
        document.getElementById('stat-conversations').textContent = data.sessions || 0;
        document.getElementById('stat-messages').textContent = data.messages || 0;
        var html = '';
        if (!data.logs || !data.logs.length) {
            html = '<div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;">No conversations yet. Start chatting with IT Bot to see logs here.</div>';
        } else {
            data.logs.forEach(function(log) {
                html += '<div class="cl-item">';
                html += '<div class="cl-msg"><span class="cl-role user">User</span><div class="cl-text">' + esc(log.message) + '</div></div>';
                html += '<div class="cl-msg"><span class="cl-role bot">Bot</span><div class="cl-text">' + esc((log.response || '').substring(0, 200)) + '...</div></div>';
                html += '<div class="cl-meta">' + (log.confidence || 'N/A') + ' confidence • ' + (log.sources_used || 'N/A') + ' • ' + formatDate(log.created_at) + '</div>';
                html += '</div>';
            });
        }
        document.getElementById('conversations-list').innerHTML = html;
    } catch(e) { console.error(e); }
}

// === UTILS ===
function esc(s) { return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }
function formatDate(d) { if (!d) return ''; var diff = (new Date() - new Date(d)) / 1000; if (diff < 3600) return Math.floor(diff/60) + 'm ago'; if (diff < 86400) return Math.floor(diff/3600) + 'h ago'; return Math.floor(diff/86400) + 'd ago'; }
function showToast(msg, type) {
    var t = document.createElement('div'); t.className = 'toast ' + type;
    t.innerHTML = '<span style="font-size:13px;">' + msg + '</span>';
    var c = document.getElementById('toast-container'); if (c) c.appendChild(t);
    setTimeout(function() { t.remove(); }, 4000);
}

// Init
loadTrainingFiles();
lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
