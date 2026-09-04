/**
 * Field IT Support Hub - Complete Application JavaScript
 * All page-specific handlers included here.
 */

// ==================== Base URL ====================
document.documentElement.classList.add('js');
var APP_BASE = (function() {
    // Prefer the base URL injected by PHP (correct for clean URLs & sub-folder installs).
    var meta = document.querySelector('meta[name="app-base"]');
    if (meta && meta.content) return meta.content;
    // Fallback: derive from the pathname (legacy /public URL style).
    var m = window.location.pathname.match(/^(.*\/public)/);
    return m ? m[1] + '/' : '/';
})();

// ==================== Global CSRF Injection ====================
// Every state-changing fetch automatically carries the session CSRF token,
// so the server-side CSRF gate (public/index.php) is satisfied regardless of
// whether a call uses the api() helper or a raw fetch().
(function() {
    var TOKEN_HEADER = 'X-CSRF-Token';
    function getToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }
    function isStateChanging(method) {
        return ['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(method) !== -1;
    }
    var originalFetch = window.fetch;
    window.fetch = function(url, opts) {
        opts = opts || {};
        var method = (opts.method || 'GET').toUpperCase();
        if (isStateChanging(method)) {
            if (opts.headers && typeof opts.headers.append === 'function') {
                // Headers instance
                if (!opts.headers.has(TOKEN_HEADER)) opts.headers.append(TOKEN_HEADER, getToken());
            } else {
                opts.headers = opts.headers || {};
                if (!opts.headers[TOKEN_HEADER]) opts.headers[TOKEN_HEADER] = getToken();
            }
        }
        return originalFetch.call(this, url, opts);
    };
})();

// ==================== Page Load Progress Bar ====================
// Slim gradient bar at the very top of every page: animates while loading,
// completes and fades out when the page is ready.
(function() {
    if (document.getElementById('page-progress')) return;
    var bar = document.createElement('div');
    bar.id = 'page-progress';
    bar.style.width = '12%';
    document.documentElement.appendChild(bar);
    requestAnimationFrame(function() { bar.style.width = '62%'; });
    var t1 = setTimeout(function() { bar.style.width = '85%'; }, 350);
    function finish() {
        clearTimeout(t1);
        bar.style.width = '100%';
        setTimeout(function() {
            bar.classList.add('done');
            setTimeout(function() { if (bar.parentElement) bar.remove(); }, 450);
        }, 180);
    }
    if (document.readyState === 'complete') finish();
    else window.addEventListener('load', finish);
    // Safety: never leave the bar stuck
    setTimeout(finish, 6000);
})();

// ==================== SweetAlert2 Helpers ====================
function swalConfirm(title, text, onConfirm) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) onConfirm();
    });
}

function swalSuccess(title, text) {
    Swal.fire({
        title: title || 'Success!',
        text: text || '',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        timerProgressBar: true
    });
}

function swalError(title, text) {
    Swal.fire({
        title: title || 'Error',
        text: text || '',
        icon: 'error',
        confirmButtonColor: '#ef4444'
    });
}

function swalInfo(title, text) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'info',
        confirmButtonColor: '#3b82f6'
    });
}

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

