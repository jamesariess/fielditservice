/**
 * Field IT Support Hub - Complete Application JavaScript
 * All page-specific handlers included here.
 */

// ==================== Base URL ====================
var APP_BASE = (function() {
    var m = window.location.pathname.match(/^(.*\/public)/);
    return m ? m[1] + '/' : '/';
})();

// ==================== Dark Mode ====================
function initDarkMode() {
    const mode = localStorage.getItem('theme');
    if (mode === 'dark' || (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        applyDarkInputs();
    }
}
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    applyDarkInputs();
}
function applyDarkInputs() {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('.dark-input').forEach(el => {
        if (isDark) { el.style.background = '#0f172a'; el.style.borderColor = '#334155'; el.style.color = '#f1f5f9'; }
        else { el.style.background = ''; el.style.borderColor = ''; el.style.color = ''; }
    });
}

// ==================== Sidebar ====================
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.add('active');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('active');
}

// ==================== Toasts ====================
function showToast(message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    if (!container) return;
    var icons = { success:'check-circle', error:'x-circle', warning:'alert-triangle', info:'info' };
    var borderColors = { success:'#16a34a', error:'#dc2626', warning:'#d97706', info:'#2563eb' };
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.style.cssText = 'display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fff;border-radius:12px;border-left:4px solid ' + borderColors[type] + ';box-shadow:0 4px 20px rgba(0,0,0,0.1);pointer-events:auto;animation:slideIn 0.3s ease;margin-bottom:8px;';
    toast.innerHTML = '<i data-lucide="' + icons[type] + '" style="width:18px;height:18px;color:' + borderColors[type] + ';flex-shrink:0;"></i>' +
        '<span style="flex:1;font-size:13px;font-weight:500;color:#374151;">' + message + '</span>' +
        '<button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;"><i data-lucide="x" style="width:14px;height:14px;"></i></button>';
    container.appendChild(toast);
    try { lucide.createIcons({ nodes: [toast] }); } catch(e) {}
    setTimeout(function() { if (toast.parentElement) toast.remove(); }, 4000);
}

// ==================== API Helper ====================
function api(endpoint, options) {
    if (endpoint.charAt(0) === '/') endpoint = APP_BASE + endpoint.substring(1);
    options = options || {};
    var csrfToken = '';
    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (metaCsrf) csrfToken = metaCsrf.content;
    var defaults = {
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        credentials: 'same-origin'
    };
    if (options.body && typeof options.body === 'object') options.body = JSON.stringify(options.body);
    var merged = Object.assign({}, defaults, options);
    merged.headers = Object.assign({}, defaults.headers, options.headers || {});
    return fetch(endpoint, merged).then(function(response) {
        return response.json().then(function(data) {
            if (!response.ok) {
                if (response.status === 401) { window.location.href = APP_BASE + 'login'; return; }
                throw new Error(data.error || 'Request failed');
            }
            return data;
        });
    }).catch(function(err) {
        if (err.message === 'Failed to fetch') showToast('Network error. You may be offline.', 'warning');
        throw err;
    });
}

// ==================== Search ====================
function handleGlobalSearch(query) { if (query.length >= 2) openSearchModal(query); }
function openSearchModal(query) {
    query = query || '';
    var m = document.getElementById('search-modal');
    if (!m) return;
    m.style.display = '';
    var inp = document.getElementById('modal-search-input');
    inp.value = query;
    inp.focus();
    if (query) performSearch(query);
}
function closeSearchModal() {
    var m = document.getElementById('search-modal');
    if (m) m.style.display = 'none';
    var r = document.getElementById('search-results');
    if (r) r.innerHTML = '';
}
function performSearch(query) {
    var r = document.getElementById('search-results');
    if (!r) return;
    r.innerHTML = '<div style="text-align:center;padding:32px;"><div style="width:24px;height:24px;border:2px solid #2563eb;border-top-color:transparent;border-radius:50%;margin:0 auto;animation:spin 0.6s linear infinite;"></div></div><style>@keyframes spin{to{transform:rotate(360deg)}}</style>';
    api('/api/search?q=' + encodeURIComponent(query)).then(function(data) {
        if (data.results && data.results.length > 0) {
            r.innerHTML = data.results.map(function(item) {
                return '<a href="' + item.url + '" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">' +
                '<div style="width:34px;height:34px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="' + (item.icon||'file') + '" style="width:16px;height:16px;color:#2563eb;"></i></div>' +
                '<div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + item.title + '</div>' +
                '<div style="font-size:11px;color:#94a3b8;">' + (item.description||'') + '</div></div>' +
                '<span style="font-size:10px;color:#94a3b8;flex-shrink:0;">' + (item.type||'') + '</span></a>';
            }).join('');
            try { lucide.createIcons({ nodes: [r] }); } catch(e) {}
        } else {
            r.innerHTML = '<div class="empty-state" style="padding:32px;"><div class="empty-state-icon"><i data-lucide="search-x"></i></div><h3>No results found</h3><p>Try different keywords</p></div>';
            try { lucide.createIcons({ nodes: [r] }); } catch(e) {}
        }
    }).catch(function() {
        r.innerHTML = '<div class="empty-state" style="padding:24px;"><p style="font-size:13px;color:#94a3b8;">Search temporarily unavailable</p></div>';
    });
}

// ==================== Modal System ====================
function openModal(id) {
    var m = document.getElementById(id);
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
}
function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(function(m) { m.style.display = 'none'; });
    document.body.style.overflow = '';
}