// ==================== Notifications ====================
function toggleNotifications(e) {
    if (e) e.stopPropagation(); // never let this click reach the outside-click closer
    var dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    dd.classList.toggle('open');
}
function closeNotifications() {
    var dd = document.getElementById('notif-dropdown');
    if (dd) dd.classList.remove('open');
}
function markAllNotificationsRead(e) {
    if (e) e.stopPropagation();
    // Visually mark every item as read + hide the bell dot immediately
    document.querySelectorAll('#notif-list .notif-dot-unread').forEach(function(d) {
        d.classList.remove('notif-dot-unread');
        d.classList.add('notif-dot-read');
    });
    var dot = document.getElementById('notif-dot');
    if (dot) dot.style.display = 'none';
    // Persist to the server (best effort, never blocks the UI)
    try {
        api('/api/notifications', { method: 'PUT', body: { all: true } }).catch(function() {});
    } catch (err) {}
}
function loadNotifications() {
    var list = document.getElementById('notif-list');
    if (!list) return;
    api('/api/notifications').then(function(data) {
        var items = data.notifications || data;
        if (!items || !items.length) {
            list.innerHTML = '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No notifications yet</div>';
            return;
        }
        var html = '';
        items.forEach(function(n) {
            var readClass = n.is_read ? 'notif-dot-read' : 'notif-dot-unread';
            var time = '';
            if (n.created_at) {
                var diff = (Date.now() - new Date(n.created_at).getTime()) / 1000;
                if (diff < 60) time = 'Just now';
                else if (diff < 3600) time = Math.floor(diff/60) + 'm ago';
                else if (diff < 86400) time = Math.floor(diff/3600) + 'h ago';
                else time = Math.floor(diff/86400) + 'd ago';
            }
            html += '<div class="notif-item">' +
                '<div class="' + readClass + '"></div>' +
                '<div style="flex:1;min-width:0;">' +
                    '<div style="font-size:13px;font-weight:600;color:#111827;">' + (n.title || 'Notification') + '</div>' +
                    '<div style="font-size:12px;color:#94a3b8;margin-top:2px;">' + (n.message || '') + '</div>' +
                    '<div style="font-size:11px;color:#cbd5e1;margin-top:4px;">' + time + '</div>' +
                '</div>' +
            '</div>';
        });
        list.innerHTML = html;
        var dot = document.getElementById('notif-dot');
        if (dot) {
            var unread = items.filter(function(n) { return !n.is_read; }).length;
            dot.style.display = unread > 0 ? '' : 'none';
            // Pulse the dot if there are new notifications
            if (unread > 0) {
                dot.classList.add('animate-pulse');
            } else {
                dot.classList.remove('animate-pulse');
            }
        }
    }).catch(function() {
        list.innerHTML = '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No notifications</div>';
    });
}