// ==================== AI Chat ====================
var aiProcessing = false;
function aiSendQuick(msg) {
    var inp = document.getElementById('chat-input');
    if (inp) { inp.value = msg; aiSendMessage(); }
}
function aiSendMessage(e) {
    if (e) e.preventDefault();
    var input = document.getElementById('chat-input');
    if (!input) return;
    var msg = input.value.trim();
    if (!msg || aiProcessing) return;
    aiProcessing = true;
    input.value = '';
    input.style.height = 'auto';
    aiAddMessage(msg, 'user');
    var tid = aiAddTyping();
    api('/api/ai/chat', { method: 'POST', body: { message: msg } }).then(function(data) {
        aiRemoveTyping(tid);
        aiAddMessage(data.response, 'ai', data.source);
        aiProcessing = false;
    }).catch(function() {
        aiRemoveTyping(tid);
        aiAddMessage('Sorry, an error occurred. Please try again.', 'ai');
        aiProcessing = false;
    });
}
function aiAddMessage(text, type, source) {
    var c = document.getElementById('chat-messages');
    if (!c) return;
    var isUser = type === 'user';
    var html = '<div style="display:flex;gap:12px;' + (isUser ? 'flex-direction:row-reverse;' : '') + 'padding-left:' + (isUser ? '44px' : '0') + ';padding-right:' + (!isUser ? '44px' : '0') + ';">' +
        '<div style="width:32px;height:32px;border-radius:10px;background:' + (isUser ? '#dbeafe' : 'linear-gradient(135deg,#8b5cf6,#6d28d9)') + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">' +
        '<i data-lucide="' + (isUser ? 'user' : 'sparkles') + '" style="width:14px;height:14px;color:' + (isUser ? '#1d4ed8' : '#fff') + ';"></i></div>' +
        '<div class="chat-bubble ' + type + '">' +
        '<div style="white-space:pre-wrap;line-height:1.7;">' + aiFormatText(text) + '</div>';
    if (!isUser) {
        html += '<div style="margin-top:12px;padding-top:10px;border-top:1px solid #e5e7eb;display:flex;gap:6px;">' +
        '<button onclick="aiRate(this,\'yes\')" style="padding:4px 12px;font-size:11px;font-weight:600;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:16px;cursor:pointer;">&#10003; Helpful</button>' +
        '<button onclick="aiRate(this,\'no\')" style="padding:4px 12px;font-size:11px;font-weight:600;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:16px;cursor:pointer;">&#10007; Not helpful</button>' +
        '</div>';
    }
    if (source) html += '<div style="margin-top:8px;font-size:10px;color:#94a3b8;font-style:italic;">Source: ' + source + '</div>';
    html += '</div></div>';
    c.insertAdjacentHTML('beforeend', html);
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
}
function aiFormatText(text) {
    if (!text) return '';
    return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`(.*?)`/g, '<code style="padding:2px 6px;background:#f1f5f9;border-radius:4px;font-size:12px;font-family:monospace;">$1</code>')
        .replace(/### (.*?)(\n|$)/g, '<h4 style="font-weight:700;font-size:14px;margin:12px 0 6px;">$1</h4>')
        .replace(/## (.*?)(\n|$)/g, '<h3 style="font-weight:700;font-size:15px;margin:14px 0 6px;">$1</h3>');
}
function aiAddTyping() {
    var c = document.getElementById('chat-messages');
    if (!c) return '';
    var id = 't-' + Date.now();
    c.insertAdjacentHTML('beforeend', '<div id="' + id + '" style="display:flex;gap:12px;"><div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="sparkles" style="width:14px;height:14px;color:#fff;"></i></div><div style="padding:12px 16px;background:#f1f5f9;border-radius:16px;display:flex;gap:5px;"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div></div>');
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
    return id;
}
function aiRemoveTyping(id) { var el = document.getElementById(id); if (el) el.remove(); }
function aiRate(btn, r) {
    btn.parentElement.innerHTML = '<span style="font-size:11px;color:#64748b;">Thanks for your feedback!</span>';
    api('/api/ai/feedback', { method: 'POST', body: { rating: r } }).catch(function(){});
}

// ==================== Knowledge Article Rating ====================
function kbRateArticle(rating) {
    var fb = document.getElementById('rating-feedback');
    if (fb) fb.style.display = '';
    var btns = document.querySelectorAll('#rate-yes, #rate-partial, #rate-no, #rate-helpful, #rate-not');
    btns.forEach(function(b) { b.style.opacity = '0.5'; b.disabled = true; });
    showToast('Thank you for your feedback!', 'success');
    api('/api/knowledge/rate', { method: 'POST', body: { article_id: 1, rating: rating } }).catch(function(){});
}

// ==================== Ticket Filters ====================
function filterTickets(status) { ticketFilter(status); }
function ticketFilter(status) {
    document.querySelectorAll('.ticket-card').forEach(function(card) {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.filter === status) btn.classList.add('active');
    });
}

// ==================== Command Filters ====================
function filterCmds(q) { cmdSearch(q); }
function filterCat(cat) { cmdFilter(cat); }
function copyCmd(text) { cmdCopy(text); }
function cmdSearch(q) {
    var query = q.toLowerCase();
    document.querySelectorAll('.cmd-card').forEach(function(card) {
        var cmdText = (card.dataset.cmd || card.textContent).toLowerCase();
        card.style.display = cmdText.indexOf(query) >= 0 ? '' : 'none';
    });
}
function cmdFilter(category) {
    document.querySelectorAll('.cmd-card').forEach(function(card) {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    document.querySelectorAll('.cmd-filter-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.filter === category) btn.classList.add('active');
    });
}
function cmdCopy(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Command copied to clipboard!', 'success');
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Command copied to clipboard!', 'success');
    }
}

// ==================== New Ticket Modal ====================
function openNewTicketModal() { openModal('new-ticket-modal'); }
function createTicket(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        title: form.querySelector('[name="title"]').value,
        description: form.querySelector('[name="description"]').value,
        category: form.querySelector('[name="category"]').value,
        priority: form.querySelector('[name="priority"]').value,
        department: form.querySelector('[name="department"]').value,
        location: form.querySelector('[name="location"]').value,
        device: form.querySelector('[name="device"]').value
    };
    api('/api/tickets/create', { method: 'POST', body: data }).then(function(res) {
        showToast('Ticket ' + res.ticket_id + ' created successfully!', 'success');
        closeModal('new-ticket-modal');
        form.reset();
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) {
        showToast('Error creating ticket: ' + err.message, 'error');
    });
}

// ==================== Ticket Troubleshoot Inline ====================
function ticketTroubleshoot(ticketId, issueSlug) {
    var container = document.getElementById('troubleshoot-panel-' + ticketId);
    if (!container) return;
    if (container.style.display === 'block') {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';
    container.innerHTML = '<div style="text-align:center;padding:24px;"><div style="width:24px;height:24px;border:2px solid #2563eb;border-top-color:transparent;border-radius:50%;margin:0 auto;animation:spin 0.6s linear infinite;"></div><p style="font-size:13px;color:#64748b;margin-top:8px;">Loading decision tree...</p></div>';
    // Load decision tree from API
    api('/api/troubleshooting/decision?issue=' + encodeURIComponent(issueSlug)).then(function(data) {
        if (data && data.node) {
            renderDecisionNode(ticketId, data);
        } else {
            container.innerHTML = '<div style="padding:16px;"><p style="color:#64748b;">No troubleshooting flow available for this issue.</p><button onclick="ticketTroubleshoot(' + ticketId + ')" class="btn btn-secondary btn-sm" style="margin-top:8px;">Close</button></div>';
        }
    }).catch(function() {
        container.innerHTML = '<div style="padding:16px;"><p style="color:#dc2626;">Error loading troubleshooting flow.</p><button onclick="ticketTroubleshoot(' + ticketId + ')" class="btn btn-secondary btn-sm" style="margin-top:8px;">Close</button></div>';
    });
}
function renderDecisionNode(ticketId, data) {
    var container = document.getElementById('troubleshoot-panel-' + ticketId);
    if (!container) return;
    var node = data.node;
    var html = '<div style="padding:20px;">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">';
    html += '<h3 style="font-size:16px;font-weight:700;color:#111827;">Troubleshooting</h3>';
    html += '<button onclick="ticketTroubleshoot(' + ticketId + ')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:13px;">&#10005; Close</button>';
    html += '</div>';
    // Risk warning
    if (node.risk && node.risk !== 'safe') {
        var riskColor = node.risk === 'danger' ? '#dc2626' : '#d97706';
        html += '<div style="padding:10px 14px;background:' + (node.risk === 'danger' ? '#fef2f2' : '#fffbeb') + ';border:1px solid ' + (node.risk === 'danger' ? '#fecaca' : '#fde68a') + ';border-radius:8px;margin-bottom:12px;font-size:12px;color:' + riskColor + ';font-weight:600;">';
        html += '&#9888; Risk: ' + node.risk.charAt(0).toUpperCase() + node.risk.slice(1) + ' - Use appropriate precautions';
        html += '</div>';
    }
    // Question
    if (node.question) {
        html += '<div class="card" style="margin-bottom:12px;"><div class="card-body">';
        html += '<p style="font-size:14px;font-weight:600;color:#111827;margin-bottom:4px;">' + node.question + '</p>';
        if (node.description) html += '<p style="font-size:12px;color:#64748b;">' + node.description + '</p>';
        html += '</div></div>';
        // Yes/No buttons
        html += '<div style="display:flex;gap:10px;margin-bottom:16px;">';
        html += '<button onclick="ticketAnswer(' + ticketId + ',' + node.id + ',\'yes\')" class="btn btn-success" style="flex:1;"><i data-lucide="check" style="width:15px;height:15px;"></i> Yes</button>';
        html += '<button onclick="ticketAnswer(' + ticketId + ',' + node.id + ',\'no\')" class="btn btn-danger" style="flex:1;"><i data-lucide="x" style="width:15px;height:15px;"></i> No</button>';
        html += '</div>';
    }
    // Terminal node - solution
    if (node.is_terminal) {
        html += '<div class="card" style="border-color:#bbf7d0;"><div class="card-body">';
        if (node.result_type === 'solved') {
            html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><i data-lucide="check-circle" style="width:20px;height:20px;color:#16a34a;"></i><span style="font-size:14px;font-weight:700;color:#16a34a;">Issue Resolved</span></div>';
        } else if (node.result_type === 'redirect') {
            html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><i data-lucide="arrow-right-circle" style="width:20px;height:20px;color:#2563eb;"></i><span style="font-size:14px;font-weight:700;color:#2563eb;">Redirect</span></div>';
        } else if (node.result_type === 'hardware') {
            html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><i data-lucide="cpu" style="width:20px;height:20px;color:#d97706;"></i><span style="font-size:14px;font-weight:700;color:#d97706;">Hardware Replacement Needed</span></div>';
        } else {
            html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><i data-lucide="alert-triangle" style="width:20px;height:20px;color:#dc2626;"></i><span style="font-size:14px;font-weight:700;color:#dc2626;">Escalation Required</span></div>';
        }
        html += '<p style="font-size:13px;color:#374151;line-height:1.6;">' + (node.question || node.description || 'No additional details.') + '</p>';
        html += '</div></div>';
        // Mark ticket resolved or escalate
        html += '<div style="display:flex;gap:8px;margin-top:12px;">';
        if (node.result_type === 'solved') {
            html += '<button onclick="ticketResolve(' + ticketId + ')" class="btn btn-success"><i data-lucide="check-circle" style="width:15px;height:15px;"></i> Mark as Resolved</button>';
        } else if (node.result_type === 'escalation') {
            html += '<button onclick="ticketEscalate(' + ticketId + ')" class="btn btn-warning"><i data-lucide="alert-triangle" style="width:15px;height:15px;"></i> Escalate Ticket</button>';
        }
        html += '</div>';
    }
    html += '</div>';
    container.innerHTML = html;
    try { lucide.createIcons({ nodes: [container] }); } catch(e) {}
}
function ticketAnswer(ticketId, nodeId, answer) {
    var container = document.getElementById('troubleshoot-panel-' + ticketId);
    if (!container) return;
    container.innerHTML = '<div style="text-align:center;padding:24px;"><div style="width:24px;height:24px;border:2px solid #2563eb;border-top-color:transparent;border-radius:50%;margin:0 auto;animation:spin 0.6s linear infinite;"></div></div>';
    api('/api/troubleshooting/decision', { method: 'POST', body: { node_id: nodeId, answer: answer } }).then(function(data) {
        // Terminal response (solved/escalated/hardware/redirect)
        if (data.solved || data.escalated || data.hardware_replacement || data.redirect) {
            renderTerminalResult(ticketId, data);
        } else if (data.node) {
            renderDecisionNode(ticketId, data);
        } else {
            renderTerminalResult(ticketId, { escalated: true, message: 'No further steps. Escalate to supervisor.', solution: '' });
        }
    }).catch(function() {
        container.innerHTML = '<div style="padding:16px;"><p style="color:#dc2626;">Error processing answer.</p></div>';
    });
}
function renderTerminalResult(ticketId, data) {
    var container = document.getElementById('troubleshoot-panel-' + ticketId);
    if (!container) return;
    var html = '<div style="padding:20px;">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">';
    html += '<h3 style="font-size:16px;font-weight:700;color:#111827;">Troubleshooting Result</h3>';
    html += '<button onclick="ticketTroubleshoot(' + ticketId + ')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:13px;">&#10005; Close</button>';
    html += '</div>';
    var icon, color, bgColor, borderColor, label;
    if (data.solved) {
        icon = 'check-circle'; color = '#16a34a'; bgColor = '#f0fdf4'; borderColor = '#bbf7d0'; label = 'Issue Resolved';
    } else if (data.redirect) {
        icon = 'arrow-right-circle'; color = '#2563eb'; bgColor = '#eff6ff'; borderColor = '#bfdbfe'; label = 'Redirect';
    } else if (data.hardware_replacement) {
        icon = 'cpu'; color = '#d97706'; bgColor = '#fffbeb'; borderColor = '#fde68a'; label = 'Hardware Replacement Needed';
    } else {
        icon = 'alert-triangle'; color = '#dc2626'; bgColor = '#fef2f2'; borderColor = '#fecaca'; label = 'Escalation Required';
    }
    html += '<div style="padding:16px;background:' + bgColor + ';border:1px solid ' + borderColor + ';border-radius:10px;margin-bottom:16px;">';
    html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;"><i data-lucide="' + icon + '" style="width:20px;height:20px;color:' + color + ';"></i><span style="font-size:15px;font-weight:700;color:' + color + ';">' + label + '</span></div>';
    html += '<p style="font-size:13px;color:#374151;line-height:1.6;margin-bottom:8px;">' + (data.message || 'Resolution reached.') + '</p>';
    if (data.detail) html += '<p style="font-size:12px;color:#64748b;">' + data.detail + '</p>';
    if (data.solution) html += '<p style="font-size:12px;color:#475569;margin-top:8px;padding-top:8px;border-top:1px solid ' + borderColor + ';"><strong>Solution:</strong> ' + data.solution + '</p>';
    html += '</div>';
    html += '<div style="display:flex;gap:8px;">';
    if (data.solved) {
        html += '<button onclick="ticketResolve(' + ticketId + ')" class="btn btn-success"><i data-lucide="check-circle" style="width:15px;height:15px;"></i> Mark as Resolved</button>';
    } else if (data.escalated) {
        html += '<button onclick="ticketEscalate(' + ticketId + ')" class="btn btn-warning"><i data-lucide="alert-triangle" style="width:15px;height:15px;"></i> Escalate Ticket</button>';
    }
    html += '</div>';
    html += '</div>';
    container.innerHTML = html;
    try { lucide.createIcons({ nodes: [container] }); } catch(e) {}
}
function ticketResolve(ticketId) {
    api('/api/tickets/action', { method: 'POST', body: { action: 'resolve', id: ticketId } }).then(function() {
        showToast('Ticket resolved!', 'success');
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) {
        showToast('Error: ' + err.message, 'error');
    });
}
function ticketEscalate(ticketId) {
    api('/api/tickets/action', { method: 'POST', body: { action: 'escalate', id: ticketId } }).then(function() {
        showToast('Ticket escalated to supervisor!', 'warning');
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) {
        showToast('Error: ' + err.message, 'error');
    });
}

// ==================== Team Chat ====================
function chatSendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('chat-msg-input');
    if (!input) return;
    var msg = input.value.trim();
    if (!msg) return;
    var conversationId = input.dataset.conversationId || '1';
    // Add message to UI immediately
    chatAddMsgToUI(msg, 'out');
    input.value = '';
    api('/api/chat/send', { method: 'POST', body: { conversation_id: conversationId, message: msg } }).then(function() {
        showToast('Message sent', 'success');
    }).catch(function(err) {
        showToast('Failed to send: ' + err.message, 'error');
    });
}
function chatAddMsgToUI(text, direction) {
    var container = document.getElementById('chat-messages');
    if (!container) return;
    var isOut = direction === 'out';
    var html = '<div style="display:flex;' + (isOut ? 'justify-content:flex-end;' : '') + 'margin-bottom:12px;">';
    html += '<div style="max-width:70%;padding:10px 14px;border-radius:16px;font-size:13px;line-height:1.5;' +
        (isOut ? 'background:#2563eb;color:#fff;border-bottom-right-radius:4px;' : 'background:#f1f5f9;color:#111827;border-bottom-left-radius:4px;') + '">';
    html += text;
    html += '<div style="font-size:10px;margin-top:4px;opacity:0.6;">Just now</div>';
    html += '</div></div>';
    container.insertAdjacentHTML('beforeend', html);
    container.scrollTop = container.scrollHeight;
}