// Start periodic notification updates (every 2 minutes)
setInterval(loadNotifications, 2 * 60 * 1000);
// Close the dropdown when clicking anywhere outside it (or the bell button)
document.addEventListener('click', function(e) {
    var dd = document.getElementById('notif-dropdown');
    if (!dd || !dd.classList.contains('open')) return;
    if (dd.contains(e.target)) return;
    var btn = document.getElementById('notif-btn');
    if (btn && btn.contains(e.target)) return;
    dd.classList.remove('open');
});
// Close with the Escape key too
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeNotifications();
});

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
                if (response.status === 401) { 
                    window.location.href = APP_BASE + 'login'; 
                    return; 
                }
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
    m.classList.add('open');
    m.style.display = 'flex';
    var inp = document.getElementById('modal-search-input');
    inp.value = query;
    inp.focus();
    if (query) performSearch(query);
}
function closeSearchModal() {
    var m = document.getElementById('search-modal');
    if (m) {
        m.classList.remove('open');
        setTimeout(function() {
            if (m && !m.classList.contains('open')) {
                m.style.display = 'none';
            }
        }, 200);
        var r = document.getElementById('search-results');
        if (r) r.innerHTML = '';
        document.body.style.overflow = '';
    }
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
    if (m) {
        m.classList.add('open');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (m) {
        m.classList.remove('open');
        setTimeout(function() {
            if (m && !m.classList.contains('open')) {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        }, 200);
    }
}
function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(function(m) {
        m.classList.remove('open');
        setTimeout(function() {
            if (m && !m.classList.contains('open')) {
                m.style.display = 'none';
            }
        }, 200);
    });
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
    api('/api/ai/chat', { method: 'POST', body: { message: msg, history: aiHistory.slice(-6) } }).then(function(data) {
        aiRemoveTyping(tid);
        aiAddMessage(data.response, 'ai', data.sources, data.confidence);
        aiHistory.push({ role: 'user', content: msg });
        aiHistory.push({ role: 'assistant', content: data.response });
        aiProcessing = false;
    }).catch(function() {
        aiRemoveTyping(tid);
        aiAddMessage('Sorry, an error occurred. Please try again.', 'ai');
        aiProcessing = false;
    });
}
var aiHistory = [];
function aiAddMessage(text, type, sources, confidence) {
    var c = document.getElementById('chat-messages');
    if (!c) return;
    var isUser = type === 'user';
    var confBadge = '';
    if (!isUser && confidence) {
        var confColor = confidence === 'high' ? '#16a34a' : confidence === 'medium' ? '#d97706' : '#dc2626';
        confBadge = '<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;background:' + confColor + '15;color:' + confColor + ';margin-left:6px;">' + confidence.toUpperCase() + '</span>';
    }
    var html = '<div style="display:flex;gap:12px;' + (isUser ? 'flex-direction:row-reverse;' : '') + 'padding-left:' + (isUser ? '44px' : '0') + ';padding-right:' + (!isUser ? '44px' : '0') + ';">' +
        '<div style="width:32px;height:32px;border-radius:10px;background:' + (isUser ? '#dbeafe' : 'linear-gradient(135deg,#8b5cf6,#6d28d9)') + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">' +
        '<i data-lucide="' + (isUser ? 'user' : 'sparkles') + '" style="width:14px;height:14px;color:' + (isUser ? '#1d4ed8' : '#fff') + ';"></i></div>' +
        '<div class="chat-bubble ' + type + '">' +
        '<div style="white-space:pre-wrap;line-height:1.7;" class="ai-response-content">' + aiFormatText(text) + '</div>';
    if (!isUser) {
        html += '<div style="margin-top:12px;padding-top:10px;border-top:1px solid #e5e7eb;display:flex;gap:6px;align-items:center;">' +
        '<button onclick="aiRate(this,\'yes\')" style="padding:4px 12px;font-size:11px;font-weight:600;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:16px;cursor:pointer;">&#10003; Helpful</button>' +
        '<button onclick="aiRate(this,\'no\')" style="padding:4px 12px;font-size:11px;font-weight:600;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:16px;cursor:pointer;">&#10007; Not helpful</button>' +
        confBadge +
        '</div>';
    }
    if (sources && sources.length) {
        html += '<div style="margin-top:8px;font-size:10px;color:#94a3b8;">Sources: ' + sources.join(', ') + '</div>';
    }
    html += '</div></div>';
    c.insertAdjacentHTML('beforeend', html);
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
}
function aiFormatText(text) {
    if (!text) return '';
    // Process line by line for lists
    var lines = text.split('\n');
    var html = '';
    var inCodeBlock = false;
    lines.forEach(function(line) {
        if (line.trim().startsWith('```')) { inCodeBlock = !inCodeBlock; html += '<pre style="padding:10px;background:#1e293b;color:#e2e8f0;border-radius:8px;font-size:12px;overflow-x:auto;margin:8px 0;"><code>' + line.replace('```', '') + '</code></pre>'; return; }
        if (inCodeBlock) { html += '<div style="padding:2px 0;font-size:12px;font-family:monospace;color:#e2e8f0;">' + line + '</div>'; return; }
        // Bullet points
        if (line.trim().startsWith('- ')) {
            html += '<div style="padding:3px 0 3px 16px;position:relative;"><span style="position:absolute;left:0;color:#2563eb;">&#8226;</span>' + aiInlineFormat(line.trim().substring(2)) + '</div>';
        } else if (/^\d+\.\s/.test(line.trim())) {
            // Numbered list
            var match = line.trim().match(/^(\d+)\.\s(.*)$/);
            if (match) html += '<div style="padding:3px 0 3px 20px;position:relative;"><span style="position:absolute;left:0;font-weight:700;color:#2563eb;font-size:12px;">' + match[1] + '.</span>' + aiInlineFormat(match[2]) + '</div>';
        } else {
            html += aiInlineFormat(line) + '\n';
        }
    });
    return html;
}
function aiInlineFormat(text) {
    return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`(.*?)`/g, '<code style="padding:2px 6px;background:#f1f5f9;border-radius:4px;font-size:12px;font-family:monospace;">$1</code>')
        .replace(/### (.*?)(\n|$)/g, '<h4 style="font-weight:700;font-size:14px;margin:12px 0 6px;">$1</h4>')
        .replace(/## (.*?)(\n|$)/g, '<h3 style="font-weight:700;font-size:15px;margin:14px 0 6px;">$1</h3>')
        .replace(/_(.*?)_/g, '<em style="color:#64748b;">$1</em>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" style="color:#2563eb;text-decoration:underline;">$1</a>');
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
    var submitBtn = form.querySelector('button[type="submit"]');
    var data = {
        title: form.querySelector('[name="title"]').value,
        content: form.querySelector('[name="content"]').value,
        category: form.querySelector('[name="category"]').value,
        tags: form.querySelector('[name="tags"]').value
    };
    if (typeof setButtonLoading === 'function') setButtonLoading(submitBtn, true, 'Submitting…');
    api('/api/documentation', { method: 'POST', body: data }).then(function() {
        if (typeof setButtonLoading === 'function') setButtonLoading(submitBtn, false);
        showToast('Documentation submitted for review!', 'success');
        form.reset();
    }).catch(function(err) {
        if (typeof setButtonLoading === 'function') setButtonLoading(submitBtn, false);
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
    var m = document.getElementById('edit-user-modal');
    if (!m) { showToast('Edit user: ' + userId, 'info'); return; }
    // Try to fetch current user data
    if (window.editUserCache && editUserCache[userId]) {
        fillEditUserForm(editUserCache[userId]);
    }
    openModal('edit-user-modal');
}
function fillEditUserForm(u) {
    var f = document.getElementById('edit-user-id');
    if (f) f.value = u.id;
    var n = document.getElementById('edit-user-name');
    if (n) n.value = u.full_name || u.name || '';
    var e = document.getElementById('edit-user-email');
    if (e) e.value = u.email || '';
    var r = document.getElementById('edit-user-role');
    if (r && u.role_id) r.value = u.role_id;
    var d = document.getElementById('edit-user-dept');
    if (d && u.department_id) d.value = u.department_id;
}
function saveEditUser() {
    var btn = document.querySelector('#edit-user-modal .btn-primary');
    if (typeof setButtonLoading === 'function') setButtonLoading(btn, true, 'Saving…');
    api('/api/users/save', {
        method: 'POST',
        body: {
            id: document.getElementById('edit-user-id').value,
            full_name: document.getElementById('edit-user-name').value,
            email: document.getElementById('edit-user-email').value,
            role_id: document.getElementById('edit-user-role').value,
            department_id: document.getElementById('edit-user-dept').value
        }
    }).then(function(data) {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        showToast(data.success ? 'User updated!' : (data.error || 'User updated!'), data.success ? 'success' : 'error');
        closeModal('edit-user-modal');
        if (data.success) setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) {
        if (typeof setButtonLoading === 'function') setButtonLoading(btn, false);
        showToast('Error: ' + err.message, 'error');
    });
}
function deleteUser(userId) {
    swalConfirm('Remove User?', 'This will permanently remove this user from the system.', function() {
        api('/api/users/delete?id=' + userId, { method: 'DELETE' }).then(function() {
            swalSuccess('User Removed', 'The user has been removed.');
            setTimeout(function() { window.location.reload(); }, 1500);
        }).catch(function(err) {
            swalError('Error', err.message);
        });
    });
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
    if (document.body) {
        var observer = new MutationObserver(function(mutations) {
            var hasNewLinks = mutations.some(function(m) {
                return m.addedNodes.length > 0;
            });
            if (hasNewLinks) fixLinks();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
})();

// ==================== Session Timeout ====================
// Enforces BOTH timeouts client-side for good UX:
//   - idle timeout (SESSION_IDLE_TIMEOUT - resets on user activity)
//   - absolute cap (SESSION_LIFETIME - cannot be extended)
// The server (Auth::enforceTimeouts) stays authoritative; this JS only warns the
// user and offers "Extend Session" before the idle window runs out.
function initSessionTimeout() {
    var sessionStartTime = parseInt(document.querySelector('meta[name="session-start-time"]')?.content) || 0;
    var sessionLifetime  = parseInt(document.querySelector('meta[name="session-lifetime"]')?.content) || 28800;
    var sessionLastSeen  = parseInt(document.querySelector('meta[name="session-last-activity"]')?.content) || sessionStartTime;
    var idleTimeout      = parseInt(document.querySelector('meta[name="session-idle-timeout"]')?.content) || 1800;
    var warningTime      = 5 * 60; // warn 5 minutes before either expiry

    if (sessionStartTime === 0) return;

    var nowSec = function() { return Math.floor(Date.now() / 1000); };
    var lastActivity = Math.max(sessionLastSeen, nowSec());

    // Track genuine user activity (the server remains the source of truth).
    ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'].forEach(function(ev) {
        document.addEventListener(ev, function() { lastActivity = nowSec(); }, { passive: true });
    });

    function checkSession() {
        var now = nowSec();
        var idleRemaining = idleTimeout - (now - lastActivity);
        var absRemaining  = sessionLifetime - (now - sessionStartTime);
        var remaining     = Math.min(idleRemaining, absRemaining);

        if (remaining <= 0) {
            logoutDueToInactivity(absRemaining <= 0);
            return;
        }
        if (remaining <= warningTime) {
            showSessionWarning(remaining);
        }
    }

    function showSessionWarning(secondsLeft) {
        if (document.getElementById('session-timeout-warning')) return;

        var minutesLeft = Math.ceil(secondsLeft / 60);
        Swal.fire({
            title: 'Session Expiring Soon',
            html: 'Your session will expire in <strong>' + minutesLeft + ' minute' + (minutesLeft !== 1 ? 's' : '') + '</strong> for security reasons.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Extend Session',
            cancelButtonText: 'Log Out Now',
            timer: secondsLeft * 1000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.getTimerLeft().then(function(timeLeft) {
                    if (timeLeft) {
                        var strong = Swal.getHtmlContainer().querySelector('strong');
                        if (strong) strong.textContent = Math.ceil(timeLeft / 60000) + ' minute' + (Math.ceil(timeLeft / 60000) !== 1 ? 's' : '');
                    }
                });
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                // Extend only the idle window - the absolute cap stays fixed.
                extendSession().then(function() {
                    lastActivity = nowSec();
                    var meta = document.querySelector('meta[name="session-last-activity"]');
                    if (meta) meta.content = lastActivity;
                    Swal.fire({
                        title: 'Session Extended',
                        text: 'Your session has been refreshed.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }).catch(function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to extend session. Please log in again.',
                        icon: 'error'
                    }).then(function() {
                        window.location.href = APP_BASE + 'login';
                    });
                });
            } else {
                logoutDueToInactivity();
            }
        });
    }

    function logoutDueToInactivity(absolute) {
        Swal.fire({
            title: 'Session Expired',
            text: absolute
                ? 'Your session has reached its maximum length. Please log in again.'
                : 'Your session has expired due to inactivity. Please log in again.',
            icon: 'info',
            confirmButtonText: 'Log In'
        }).then(function() {
            window.location.href = APP_BASE + 'login';
        });
    }

    function extendSession() {
        // The global CSRF interceptor auto-adds the X-CSRF-Token header.
        return fetch(APP_BASE + 'api/session/extend', {
            method: 'POST',
            credentials: 'same-origin'
        }).then(function(response) {
            if (!response.ok) throw new Error('Failed to extend session');
            return response.json();
        });
    }

    // Check periodically and on user activity.
    setInterval(checkSession, 30 * 1000);
    document.addEventListener('mousemove', checkSession);
    document.addEventListener('keypress', checkSession);
    document.addEventListener('click', checkSession);
}

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
    // Load notifications if dropdown exists
    if (document.getElementById('notif-list')) loadNotifications();
    // Init session timeout
    initSessionTimeout();
    // Start heartbeat for keeping session alive and updating critical data
    startHeartbeat();
    // Add live indicator
    addLiveIndicator();
});

// ==================== Live Indicator ====================
function addLiveIndicator() {
    var headerActions = document.querySelector('.header-actions');
    if (!headerActions) return;
    
    var liveIndicator = document.createElement('div');
    liveIndicator.className = 'live-indicator';
    liveIndicator.innerHTML = '<i data-lucide="radio" style="width:12px;height:12px;"></i>';
    liveIndicator.title = 'Live updates enabled';
    liveIndicator.style.cssText = 'display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:rgba(16,185,129,0.2);color:#10b981;font-size:10px;margin-left:8px;position:relative;overflow:hidden;';
    
    // Add pulse animation
    liveIndicator.innerHTML += '<style>@keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); } 70% { box-shadow: 0 0 0 8px rgba(16,185,129,0); } 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); } } .live-indicator { animation: livePulse 2s ease-in-out infinite; }</style>';
    
    headerActions.insertBefore(liveIndicator, headerActions.firstChild);
    
    // Initialize Lucide icons for the new element
    try { lucide.createIcons({ nodes: [liveIndicator] }); } catch(e) {}
}

// ==================== Heartbeat System ====================
function startHeartbeat() {
    // Track visibility state
    var isVisible = !document.hidden;
    
    // Handle visibility changes
    document.addEventListener('visibilitychange', function() {
        isVisible = !document.hidden;
        if (!isVisible) {
            // Pause non-essential updates when tab is hidden
            pauseNonEssentialUpdates();
        } else {
            // Resume updates when tab becomes visible
            resumeNonEssentialUpdates();
        }
    });
    
    // Ping server every 2 minutes to keep session alive during active use
    setInterval(function() {
        // Only send heartbeat if user has interacted recently (last 30 seconds) AND tab is visible
        if (Date.now() - (window.lastUserActivity || 0) < 30000 && isVisible) {
            fetch(APP_BASE + '/api/heartbeat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            }).catch(() => {}); // Ignore errors
        }
    }, 2 * 60 * 1000); // 2 minutes
    
    // Track user activity
    ['mousemove', 'keypress', 'click', 'scroll'].forEach(function(event) {
        document.addEventListener(event, function() {
            window.lastUserActivity = Date.now();
        });
    });
    
    // Update ticket counts periodically if on tickets page
    if (document.body.classList.contains('tickets-page')) {
        ticketCountsInterval = setInterval(updateTicketCounts, 60 * 1000); // Every minute
    }
    
    // Update knowledge base stats if on KB page
    if (document.body.classList.contains('kb-page')) {
        kbStatsInterval = setInterval(updateKBStats, 5 * 60 * 1000); // Every 5 minutes
    }
}

// ==================== Pause/Resume Non-Essential Updates ====================
function pauseNonEssentialUpdates() {
    if (window.ticketCountsInterval) {
        clearInterval(window.ticketCountsInterval);
        window.ticketCountsInterval = null;
    }
    if (window.kbStatsInterval) {
        clearInterval(window.kbStatsInterval);
        window.kbStatsInterval = null;
    }
}

function resumeNonEssentialUpdates() {
    // Resume ticket counts if on tickets page
    if (document.body.classList.contains('tickets-page') && !window.ticketCountsInterval) {
        window.ticketCountsInterval = setInterval(updateTicketCounts, 60 * 1000);
    }
    
    // Resume KB stats if on KB page
    if (document.body.classList.contains('kb-page') && !window.kbStatsInterval) {
        window.kbStatsInterval = setInterval(updateKBStats, 5 * 60 * 1000);
    }
}

// ==================== Heartbeat API Endpoint ====================
// This would need a corresponding API endpoint at /api/heartbeat
// For now, we'll just comment that it needs to be created
/*
function createHeartbeatEndpoint() {
    // This would be implemented in /api/heartbeat.php
    // It would just return a 200 OK to keep the session alive
}
*/

// ==================== Ticket Count Updates ====================
function updateTicketCounts() {
    // Only update if we're on the tickets page and not currently viewing a modal
    if (!document.body.classList.contains('tickets-page') || 
        document.querySelector('.modal-overlay.open')) return;
        
    api('/api/tickets/counts').then(function(data) {
        // Update badge counts in sidebar
        var pendingBadge = document.querySelector('.sidebar-link[url*="/tickets"] .badge');
        if (pendingBadge && data.pending !== undefined) {
            pendingBadge.textContent = data.pending;
        }
        
        // Update any dashboard-style counters if they exist
        var pendingCountEl = document.getElementById('pending-ticket-count');
        if (pendingCountEl) pendingCountEl.textContent = data.pending || 0;
        
        var resolvedCountEl = document.getElementById('resolved-ticket-count');
        if (resolvedCountEl) resolvedCountEl.textContent = data.resolved || 0;
    }).catch(() => {}); // Silently fail
}

// ==================== KB Stats Updates ====================
function updateKBStats() {
    // Only update if we're on the KB page
    if (!document.body.classList.contains('kb-page')) return;
    
    api('/api/knowledge/stats').then(function(data) {
        // Update KB stats if elements exist
        var articlesCountEl = document.getElementById('kb-articles-count');
        if (articlesCountEl) articlesCountEl.textContent = data.articles || 0;
        
        var pendingReviewEl = document.getElementById('kb-pending-review');
        if (pendingReviewEl) pendingReviewEl.textContent = data.pending_review || 0;
    }).catch(() => {}); // Silently fail
}

// ==================== Reveal on Scroll + Count-up FX ====================
document.documentElement.classList.add('js');

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var els = document.querySelectorAll('.fx-reveal');
        if (!('IntersectionObserver' in window)) {
            els.forEach(function(el) { el.classList.add('fx-in'); animateFx(el); });
            return;
        }
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                el.classList.add('fx-in');
                animateFx(el);
                io.unobserve(el);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -24px 0px' });
        els.forEach(function(el) { io.observe(el); });
    });
})();