// ==================== Documentation Submit ====================
function docSubmit(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        title: form.querySelector('[name="title"]').value,
        content: form.querySelector('[name="content"]').value,
        category: form.querySelector('[name="category"]').value,
        tags: form.querySelector('[name="tags"]').value
    };
    api('/api/documentation', { method: 'POST', body: data }).then(function() {
        showToast('Documentation submitted for review!', 'success');
        form.reset();
    }).catch(function(err) {
        showToast('Error: ' + err.message, 'error');
    });
}

// ==================== Admin: Invite User ====================
function openInviteUserModal() { openModal('invite-user-modal'); }
function inviteUser(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        email: form.querySelector('[name="email"]').value,
        name: form.querySelector('[name="name"]').value,
        role: form.querySelector('[name="role"]').value,
        department: form.querySelector('[name="department"]').value
    };
    api('/api/users/invite', { method: 'POST', body: data }).then(function() {
        showToast('Invitation sent successfully!', 'success');
        closeModal('invite-user-modal');
        form.reset();
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) {
        showToast('Error: ' + err.message, 'error');
    });
}

// ==================== Admin: Edit User ====================
function editUser(userId) {
    showToast('Edit user: ' + userId, 'info');
    openModal('edit-user-modal');
}
function deleteUser(userId) {
    if (confirm('Are you sure you want to remove this user?')) {
        api('/api/users/delete?id=' + userId, { method: 'DELETE' }).then(function() {
            showToast('User removed', 'success');
            setTimeout(function() { window.location.reload(); }, 1000);
        }).catch(function(err) {
            showToast('Error: ' + err.message, 'error');
        });
    }
}

// ==================== Admin: KB Approve/Reject ====================
function kbApprove(articleId) {
    api('/api/knowledge/approve', { method: 'POST', body: { article_id: articleId, action: 'approve' } }).then(function() {
        showToast('Article approved and published!', 'success');
        var row = document.getElementById('kb-row-' + articleId);
        if (row) {
            var statusCell = row.querySelector('.kb-status');
            if (statusCell) { statusCell.textContent = 'Published'; statusCell.className = 'badge badge-green kb-status'; }
            var actionsCell = row.querySelector('.kb-actions');
            if (actionsCell) actionsCell.innerHTML = '<i data-lucide="pencil" style="width:14px;height:14px;color:#64748b;"></i>';
            try { lucide.createIcons({ nodes: [row] }); } catch(e) {}
        }
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}
function kbReject(articleId) {
    api('/api/knowledge/approve', { method: 'POST', body: { article_id: articleId, action: 'reject' } }).then(function() {
        showToast('Article rejected', 'warning');
        var row = document.getElementById('kb-row-' + articleId);
        if (row) {
            var statusCell = row.querySelector('.kb-status');
            if (statusCell) { statusCell.textContent = 'Rejected'; statusCell.className = 'badge badge-red kb-status'; }
            var actionsCell = row.querySelector('.kb-actions');
            if (actionsCell) actionsCell.innerHTML = '';
        }
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== Admin: Add Department ====================
function openAddDeptModal() { openModal('add-dept-modal'); }
function addDepartment(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        name: form.querySelector('[name="name"]').value,
        description: form.querySelector('[name="description"]').value
    };
    api('/api/departments', { method: 'POST', body: data }).then(function() {
        showToast('Department added!', 'success');
        closeModal('add-dept-modal');
        form.reset();
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== Admin: Add Equipment ====================
function openAddEquipmentModal() { openModal('add-equipment-modal'); }
function addEquipment(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        manufacturer: form.querySelector('[name="manufacturer"]').value,
        model: form.querySelector('[name="model"]').value,
        type: form.querySelector('[name="type"]').value,
        serial_number: form.querySelector('[name="serial_number"]').value,
        notes: form.querySelector('[name="notes"]').value
    };
    api('/api/equipment', { method: 'POST', body: data }).then(function() {
        showToast('Equipment added!', 'success');
        closeModal('add-equipment-modal');
        form.reset();
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== Profile ====================
function updateProfile(e) {
    e.preventDefault();
    var form = e.target;
    var data = {
        name: form.querySelector('[name="name"]').value,
        email: form.querySelector('[name="email"]').value,
        phone: form.querySelector('[name="phone"]') ? form.querySelector('[name="phone"]').value : ''
    };
    api('/api/profile', { method: 'POST', body: data }).then(function() {
        showToast('Profile updated!', 'success');
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== Settings ====================
function saveSettings(e) {
    e.preventDefault();
    var form = e.target;
    var data = {};
    var inputs = form.querySelectorAll('[name]');
    inputs.forEach(function(inp) { data[inp.name] = inp.value; });
    api('/api/settings', { method: 'POST', body: data }).then(function() {
        showToast('Settings saved!', 'success');
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== Troubleshoot Filter ====================
function filterIssues(query) {
    var q = (query || '').toLowerCase().trim();
    var cards = document.querySelectorAll('#category-grid a');
    if (!q) { cards.forEach(function(c) { c.style.display = ''; }); return; }
    cards.forEach(function(card) {
        var tags = (card.dataset.tags || '').toLowerCase();
        var title = (card.dataset.title || '').toLowerCase();
        var desc = (card.dataset.desc || '').toLowerCase();
        card.style.display = (tags.indexOf(q) >= 0 || title.indexOf(q) >= 0 || desc.indexOf(q) >= 0) ? '' : 'none';
    });
}

// ==================== Favorites ====================
function toggleFavorite(type, itemId) {
    api('/api/favorites/toggle', { method: 'POST', body: { type: type, item_id: itemId } }).then(function(data) {
        showToast(data.favorited ? 'Added to favorites' : 'Removed from favorites', 'success');
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== KB Search/Filter ====================
function kbFilter(status) {
    document.querySelectorAll('.kb-row').forEach(function(row) {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    document.querySelectorAll('.kb-filter-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.filter === status) btn.classList.add('active');
    });
}

// ==================== Keyboard Shortcuts ====================
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearchModal(); }
    if (e.key === 'Escape') closeAllModals();
});

// ==================== Fix Base URLs for All Links ====================
(function() {
    function fixLinks() {
        document.querySelectorAll('a[href]').forEach(function(a) {
            var href = a.getAttribute('href');
            if (href && href.charAt(0) === '/' && href.indexOf(APP_BASE) !== 0 && !href.match(/^\/(assets|css|js|images|fonts)/)) {
                a.setAttribute('href', APP_BASE + href.substring(1));
            }
        });
    }
    // Fix links on DOM ready and after any AJAX content loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixLinks);
    } else {
        fixLinks();
    }
    // Watch for new links added to DOM (modals, AJAX content)
    var observer = new MutationObserver(function(mutations) {
        var hasNewLinks = mutations.some(function(m) {
            return m.addedNodes.length > 0;
        });
        if (hasNewLinks) fixLinks();
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();

// ==================== Init ====================
document.addEventListener('DOMContentLoaded', function() {
    initDarkMode();
    var si = document.getElementById('modal-search-input');
    if (si) {
        var t;
        si.addEventListener('input', function(e) {
            clearTimeout(t);
            t = setTimeout(function() { performSearch(e.target.value); }, 300);
        });
    }
    // Connection status
    function updateConn() {
        var el = document.getElementById('connection-status');
        if (!el) return;
        if (!navigator.onLine) { el.style.display = 'flex'; } else { el.style.display = 'none'; }
    }
    updateConn();
    window.addEventListener('online', updateConn);
    window.addEventListener('offline', updateConn);
    // Init icons
    try { lucide.createIcons(); } catch(e) {}
});