function animateFx(scope) {
    if (!scope || scope.nodeType !== 1) return;

    // Gentle count-up for [data-count] numbers
    var counters = scope.hasAttribute('data-count') ? [scope] : (scope.querySelectorAll ? scope.querySelectorAll('[data-count]') : []);
    Array.prototype.forEach.call(counters, function(el, i) {
        var target = parseFloat(el.getAttribute('data-count')) || 0;
        var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var duration = 1100;
        var start = null;
        function tick(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            var val = target * eased;
            el.textContent = decimals > 0 ? val.toFixed(decimals) : Math.round(val).toLocaleString();
            if (p < 1) requestAnimationFrame(tick);
        }
        setTimeout(function() { requestAnimationFrame(tick); }, i * 130);
    });

    // Grow .bar-chart-fill bars from 0 to their inline width
    var bars = scope.querySelectorAll ? scope.querySelectorAll('.bar-chart-fill') : [];
    Array.prototype.forEach.call(bars, function(bar) {
        var target = bar.style.width;
        bar.style.transition = 'none';
        bar.style.width = '0%';
        void bar.offsetWidth;
        bar.style.transition = '';
        bar.style.width = target;
    });
}

// ==================== Loading Buttons + Skeleton Helpers ====================
// Show a spinner + label on a button while async work runs.
function setButtonLoading(btn, loading, loadingLabel) {
    if (!btn || !btn.style) return;
    if (loading) {
        if (!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('btn-loading');
        btn.innerHTML = '<span class="btn-spinner"></span>' + (loadingLabel || 'Processing…');
    } else {
        btn.disabled = false;
        btn.classList.remove('btn-loading');
        if (btn.dataset.origHtml) { btn.innerHTML = btn.dataset.origHtml; delete btn.dataset.origHtml; }
    }
}

// Build a reusable skeleton card (for JS-loaded lists / panels).
function skeletonCard(lines) {
    var s = '<div class="skeleton-card" style="margin-bottom:12px;">';
    s += '<div style="display:flex;gap:12px;align-items:flex-start;">';
    s += '<div class="skeleton-shine skeleton-circle"></div>';
    s += '<div style="flex:1;">';
    for (var i = 0; i < (lines || 3); i++) {
        s += '<div class="skeleton-shine skeleton-line' + (i === lines - 1 ? ' sm' : '') + '"></div>';
    }
    s += '</div></div></div>';
    return s;
}

// Helper: show skeletons in a list container until real content is rendered.
function skeletonFill(container, count, lines) {
    if (!container) return;
    var html = '';
    for (var i = 0; i < (count || 3); i++) html += skeletonCard(lines || 3);
    container.innerHTML = html;
}

// Build a responsive grid of skeleton cards (for card-grid pages).
function skeletonGrid(count, cols) {
    var s = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(' + (cols || 260) + 'px,1fr));gap:14px;">';
    for (var i = 0; i < (count || 6); i++) {
        s += '<div class="skeleton-card"><div class="skeleton-shine" style="height:96px;margin:-20px -20px 14px;border-radius:15px 15px 0 0;"></div>' +
             '<div class="skeleton-shine skeleton-line"></div><div class="skeleton-shine skeleton-line sm"></div>' +
             '<div class="skeleton-shine skeleton-pill" style="margin-top:12px;"></div></div>';
    }
    return s + '</div>';
}

// Auto loading buttons: any <form data-auto-loading> shows a spinner on its
// submit button automatically while the (native) submission is in flight.
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form || !form.hasAttribute || !form.hasAttribute('data-auto-loading')) return;
    var btn = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
    if (btn && typeof setButtonLoading === 'function') setButtonLoading(btn, true, btn.getAttribute('data-loading-label') || 'Saving…');
}, true);

// Auto-enhance: every <form> submit shows a spinner on its submit button (once).
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (form.dataset.noSpinner) return;
    var btn = form.querySelector('button[type="submit"], .btn-submit');
    if (btn && !btn.disabled) setButtonLoading(btn, true, btn.dataset.loading || 'Saving…');
}, true);
