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

// ==================== String helpers ====================
function escHtml(s) {
    if (s == null) return '';
    s = String(s);
    return s.replace(/[&<>\'"]/g, function(c) {
        if (c === '&') return '&amp;';
        if (c === '<') return '&lt;';
        if (c === '>') return '&gt;';
        if (c === '\'') return '&#39;';
        if (c === '"') return '&quot;';
        return c;
    });
}

// ==================== SweetAlert2 Helpers ====================
function swalConfirm(title, text, onConfirm) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Confirm',
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
    // Clean up the ticket map when the new-ticket modal closes (avoid duplicates + memory leak)
    if (id === 'new-ticket-modal') {
        var mapEl = document.getElementById('tt-map');
        if (ttMapInstance && mapEl) {
            try { ttMapInstance.remove(); } catch(e) {}
            ttMapInstance = null;
            ttMapMarker = null;
            ttMapMarkerEnd = null;
        }
        if (mapEl) mapEl.innerHTML = '';
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

// ==================== Ticket Filters / Search / Sort ====================
function filterTickets(status) { ticketFilter(status); }
function ticketFilter(status) {
    window.ticketActiveFilter = status;
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filter === status);
    });
    ticketApplyFilters();
}

// Combined filter (status chips) + search + sort over the ticket card grid.
function ticketApplyFilters() {
    var searchEl = document.getElementById('ticket-search');
    var sortEl   = document.getElementById('ticket-sort');
    var grid     = document.getElementById('tickets-grid');
    if (!grid) return;
    var q = searchEl ? searchEl.value.toLowerCase().trim() : '';
    var filter = window.ticketActiveFilter || ticketDefaultFilter();
    var sort = sortEl ? sortEl.value : 'newest';
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.ft-ticket-card'));
    cards.forEach(function(card) {
        var okFilter = card.dataset.status === filter;
        var okSearch = !q || (card.dataset.search || '').indexOf(q) !== -1;
        card.style.display = (okFilter && okSearch) ? '' : 'none';
    });
    cards.sort(function(a, b) {
        var ca = parseInt(a.dataset.created || '0', 10);
        var cb = parseInt(b.dataset.created || '0', 10);
        var ua = parseInt(a.dataset.updated || '0', 10);
        var ub = parseInt(b.dataset.updated || '0', 10);
        if (sort === 'nearest') {
            var da = parseFloat(a.dataset.distanceMeters || 'Infinity');
            var db = parseFloat(b.dataset.distanceMeters || 'Infinity');
            return da - db;
        }
        if (sort === 'oldest') return ca - cb;
        if (sort === 'updated') return ub - ua;
        return cb - ca; // newest
    });
    cards.forEach(function(card) { grid.appendChild(card); });
    if (window.ticketTravelLoaded) ticketMarkFirstStop();
}

function ticketDefaultFilter() {
    var grid = document.getElementById('tickets-grid');
    if (!grid) return 'new';
    if (grid.querySelector('.ft-ticket-card[data-status="new"]')) return 'new';
    if (grid.querySelector('.ft-ticket-card[data-status="in_progress"]')) return 'in_progress';
    if (grid.querySelector('.ft-ticket-card[data-status="escalated"]')) return 'escalated';
    return 'new';
}

function ticketInitDefaultFilter() {
    var filter = ticketDefaultFilter();
    window.ticketActiveFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });
    ticketApplyFilters();
    ticketInitTravelEstimates();
}

function ticketTravelFormatDuration(seconds) {
    var mins = Math.max(1, Math.round(Number(seconds || 0) / 60));
    if (mins < 60) return mins + ' min';
    var hours = Math.floor(mins / 60);
    var rest = mins % 60;
    return hours + ' hr' + (rest ? ' ' + rest + ' min' : '');
}

function ticketTravelFallback(origin, destination) {
    var rad = Math.PI / 180;
    var dLat = (destination.lat - origin.lat) * rad;
    var dLng = (destination.lng - origin.lng) * rad;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(origin.lat * rad) * Math.cos(destination.lat * rad) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    var straightKm = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var roadKm = straightKm * 1.28;
    return { distance: roadKm * 1000, duration: (roadKm / 32) * 3600, approximate: true };
}

function ticketRenderTravel(card, route) {
    if (!card || !route) return;
    card.dataset.distanceMeters = String(route.distance);
    var box = document.getElementById('ticket-travel-' + card.dataset.id);
    if (!box) return;
    var distance = box.querySelector('.ft-travel-distance');
    var eta = box.querySelector('.ft-travel-eta');
    var km = Number(route.distance || 0) / 1000;
    if (distance) distance.textContent = (route.approximate ? '~' : '') + (km < 10 ? km.toFixed(1) : Math.round(km)) + ' km away';
    if (eta) {
        var arrival = new Date(Date.now() + (Number(route.duration || 0) * 1000));
        eta.textContent = 'Arrive ' + arrival.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) + ' · ' + ticketTravelFormatDuration(route.duration);
    }
}

function ticketMarkFirstStop() {
    document.querySelectorAll('.ft-ticket-card').forEach(function(card) { delete card.dataset.routeRank; });
    var visible = Array.prototype.slice.call(document.querySelectorAll('.ft-ticket-card')).filter(function(card) {
        return card.dataset.status === (window.ticketActiveFilter || ticketDefaultFilter()) && isFinite(parseFloat(card.dataset.distanceMeters));
    }).sort(function(a, b) { return parseFloat(a.dataset.distanceMeters) - parseFloat(b.dataset.distanceMeters); });
    if (visible[0]) visible[0].dataset.routeRank = '1';
}

function ticketInitTravelEstimates() {
    if (window.ticketTravelLoading || window.ticketTravelLoaded) return;
    var origin = { lat: parseFloat((window.ttProfileTicketDefault || {}).lat), lng: parseFloat((window.ttProfileTicketDefault || {}).lng) };
    if (!isFinite(origin.lat) || !isFinite(origin.lng)) return;
    var cards = Array.prototype.slice.call(document.querySelectorAll('.ft-ticket-card')).filter(function(card) {
        return isFinite(parseFloat(card.dataset.lat)) && isFinite(parseFloat(card.dataset.lng));
    });
    if (!cards.length) return;
    window.ticketTravelLoading = true;
    var batches = [];
    for (var i = 0; i < cards.length; i += 40) batches.push(cards.slice(i, i + 40));
    Promise.all(batches.map(function(batch) {
        var coords = [origin.lng + ',' + origin.lat].concat(batch.map(function(card) { return card.dataset.lng + ',' + card.dataset.lat; }));
        var destinations = batch.map(function(_, index) { return index + 1; }).join(';');
        var url = 'https://router.project-osrm.org/table/v1/driving/' + coords.join(';') + '?sources=0&destinations=' + destinations + '&annotations=distance,duration';
        return fetch(url).then(function(response) {
            if (!response.ok) throw new Error('Routing unavailable');
            return response.json();
        }).then(function(data) {
            batch.forEach(function(card, index) {
                var distance = data.distances && data.distances[0] ? data.distances[0][index] : null;
                var duration = data.durations && data.durations[0] ? data.durations[0][index] : null;
                if (distance != null && duration != null) ticketRenderTravel(card, { distance: distance, duration: duration });
                else ticketRenderTravel(card, ticketTravelFallback(origin, { lat: parseFloat(card.dataset.lat), lng: parseFloat(card.dataset.lng) }));
            });
        }).catch(function() {
            batch.forEach(function(card) {
                ticketRenderTravel(card, ticketTravelFallback(origin, { lat: parseFloat(card.dataset.lat), lng: parseFloat(card.dataset.lng) }));
            });
        });
    })).then(function() {
        window.ticketTravelLoading = false;
        window.ticketTravelLoaded = true;
        ticketApplyFilters();
        ticketMarkFirstStop();
    });
}

function ticketSuggestionBulk(action) {
    var checked = Array.prototype.slice.call(document.querySelectorAll('#ticket-suggestions-list input[type="checkbox"]:checked'));
    var ids = checked.map(function(cb) { return parseInt(cb.value, 10); }).filter(Boolean);
    if (!ids.length) { showToast('Select at least one suggestion first.', 'warning'); return; }
    api('/api/tickets/suggestions', { method: 'POST', body: { action: action, ids: ids } })
        .then(function(res) {
            if (!res.success) { showToast(res.error || 'Suggestion update failed.', 'error'); return; }
            showToast(action === 'approve' ? 'Suggestion approved.' : 'Suggestions deleted.', 'success');
            setTimeout(function() { window.location.reload(); }, 500);
        })
        .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

function ticketApprovalTab(kind, button) {
    document.querySelectorAll('.tt-approval-tab').forEach(function(tab) { tab.classList.toggle('active', tab === button); });
    document.querySelectorAll('.tt-approval-pane').forEach(function(pane) {
        pane.classList.toggle('active', pane.dataset.approvalPane === kind);
    });
}

function ticketApprovalBulk(kind, action) {
    var list = document.getElementById('ticket-approval-list-' + kind);
    if (!list) return;
    var ids = Array.prototype.slice.call(list.querySelectorAll('input[type="checkbox"]:checked'))
        .map(function(cb) { return parseInt(cb.value, 10); }).filter(Boolean);
    if (!ids.length) { showToast('Select at least one item first.', 'warning'); return; }
    api('/api/tickets/approvals', { method: 'POST', body: { kind: kind, action: action, ids: ids } })
        .then(function(res) {
            if (!res.success) { showToast(res.error || 'Approval update failed.', 'error'); return; }
            showToast(action === 'approve' ? 'Selected entries approved.' : 'Selected entries deleted.', 'success');
            setTimeout(function() { window.location.reload(); }, 500);
        })
        .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

function ticketStepReview(id, action, problemSelectId) {
    var issueId = 0;
    if (problemSelectId) {
        var problemSelect = document.getElementById(problemSelectId);
        issueId = problemSelect ? parseInt(problemSelect.value || '0', 10) : 0;
        if (!issueId) { showToast('Select the correct problem first.', 'warning'); return; }
    }
    api('/api/tickets/approvals', { method: 'POST', body: { kind: 'checklist', action: action, ids: [id], issue_id: issueId } })
        .then(function(res) {
            if (!res.success) { showToast(res.error || 'Checklist review failed.', 'error'); return; }
            if (res.duplicate) {
                showToast(res.message || 'That step already exists for this problem.', 'warning');
            } else {
                showToast(action === 'approve' ? 'Step approved and added to this problem.' : 'Step removed from the approval queue.', 'success');
            }
            setTimeout(function() { window.location.reload(); }, 500);
        })
        .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
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
function openNewTicketModal() {
    if (typeof wireNewTicketModal === 'function') {
        wireNewTicketModal();
    }
    var stepDevice = document.getElementById('step-device');
    var stepProblem = document.getElementById('step-problem-loc');
    var next1 = document.getElementById('tt-next-1');
    if (stepDevice) stepDevice.style.display = '';
    if (stepProblem) stepProblem.style.display = 'none';
    if (next1) {
        next1.disabled = false;
        next1.textContent = 'Next: Problem & Location';
    }
    ttApplyProfileDefaults();
    openModal('new-ticket-modal');
}
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
}function ticketEscalate(ticketId) {
    api('/api/tickets/action', { method: 'POST', body: { action: 'escalate', id: ticketId } }).then(function() {
        showToast('Ticket escalated to supervisor!', 'warning');
        setTimeout(function() { window.location.reload(); }, 1000);
    }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ==================== New Ticket: two-step modal ====================
// Step 1 (ticket + company + task + device picked from the real `equipment` table),
// Step 2 (problem picked from troubleshooting issues — or typed by hand — plus a
// note and the address). The final submit POSTs to /api/tickets/timein.
var ttEqData = [];          // equipment rows injected by the page
var ttIssueData = [];       // troubleshooting issues injected by the page
var ttCompanyData = [];     // past tickets: {company, address, lat, lng} rows injected by the page
var ttTaskData = [];        // previously used task text injected by the page
var ttProfileTicketDefault = {};
var ttIssueFieldOptions = {};
var ttCompanyContacts = {};
var ttSelectedEquipmentId = 0;
var ttSelectedDeviceType = '';
var ttSelectedMfrName = '';
var ttSelectedModelName = '';
var ttSelectedSerial = '';
var ttSelectedAsset = '';
var ttSelectedLocation = '';
var ttMapInstance = null;
var ttMapMarker = null;
var ttMapMarkerEnd = null;
var ttUserLocation = null;

// ---------- step navigation ----------
function ticketStepBack() {
    var step1 = document.getElementById('step-device');
    var step2 = document.getElementById('step-problem-loc');
    if (step1) step1.style.display = '';
    if (step2) step2.style.display = 'none';
    var btn = document.getElementById('tt-next-1');
    if (btn) { btn.disabled = false; btn.textContent = 'Next: Problem & Location'; }
}

function ticketGoToStep2() {
    var ticketNo = (document.getElementById('tt-ticket-no').value || '').trim();
    var company = ttCompanyValue();
    var task = ttTaskValue();
    var device = (ttSelectedDeviceType || '').trim();
    if (!/^\d+$/.test(ticketNo)) { showToast('Type the numeric ticket number. SD is added automatically.', 'warning'); return; }
    if (!company) { showToast('Please type the company name.', 'warning'); return; }
    if (!task) { showToast('Please fill in the task.', 'warning'); return; }
    if (!device) { showToast('Please select a device first.', 'warning'); return; }
    // Ticket title: the equipment is the hero, the task is the fallback.
    var title = ttSelectedModelName ? ((ttSelectedMfrName + ' ' + ttSelectedModelName).trim()) : task;
    document.getElementById('tt-title').value = title;
    document.getElementById('tt-description').value = task;

    document.getElementById('step-device').style.display = 'none';
    document.getElementById('step-problem-loc').style.display = '';
    var btn = document.getElementById('tt-next-1');
    if (btn) { btn.disabled = true; btn.textContent = 'Step 1 complete'; }

    ttPopulateIssues();
    ttRenderCompanyDatalist();
    ttRenderAddressDatalist();
    initTicketMap();
}

// The ticket number + company are typed by hand (datalist suggests known companies).
function ttCompanyValue() {
    var sel = document.getElementById('tt-company');
    if (!sel) return '';
    if (sel.value === '__OTHER__') {
        var other = document.getElementById('tt-company-other');
        return other ? (other.value || '').trim() : '';
    }
    return (sel.value || '').trim();
}

function ttTaskValue() {
    var sel = document.getElementById('tt-task');
    if (!sel) return '';
    if (sel.value === '__OTHER__') {
        var other = document.getElementById('tt-task-other');
        return other ? (other.value || '').trim() : '';
    }
    return (sel.value || '').trim();
}

function ttApprovedSelectValues(id) {
    var sel = document.getElementById(id);
    var values = [];
    if (!sel) return values;
    Array.prototype.forEach.call(sel.options, function(opt) {
        if (opt.value && opt.value !== '__OTHER__') values.push(opt.value);
    });
    return values;
}

function ttFindApprovedValue(id, value) {
    var wanted = ttNormalizeName(value);
    if (!wanted) return '';
    var values = ttApprovedSelectValues(id);
    for (var i = 0; i < values.length; i++) {
        if (ttNormalizeName(values[i]) === wanted) return values[i];
    }
    return '';
}

function ttSuggestionChanged(kind) {
    var isCompany = kind === 'company';
    var selectId = isCompany ? 'tt-company' : 'tt-task';
    var otherId = isCompany ? 'tt-company-other' : 'tt-task-other';
    var hintId = isCompany ? 'tt-company-hint' : 'tt-task-hint';
    var sel = document.getElementById(selectId);
    var other = document.getElementById(otherId);
    var hint = document.getElementById(hintId);
    if (!sel || !other) return;
    var isOther = sel.value === '__OTHER__';
    other.style.display = isOther ? '' : 'none';
    if (isOther) {
        other.focus();
        if (hint) {
            hint.textContent = 'New ' + (isCompany ? 'company' : 'task') + ' names are saved as pending until a manager/admin approves them.';
            hint.style.display = '';
        }
    } else if (hint) {
        hint.style.display = 'none';
        hint.textContent = '';
    }
    if (isCompany) {
        ttRenderAddressDatalist();
        ttApplyCompanyDefaultLocation();
    }
}

function ttApplyProfileDefaults() {
    var d = ttProfileTicketDefault || {};
    var company = document.getElementById('tt-company');
    var companyOther = document.getElementById('tt-company-other');
    if (company && d.company && !ttCompanyValue()) {
        var match = ttFindApprovedValue('tt-company', d.company);
        company.value = match || '__OTHER__';
        if (!match && companyOther) { companyOther.value = d.company; companyOther.style.display = ''; }
    }
    var location = document.getElementById('tt-location');
    var address = document.getElementById('tt-address');
    var lat = document.getElementById('tt-lat');
    var lng = document.getElementById('tt-lng');
    if (location && !location.value && d.location) location.value = d.location;
    if (address && !address.value && d.address) address.value = d.address;
    if (lat && !lat.value && d.lat) lat.value = d.lat;
    if (lng && !lng.value && d.lng) lng.value = d.lng;
}

function ttApplyCompanyDefaultLocation() {
    var company = ttNormalizeName(ttCompanyValue());
    if (!company) return;
    var row = null;
    for (var i = 0; i < ttCompanyData.length; i++) {
        if (ttNormalizeName(ttCompanyData[i].company) === company && ttCompanyData[i].address) { row = ttCompanyData[i]; break; }
    }
    if (!row) return;
    var address = document.getElementById('tt-address');
    var lat = document.getElementById('tt-lat');
    var lng = document.getElementById('tt-lng');
    if (address) address.value = row.address || '';
    if (lat) lat.value = row.lat || '';
    if (lng) lng.value = row.lng || '';
    if (row.lat && row.lng) ttPlaceMapMarkerEnd(row.lat, row.lng);
}

function ttOtherSuggestionTyped(kind) {
    var isCompany = kind === 'company';
    var selectId = isCompany ? 'tt-company' : 'tt-task';
    var otherId = isCompany ? 'tt-company-other' : 'tt-task-other';
    var hintId = isCompany ? 'tt-company-hint' : 'tt-task-hint';
    var other = document.getElementById(otherId);
    var hint = document.getElementById(hintId);
    var match = ttFindApprovedValue(selectId, other ? other.value : '');
    if (hint) {
        if (match) {
            hint.textContent = '"' + match + '" is already approved. Use the dropdown item instead.';
            hint.style.display = '';
        } else if (other && other.value.trim()) {
            hint.textContent = 'This will be pending approval after ticket creation.';
            hint.style.display = '';
        }
    }
}

// ---------- saved companies + addresses (from past tickets) ----------
// The Company field suggests every company used before; the Address field then
// offers only the addresses recorded for that company (a company can have several
// branches/locations). Both fields still accept brand-new typed values.
function ttNormalizeName(v) {
    return String(v || '').toLowerCase().replace(/\s+/g, ' ').replace(/[^a-z0-9&.\- ]/g, '').trim();
}

function ttCompanyOptions() {
    var seen = {}, out = [];
    for (var i = 0; i < ttCompanyData.length; i++) {
        var c = String(ttCompanyData[i].company || '').trim();
        if (!c) continue;
        var k = ttNormalizeName(c);
        if (k && !seen[k]) { seen[k] = true; out.push(c); }
    }
    return out;
}

function ttRenderCompanyDatalist() {
    var dl = document.getElementById('tt-company-list');
    if (!dl) return;
    // Merge the server-rendered options (organizations table) with the
    // companies seen in past tickets, so nothing is lost on re-render.
    var seen = {}, names = [];
    var existing = dl.querySelectorAll('option');
    for (var i = 0; i < existing.length; i++) {
        var v = String(existing[i].value || '').trim();
        if (!v) continue;
        var k = ttNormalizeName(v);
        if (k && !seen[k]) { seen[k] = true; names.push(v); }
    }
    var hist = ttCompanyOptions();
    for (var j = 0; j < hist.length; j++) {
        var hk = ttNormalizeName(hist[j]);
        if (hk && !seen[hk]) { seen[hk] = true; names.push(hist[j]); }
    }
    var html = '';
    for (var n = 0; n < names.length; n++) {
        html += '<option value="' + escHtml(names[n]) + '"></option>';
    }
    dl.innerHTML = html;
}

function ttRenderAddressDatalist() {
    var dl = document.getElementById('tt-address-list');
    if (!dl) return;
    var comp = ttNormalizeName(ttCompanyValue());
    var seen = {}, out = [];
    for (var i = 0; i < ttCompanyData.length; i++) {
        var row = ttCompanyData[i];
        if (!comp || ttNormalizeName(row.company) !== comp) continue;
        var a = String(row.address || '').trim();
        if (a) {
            var k = a.toLowerCase();
            if (!seen[k]) { seen[k] = true; out.push({ address: a, lat: row.lat, lng: row.lng }); }
        }
    }
    var html = '';
    for (var j = 0; j < out.length; j++) {
        html += '<option value="' + escHtml(out[j].address) + '"></option>';
    }
    dl.innerHTML = html;          // <option> per saved address for this company
    dl.dataset.addrCount = String(out.length);
    dl._addrRows = out;   // remember lat/lng per address for ttApplySavedAddress
    return out;
}

function ttFindSavedAddress(comp, addr) {
    var c = ttNormalizeName(comp), a = String(addr || '').trim().toLowerCase();
    if (!c || !a) return null;
    for (var i = 0; i < ttCompanyData.length; i++) {
        var row = ttCompanyData[i];
        if (ttNormalizeName(row.company) === c && String(row.address || '').trim().toLowerCase() === a) {
            return row;
        }
    }
    return null;
}

// Picking (or matching) a saved address restores its coordinates + drops the map pin.
function ttApplySavedAddress() {
    var addrInput = document.getElementById('tt-address');
    if (!addrInput) return;
    var addr = (addrInput.value || '').trim();
    var saved = ttFindSavedAddress(ttCompanyValue(), addr);
    if (saved && saved.lat && saved.lng) {
        var latInput = document.getElementById('tt-lat');
        var lngInput = document.getElementById('tt-lng');
        if (latInput) latInput.value = saved.lat;
        if (lngInput) lngInput.value = saved.lng;
        ttPlaceMapMarkerEnd(saved.lat, saved.lng);
    }
}

// If the map/tap set a brand-new address, keep it for the next ticket of this company.
function ttRememberAddress(comp, addr, lat, lng) {
    comp = String(comp || '').trim(); addr = String(addr || '').trim();
    if (!comp || !addr) return;
    var c = ttNormalizeName(comp), a = addr.toLowerCase();
    for (var i = 0; i < ttCompanyData.length; i++) {
        if (ttNormalizeName(ttCompanyData[i].company) === c && String(ttCompanyData[i].address || '').trim().toLowerCase() === a) {
            if (lat && lng && !ttCompanyData[i].lat) { ttCompanyData[i].lat = lat; ttCompanyData[i].lng = lng; }
            return;
        }
    }
    ttCompanyData.push({ company: comp, address: addr, lat: lat || '', lng: lng || '' });
}

// ---------- equipment list (fed by the `equipment` table) ----------
function ttRenderEquipResults() {
    var box = document.getElementById('tt-equip-results');
    if (!box) return;
    var searchEl = document.getElementById('tt-equip-search');
    var q = searchEl ? (searchEl.value || '').trim().toLowerCase() : '';
    var selectedDevice = (ttSelectedDeviceType || '').trim();

    if (!ttEqData.length) {
        box.innerHTML = '<div style="padding:14px;font-size:12px;color:#94a3b8;">No equipment records available — type the model manually below.</div>';
        box.style.display = 'block';
        return;
    }

    var rows = [];
    for (var i = 0; i < ttEqData.length; i++) {
        var e = ttEqData[i];
        if (selectedDevice && !ttSameDeviceType(selectedDevice, e.device_type || '')) continue;
        if (q) {
            var hay = ((e.manufacturer || '') + ' ' + (e.model_name || '') + ' ' + (e.serial_number || '') + ' ' + (e.asset_tag || '') + ' ' + (e.location || '')).toLowerCase();
            if (hay.indexOf(q) === -1) continue;
        }
        rows.push(e);
        if (rows.length >= 60) break;
    }

    if (!rows.length) {
        box.innerHTML = '<div style="padding:14px;font-size:12px;color:#94a3b8;">No ' + escHtml(selectedDevice || 'equipment') + ' matches yet — search again or type the model manually below.</div>';
        box.style.display = 'block';
        return;
    }

    var html = '<div style="padding:6px 8px;font-size:11px;color:#94a3b8;border-bottom:1px solid #f1f5f9;">' + rows.length + (selectedDevice ? ' ' + escHtml(selectedDevice) : '') + ' equipment match' + (rows.length === 1 ? '' : 'es') + ' — click to select</div>';
    for (var j = 0; j < rows.length; j++) {
        var r = rows[j];
        html += '<div class="tt-equip-item" data-id="' + r.id + '" style="padding:9px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f8fafc;">';
        html += '<div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">';
        html += '<span style="font-weight:600;color:#111827;">' + escHtml(((r.manufacturer || '') + ' ' + (r.model_name || '')).trim()) + '</span>';
        if (r.device_type) html += '<span class="badge badge-gray" style="font-size:10px;padding:2px 6px;">' + escHtml(r.device_type) + '</span>';
        html += '</div>';
        var sub = [];
        if (r.serial_number) sub.push('SN: ' + r.serial_number);
        if (r.asset_tag) sub.push('Asset: ' + r.asset_tag);
        if (r.location) sub.push(r.location);
        if (sub.length) html += '<div style="font-size:11px;color:#94a3b8;margin-top:2px;">' + escHtml(sub.join(' · ')) + '</div>';
        html += '</div>';
    }
    box.innerHTML = html;
    box.style.display = 'block';

    var items = box.querySelectorAll('.tt-equip-item');
    for (var k = 0; k < items.length; k++) {
        (function(el) { el.onclick = function() { ttSelectEquipment(parseInt(el.dataset.id, 10)); }; })(items[k]);
    }
}

function ttSelectEquipment(id) {
    var e = null;
    for (var i = 0; i < ttEqData.length; i++) {
        if (parseInt(ttEqData[i].id, 10) === id) { e = ttEqData[i]; break; }
    }
    if (!e) return;

    ttSelectedEquipmentId = id;
    ttSelectedMfrName     = e.manufacturer || '';
    ttSelectedModelName   = e.model_name || '';
    ttSelectedDeviceType  = e.device_type || '';
    ttSelectedSerial      = e.serial_number || '';
    ttSelectedAsset       = e.asset_tag || '';
    ttSelectedLocation    = e.location || '';

    document.getElementById('tt-selected-name').textContent = (ttSelectedMfrName + ' ' + ttSelectedModelName).trim();
    document.getElementById('tt-selected-mfr').textContent = ttSelectedMfrName || '—';
    document.getElementById('tt-selected-type').textContent = ttSelectedDeviceType || 'Device';
    document.getElementById('tt-selected-equip').style.display = 'block';

    var serialBadge = document.getElementById('tt-selected-serial-badge');
    if (serialBadge) serialBadge.style.display = ttSelectedSerial ? '' : 'none';
    var serialSpan = document.getElementById('tt-selected-serial');
    if (serialSpan) serialSpan.textContent = ttSelectedSerial;

    var assetBadge = document.getElementById('tt-selected-asset-badge');
    if (assetBadge) assetBadge.style.display = ttSelectedAsset ? '' : 'none';
    var assetSpan = document.getElementById('tt-selected-asset');
    if (assetSpan) assetSpan.textContent = ttSelectedAsset;

    var manual = document.getElementById('tt-selected-manual');
    if (manual) manual.style.display = 'none';

    // Auto-fill the ticket fields the equipment already knows about.
    if (ttSelectedSerial) document.getElementById('tt-serial').value = ttSelectedSerial;
    if (ttSelectedLocation) document.getElementById('tt-location').value = ttSelectedLocation;
    document.getElementById('tt-device-type').value = ttSelectedDeviceType;
    ttPopulateDeviceDropdown();

    var modelInput = document.getElementById('tt-model-search');
    if (modelInput) modelInput.value = '';

    var box = document.getElementById('tt-equip-results');
    if (box) box.style.display = 'none';
}

function ttSameDeviceType(selected, candidate) {
    var a = String(selected || '').trim().toLowerCase();
    var b = String(candidate || '').trim().toLowerCase();
    if (!a) return true;
    if (!b) return false;
    if (a === b) return true;
    var normA = a.replace(/[^a-z0-9]+/g, ' ').replace(/\s+/g, ' ').trim();
    var normB = b.replace(/[^a-z0-9]+/g, ' ').replace(/\s+/g, ' ').trim();
    if (normA === normB) return true;
    return normA.replace(/s$/, '') === normB.replace(/s$/, '');
}

function ticketClearEquipSelection() {
    ttSelectedEquipmentId = 0;
    ttSelectedMfrName = '';
    ttSelectedModelName = '';
    ttSelectedDeviceType = '';
    ttSelectedSerial = '';
    ttSelectedAsset = '';
    ttSelectedLocation = '';

    var card = document.getElementById('tt-selected-equip');
    if (card) card.style.display = 'none';
    var box = document.getElementById('tt-equip-results');
    if (box) { box.style.display = 'none'; box.innerHTML = ''; }
    var serial = document.getElementById('tt-serial');
    if (serial) serial.value = '';
    var search = document.getElementById('tt-equip-search');
    if (search) search.value = '';
    var modelInput = document.getElementById('tt-model-search');
    if (modelInput) modelInput.value = '';
    var dt = document.getElementById('tt-device-type');
    if (dt) dt.value = '';
    var devSel = document.getElementById('tt-device');
    if (devSel) devSel.value = '';
    var devOther = document.getElementById('tt-device-other');
    if (devOther) { devOther.value = ''; devOther.style.display = 'none'; }
}

// Manual model override — used when the device is not in Equipment yet.
function ttModelFreeText(v) {
    var val = (v || '').trim();
    ttSelectedEquipmentId = 0;
    if (!val) {
        if (!ttSelectedDeviceType && !ttSelectedSerial) {
            var card = document.getElementById('tt-selected-equip');
            if (card) card.style.display = 'none';
        }
        return;
    }
    ttSelectedModelName = val;
    document.getElementById('tt-selected-name').textContent = (ttSelectedMfrName ? ttSelectedMfrName + ' ' : '') + val;
    document.getElementById('tt-selected-mfr').textContent = ttSelectedMfrName || '—';
    document.getElementById('tt-selected-type').textContent = ttSelectedDeviceType || 'Device';
    document.getElementById('tt-selected-equip').style.display = 'block';
    var sb = document.getElementById('tt-selected-serial-badge');
    if (sb) sb.style.display = 'none';
    var ab = document.getElementById('tt-selected-asset-badge');
    if (ab) ab.style.display = 'none';
}

// ---------- problem picker (from troubleshooting_issues) ----------
// Device FIRST: Step-2 only lists troubleshooting titles whose device_types
// contain the Step-1 device (e.g. Laptop -> "No Power", "No Display"...).
// Matching is case-insensitive and also matches singular/plural + partial
// words so "Laptop" matches "laptop,desktop" and "Printer" matches "printers".
function ttDeviceMatchesTags(device, tags) {
    var d = String(device || '').trim().toLowerCase();
    var t = String(tags || '').trim().toLowerCase();
    if (!d) return true;      // no device chosen -> show everything
    if (!t) return false;     // device chosen -> hide untagged items to avoid unrelated problems
    if (t.indexOf(d) !== -1 || d.indexOf(t) !== -1) return true;
    var singD = d.replace(/s$/, '');
    var singT = t.replace(/s$/, '');
    if (singD && (t.indexOf(singD) !== -1 || singT.indexOf(singD) !== -1)) return true;
    var words = d.split(/[^a-z0-9]+/);
    for (var i = 0; i < words.length; i++) {
        if (words[i] && t.indexOf(words[i]) !== -1) return true;
    }
    return false;
}

function ttPopulateIssues() {
    var sel = document.getElementById('tt-issue');
    if (!sel) return;
    var dt = (ttSelectedDeviceType || '').trim();
    var opt = '<option value="">— Select a known problem —</option>';
    var matched = 0;
    var total = 0;
    for (var i = 0; i < ttIssueData.length; i++) {
        var it = ttIssueData[i];
        total++;
        // Only show problems that apply to the chosen device.
        if (!ttDeviceMatchesTags(dt, it.device_types)) continue;
        opt += '<option value="' + it.id + '">' + escHtml(it.title) + '</option>';
        matched++;
    }
    opt += '<option value="__OTHER__">Other / not listed — type it manually…</option>';
    sel.innerHTML = opt;

    var hint = document.getElementById('tt-issue-hint');
    if (hint) {
        if (!ttIssueData.length) {
            hint.textContent = 'No troubleshooting entries loaded — choose “Other / not listed” and type the problem.';
        } else if (matched) {
            hint.textContent = 'Showing ' + matched + ' of ' + total + ' problem' + (total === 1 ? '' : 's') + ' for device "' + (dt || 'all') + '".';
        } else {
            hint.textContent = 'No troubleshooting entry matches device "' + dt + '" — choose “Other / not listed” and type the problem.';
        }
        hint.style.display = 'block';
    }
    ttIssueChanged();
}

function ttIssueChanged() {
    var sel = document.getElementById('tt-issue');
    var custom = document.getElementById('tt-issue-custom');
    if (!sel || !custom) return;
    var isOther = (sel.value === '__OTHER__' || sel.value === '');
    custom.style.display = isOther ? '' : 'none';
    if (sel.value === '__OTHER__') custom.focus();
    ttRenderProblemInsight(sel.value);
    // The ticket title follows the troubleshooting problem (e.g. "No Power");
    // fall back to the equipment name, then the task, when nothing is picked.
    var titleEl = document.getElementById('tt-title');
    if (titleEl) {
        var problem = ttSelectedIssueTitle();
        if (problem) {
            titleEl.value = problem;
        } else if (isOther) {
            var task = ttTaskValue();
            titleEl.value = ttSelectedModelName ? ((ttSelectedMfrName + ' ' + ttSelectedModelName).trim()) : task;
        }
    }
}

function ttSelectedIssueTitle() {
    var sel = document.getElementById('tt-issue');
    if (!sel || !sel.value || sel.value === '__OTHER__') return '';
    var o = sel.options[sel.selectedIndex];
    return o ? o.textContent : '';
}

// ---------- device dropdown (drives the troubleshooting list) ----------
// Step 1 shows Device first; picking a device filters the Step-2 Problem list.
function ttDeviceOptions() {
    var seen = {};
    var out = [];
    for (var i = 0; i < ttEqData.length; i++) {
        var t = String(ttEqData[i].device_type || '').trim();
        if (!t || seen[t]) continue;
        seen[t] = true;
        out.push(t);
    }
    out.sort();
    return out;
}

function ttPopulateDeviceDropdown() {
    var sel = document.getElementById('tt-device');
    if (!sel) return;
    var opts = ttDeviceOptions();
    var cur = ttSelectedDeviceType || '';
    var html = '<option value="">— Select device —</option>';
    for (var i = 0; i < opts.length; i++) {
        html += '<option value="' + escHtml(opts[i]) + '"' + (cur === opts[i] ? ' selected' : '') + '>' + escHtml(opts[i]) + '</option>';
    }
    if (cur && opts.indexOf(cur) === -1) {
        html += '<option value="' + escHtml(cur) + '" selected>' + escHtml(cur) + '</option>';
    }
    html += '<option value="__OTHER__">Other — type it manually…</option>';
    sel.innerHTML = html;
    var other = document.getElementById('tt-device-other');
    if (other) {
        var showOther = (sel.value === '__OTHER__');
        other.style.display = showOther ? '' : 'none';
        if (!showOther && cur && opts.indexOf(cur) === -1) other.value = cur;
    }
}

function ttDeviceChanged() {
    var sel = document.getElementById('tt-device');
    var other = document.getElementById('tt-device-other');
    if (!sel) return;
    var previousEquipmentType = ttSelectedEquipmentId ? ttSelectedDeviceType : '';
    if (sel.value === '__OTHER__') {
        if (other) { other.style.display = ''; other.focus(); }
        ttSelectedDeviceType = other ? (other.value || '').trim() : '';
    } else {
        if (other) { other.style.display = 'none'; }
        ttSelectedDeviceType = (sel.value || '').trim();
    }
    var hidden = document.getElementById('tt-device-type');
    if (hidden) hidden.value = ttSelectedDeviceType;
    if (ttSelectedEquipmentId && previousEquipmentType && ttSelectedDeviceType && !ttSameDeviceType(ttSelectedDeviceType, previousEquipmentType)) {
        var chosenDevice = ttSelectedDeviceType;
        var isOtherDevice = sel.value === '__OTHER__';
        ticketClearEquipSelection();
        ttSelectedDeviceType = chosenDevice;
        if (sel) sel.value = isOtherDevice ? '__OTHER__' : chosenDevice;
        if (other) {
            other.style.display = isOtherDevice ? '' : 'none';
            if (isOtherDevice) other.value = chosenDevice;
        }
        if (hidden) hidden.value = ttSelectedDeviceType;
    }
    // Keep the equipment search in sync with the chosen device.
    ttRenderEquipResults();
}

function ttRenderProblemInsight(issueId) {
    var panel = document.getElementById('tt-problem-insight');
    var symptomsEl = document.getElementById('tt-insight-symptoms');
    var causeEl = document.getElementById('tt-insight-cause');
    var linkEl = document.getElementById('tt-insight-kb-link');
    if (!panel || !symptomsEl || !causeEl || !linkEl) return;
    var issue = null;
    for (var i = 0; i < ttIssueData.length; i++) {
        if (String(ttIssueData[i].id) === String(issueId)) { issue = ttIssueData[i]; break; }
    }
    if (!issue) {
        panel.style.display = 'none';
        symptomsEl.innerHTML = '';
        causeEl.textContent = '';
        return;
    }
    var symptoms = Array.isArray(issue.symptom_list) ? issue.symptom_list : [];
    symptomsEl.innerHTML = symptoms.length
        ? symptoms.map(function(symptom) { return '<span>' + ttEsc(symptom) + '</span>'; }).join('')
        : '<small>No symptoms documented for this problem yet.</small>';
    causeEl.textContent = issue.root_cause || 'No common cause has been linked from the Knowledge Base yet.';
    if (issue.knowledge_id) {
        linkEl.href = APP_BASE + 'knowledge/view?id=' + encodeURIComponent(issue.knowledge_id);
        linkEl.style.display = '';
    } else {
        linkEl.style.display = 'none';
        linkEl.removeAttribute('href');
    }
    panel.style.display = '';
    if (window.lucide) lucide.createIcons();
}

function ttDeviceOtherTyped(v) {
    ttSelectedDeviceType = (v || '').trim();
    var hidden = document.getElementById('tt-device-type');
    if (hidden) hidden.value = ttSelectedDeviceType;
}

// ---- Ticket map (Leaflet + OpenStreetMap, free) ----
function initTicketMap() {
    var mapEl = document.getElementById('tt-map');
    if (!mapEl) return;
    if (typeof L === 'undefined') {
        mapEl.innerHTML = '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:12px;"><div style="width:28px;height:28px;border:2px solid #2563eb;border-top-color:transparent;border-radius:50%;margin:0 auto 8px;animation:spin 0.7s linear infinite;display:inline-block;"></div>Loading map…</div>';
        // Retry a few times in case the script loads async
        var tries = 0;
        var timer = setInterval(function() {
            tries++;
            if (typeof L !== 'undefined') { clearInterval(timer); initTicketMap(); }
            if (tries > 20) clearInterval(timer);
        }, 150);
        return;
    }
    // Grab any existing destination coords from the ticket being edited (rare on create)
    var latInput = document.getElementById('tt-lat');
    var lngInput = document.getElementById('tt-lng');
    var addrInput = document.getElementById('tt-address');
    var zoom = 14;
    var center = [14.5995, 120.9842]; // Manila default
    if (latInput && lngInput && latInput.value && lngInput.value) {
        center = [parseFloat(latInput.value), parseFloat(lngInput.value)];
        zoom = 16;
    }
    ttMapInstance = L.map(mapEl, { zoomControl: true, attributionControl: true }).setView(center, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(ttMapInstance);
    // Start marker (company origin) — only if we have a stored start point
    if (latInput && lngInput && latInput.value && lngInput.value) {
        ttMapMarker = L.marker(center, { icon: ttBlueIcon() }).addTo(ttMapInstance);
        ttMapMarker.bindPopup('<b>Start</b><br/>' + escHtml(addrInput ? addrInput.value : '')).openPopup();
    }
    ttMapMarkerEnd = L.marker(center, { icon: ttRedIcon() }).addTo(ttMapInstance);
    if (addrInput && addrInput.value) {
        ttMapMarkerEnd.bindPopup('<b>Destination</b><br/>' + escHtml(addrInput.value)).openPopup();
    }
    ttMapInstance.on('click', function(e) {
        var lat = e.latlng.lat.toFixed(6);
        var lng = e.latlng.lng.toFixed(6);
        ttPlaceMapMarkerEnd(lat, lng);
        // reverse geocode (free Nominatim)
        ttReverseGeocode(lat, lng, function(addr) {
            if (addrInput) addrInput.value = addr;
            ttRememberAddress(ttCompanyValue(), addr, lat, lng);
            ttRenderAddressDatalist();
            if (ttMapMarkerEnd) ttMapMarkerEnd.getPopup()?.setContent('<b>Destination</b><br/>' + escHtml(addr));
        });
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
    });
    // Locate me button
    var locateBtn = document.getElementById('tt-map-locate');
    if (locateBtn) {
        locateBtn.onclick = function() { ttLocateMe(); };
    }
    var clearBtn = document.getElementById('tt-map-clear');
    if (clearBtn) {
        clearBtn.onclick = function() {
            if (ttMapMarkerEnd) { ttMapInstance.removeLayer(ttMapMarkerEnd); ttMapMarkerEnd = null; }
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            if (addrInput) addrInput.value = '';
        };
    }
    // Fit the map to show both markers if we have two points
    setTimeout(function() {
        try {
            var markers = [];
            if (ttMapMarker) markers.push(ttMapMarker);
            if (ttMapMarkerEnd) markers.push(ttMapMarkerEnd);
            if (markers.length > 1) ttMapInstance.fitBounds(L.featureGroup(markers).getBounds().pad(0.3));
        } catch(e) {}
    }, 250);
}

function ttPlaceMapMarkerEnd(lat, lng) {
    if (!ttMapInstance) return;
    if (ttMapMarkerEnd) ttMapInstance.removeLayer(ttMapMarkerEnd);
    ttMapMarkerEnd = L.marker([lat, lng], { icon: ttRedIcon() }).addTo(ttMapInstance);
}

function ttReverseGeocode(lat, lng, cb) {
    if (!cb) return;
    fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&zoom=18', {
        headers: { 'Accept-Language': 'en' }
    }).then(function(r) { return r.json(); }).then(function(data) {
        var addr = data.display_name || (data.address ? (data.address.road || '') + ', ' + (data.address.city || data.address.village || '') : '');
        cb(addr);
    }).catch(function() { cb('Address unavailable'); });
}

function ttLocateMe() {
    if (!navigator.geolocation) { showToast('Geolocation is not available on this device.', 'warning'); return; }
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(6);
        var lng = pos.coords.longitude.toFixed(6);
        ttPlaceMapMarkerEnd(lat, lng);
        var latInput = document.getElementById('tt-lat');
        var lngInput = document.getElementById('tt-lng');
        var addrInput = document.getElementById('tt-address');
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        ttReverseGeocode(lat, lng, function(addr) {
            if (addrInput) addrInput.value = addr;
            ttRememberAddress(ttCompanyValue(), addr, lat, lng);
            ttRenderAddressDatalist();
            if (ttMapMarkerEnd) ttMapMarkerEnd.getPopup()?.setContent('<b>Destination</b><br/>' + escHtml(addr));
        });
        showToast('Location captured from your device.', 'success');
    }, function() { showToast('Could not get your location. Tap the map to place it instead.', 'warning'); }, { enableHighAccuracy: true, timeout: 10000 });
}

function ttBlueIcon() {
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    return L.divIcon({ className: 'tt-map-icon', html: svg, iconSize: [24, 24], iconAnchor: [12, 24], popupAnchor: [0, -24] });
}

function ttRedIcon() {
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
    return L.divIcon({ className: 'tt-map-icon', html: svg, iconSize: [24, 24], iconAnchor: [12, 24], popupAnchor: [0, -24] });
}

// ---- Submit: Time In ----
function ticketSubmitTimeIn() {
    var ticketNo = (document.getElementById('tt-ticket-no').value || '').trim();
    var title = (document.getElementById('tt-title').value || '').trim();
    var desc  = (document.getElementById('tt-description').value || '').trim();
    var task  = ttTaskValue();
    var pri   = (document.getElementById('tt-priority') && document.getElementById('tt-priority').value) || 'medium';
    var cust  = (document.getElementById('tt-customer') ? (document.getElementById('tt-customer').value || '').trim() : '');
    var loc   = (document.getElementById('tt-location') ? (document.getElementById('tt-location').value || '').trim() : '');
    var serial= (document.getElementById('tt-serial').value || '').trim();
    var addr  = (document.getElementById('tt-address') ? (document.getElementById('tt-address').value || '').trim() : '');
    var lat   = (document.getElementById('tt-lat') ? (document.getElementById('tt-lat').value || '').trim() : '');
    var lng   = (document.getElementById('tt-lng') ? (document.getElementById('tt-lng').value || '').trim() : '');
    var timeVal = (document.getElementById('tt-time') ? (document.getElementById('tt-time').value || '').trim() : '');
    var note  = (document.getElementById('tt-note') ? (document.getElementById('tt-note').value || '').trim() : '');
    var company = ttCompanyValue();

    // ---- Problem: a known troubleshooting issue, or free text ----
    var issueSel = document.getElementById('tt-issue');
    var issueVal = issueSel ? (issueSel.value || '') : '';
    var issueId = 0;
    var problemText = '';
    if (issueVal && issueVal !== '__OTHER__') {
        issueId = parseInt(issueVal, 10) || 0;
        problemText = ttSelectedIssueTitle();
    } else {
        var custom = document.getElementById('tt-issue-custom');
        problemText = custom ? (custom.value || '').trim() : '';
        if (issueVal === '__OTHER__' && !problemText) {
            showToast('Please type the problem, or pick one from the list.', 'warning'); return;
        }
    }

    if (!/^\d+$/.test(ticketNo)) { showToast('Type the numeric ticket number. SD is added automatically.', 'warning'); return; }
    if (!company) { showToast('Please type the company name.', 'warning'); return; }
    if (!task)    { showToast('Please fill in the task.', 'warning'); return; }
    var companyIsNew = (document.getElementById('tt-company') || {}).value === '__OTHER__';
    var taskIsNew = (document.getElementById('tt-task') || {}).value === '__OTHER__';
    var approvedCompanyMatch = companyIsNew ? ttFindApprovedValue('tt-company', company) : '';
    var approvedTaskMatch = taskIsNew ? ttFindApprovedValue('tt-task', task) : '';
    if (approvedCompanyMatch) { showToast('Company already exists as "' + approvedCompanyMatch + '". Select it from the dropdown.', 'warning'); return; }
    if (approvedTaskMatch) { showToast('Task already exists as "' + approvedTaskMatch + '". Select it from the dropdown.', 'warning'); return; }
    if (!problemText) { showToast('Please select a problem or type one in.', 'warning'); return; }
    if (!pri) pri = 'medium';

    // The ticket title: the troubleshooting problem is the hero (e.g. "No Power"),
    // then the equipment name, then the task.
    if (problemText) {
        title = problemText;
    } else if (!title) {
        title = ttSelectedModelName ? ((ttSelectedMfrName + ' ' + ttSelectedModelName).trim()) : task;
    }
    document.getElementById('tt-title').value = title;
    if (!desc) desc = task;

    // Remember this address under the company so the next ticket for the same
    // company can pick it from the address dropdown (coordinates included).
    ttRememberAddress(company, addr, lat, lng);

    // Time In is stamped by the server ("now") — no picker on the form anymore.
    // If a hidden tt-time field ever exists, honor it; otherwise let the API decide.
    var timeInPayload = {};
    if (timeVal) { timeInPayload.time_in = timeVal; }

    var body = {
        ticket_number: ticketNo,
        title: title,
        description: desc,
        task: task,
        task_is_new: taskIsNew ? 1 : 0,
        problem: problemText,
        issue_id: issueId || undefined,
        priority: pri,
        company_name: company,
        company_is_new: companyIsNew ? 1 : 0,
        customer_name: cust || undefined,
        location: loc || undefined,
        device: ttSelectedModelName ? ((ttSelectedMfrName + ' ' + ttSelectedModelName).trim()) : undefined,
        device_type: ttSelectedDeviceType || undefined,
        equipment_id: ttSelectedEquipmentId || undefined,
        serial_number: serial || undefined,
        notes: note || undefined,
        address: addr || undefined,
        latitude: lat || undefined,
        longitude: lng || undefined
    };
    var bodyKeys = Object.keys(timeInPayload);
    for (var k = 0; k < bodyKeys.length; k++) { body[bodyKeys[k]] = timeInPayload[bodyKeys[k]]; }

    var btn = document.getElementById('tt-next-2');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving…';
    }
    api('/api/tickets/timein', { method: 'POST', body: body }).then(function(res) {
        if (res.success) {
            showToast('Ticket ' + res.ticket_number + ' created — time in at ' + res.time_in, 'success');
            closeModal('new-ticket-modal');
            setTimeout(function() { window.location.reload(); }, 1200);
        } else {
            showToast('Time-in failed: ' + (res.error || 'unknown'), 'error');
            if (btn) { btn.disabled = false; btn.textContent = 'Start Session (Time In)'; }
        }
    }).catch(function(err) {
        showToast('Error: ' + err.message, 'error');
        if (btn) { btn.disabled = false; btn.textContent = 'Start Session (Time In)'; }
    });
}

// ==================== Ticket Drawer (View Ticket) ====================
function ttEsc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function ttStatusBadge(status) {
    var map = {
        'new':         ['#fffbeb', '#d97706', 'New'],
        'in_progress': ['#eff6ff', '#2563eb', 'In Progress'],
        'solved':      ['#f0fdf4', '#16a34a', 'Solved'],
        'partial':     ['#f0fdf4', '#16a34a', 'Solved'],
        'escalated':   ['#fef2f2', '#dc2626', 'Escalated']
    };
    var m = map[status] || map['new'];
    return '<span class="badge" style="background:' + m[0] + ';color:' + m[1] + ';">' + m[2] + '</span>';
}

function ttStatusLabel(status) {
    var labels = { 'new':'New', 'in_progress':'In Progress', 'solved':'Solved', 'partial':'Solved', 'escalated':'Escalated' };
    return labels[status] || status;
}

// Renders a drawer section; rows with empty values are skipped entirely.
function ttDrawerSection(title, rows) {
    var html = '<div class="ftd-section"><div class="ftd-title">' + ttEsc(title) + '</div>';
    rows.forEach(function(r) {
        var v = r[1];
        if (v === undefined || v === null || String(v).trim() === '') return;
        html += '<div class="ftd-row"><div class="ftd-lbl">' + ttEsc(r[0]) + '</div><div class="ftd-val">' + ttEsc(v) + '</div></div>';
    });
    return html + '</div>';
}

// Issue / Task "View more" toggle on the card.
function ticketToggleIssue(ticketId, btn) {
    var el = document.getElementById('issue-' + ticketId);
    if (!el) return;
    var clamped = el.classList.toggle('ft-clamped');
    btn.textContent = clamped ? 'View more' : 'Show less';
}

// Duration helper: prefer the DB value; compute from the timestamps as fallback.
function ttDurationStr(S) {
    var mins = S.time_spent_minutes;
    if ((mins === undefined || mins === null || mins === '') && S.started_at && S.ended_at) {
        var a = new Date(String(S.started_at).replace(' ', 'T'));
        var b = new Date(String(S.ended_at).replace(' ', 'T'));
        if (!isNaN(a.getTime()) && !isNaN(b.getTime())) { mins = Math.max(0, Math.round((b.getTime() - a.getTime()) / 60000)); }
    }
    if (mins === undefined || mins === null || mins === '') return '';
    mins = parseInt(mins, 10);
    if (isNaN(mins)) return '';
    if (mins >= 60) { var hh = Math.floor(mins / 60), mm = mins % 60; return hh + 'h' + (mm ? ' ' + mm + 'm' : ''); }
    return mins + ' minute' + (mins === 1 ? '' : 's');
}

// Keep unsaved report input values when the drawer re-renders (time in/out/done).
function ttCacheReport(ticketId) {
    var cache = window._ttReportCache = window._ttReportCache || {};
    var c = cache[ticketId] = {};
    ['result', 'reco', 'confirm'].forEach(function(k) {
        var el = document.getElementById('rep-' + ticketId + '-' + k);
        if (el) { c[k] = el.value; }
    });
}

function openTicketDrawer(ticketId) {
    var S = (window.ttTicketData && window.ttTicketData[ticketId]) || null;
    if (!S) { showToast('Ticket data not found.', 'error'); return; }
    var status = S.status || 'new';
    var device = ((S.manufacturer || '') + ' ' + (S.model || '')).trim();
    var problem = S.problem_description || S.task || S.title || '';
    var timeIn = S.started_at ? ttFmtTime(S.started_at) : '00';
    var timeOut = S.ended_at ? ttFmtTime(S.ended_at) : '00';

    // ---- Header: company, ticket #, SN, device, location | status ----
    var h = '<div class="ftd-modal-head" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;border-bottom:1px solid #e5e7eb;">';
    h += '<div style="display:flex;gap:12px;min-width:0;">';
    h += '<div class="ft-co-ico"><i data-lucide="building-2"></i></div>';
    h += '<div style="min-width:0;">';
    h += '<div id="ticket-drawer-title" style="font-size:17px;font-weight:800;color:#111827;word-break:break-word;">' + ttEsc(S.company_name || 'Company not specified') + '</div>';
    h += '<div style="font-size:12px;color:#64748b;font-weight:600;margin-top:3px;">Ticket #' + ttEsc(S.ticket_number || '') + (S.serial_number ? '<span style="margin-left:10px;">SN: ' + ttEsc(S.serial_number) + '</span>' : '') + '</div>';
    h += '</div></div>';
    h += '<div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">' + ttStatusBadge(status)
       + '<button onclick="closeTicketDrawer()" class="btn btn-sm btn-ghost ftd-close-btn" aria-label="Close ticket details" title="Close" style="color:#64748b;">&#10005;</button></div>';
    h += '</div>';

    h += '<div class="ftd-grid">';

    // ================= LEFT: ticket / device / work time / issue =================
    h += '<div>';
    h += ttDrawerSection('Ticket', [
        ['Ticket #', S.ticket_number],
        ['Status', ttStatusLabel(status)],
        ['Created', S.created_at ? ttFmtDate(S.created_at) : (S.started_at ? ttFmtDate(S.started_at) : '')],
        ['Priority', S.priority],
        ['Address', S.address]
    ]);
    if ((S.latitude && S.longitude) || S.address || S.location) {
        h += '<div class="ftd-section"><div class="ftd-title">Ticket Location</div><div id="ticket-location-map-' + ticketId + '" class="ftd-ticket-map"></div></div>';
    }
    h += ttDrawerSection('Device', [
        ['Device', device],
        ['Serial Number', S.serial_number]
    ]);

    // ---- Work Time: Time In / Time Out / Duration + state-driven button ----
    h += '<div class="ftd-section"><div class="ftd-title">Work Time</div>';
    h += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">';
    h += '<div class="ftd-timebox"><div class="ftd-lbl">TIME IN</div>'
       + '<div class="ftd-time' + (S.started_at ? '' : ' ftd-zero') + '">' + ttEsc(timeIn) + '</div>'
       + '<div class="ftd-subdate">' + (S.started_at ? ttEsc(ttFmtDate(S.started_at)) : '&nbsp;') + '</div></div>';
    h += '<div class="ftd-timebox"><div class="ftd-lbl">TIME OUT</div>'
       + '<div class="ftd-time' + (S.ended_at ? '' : ' ftd-zero') + '">' + ttEsc(timeOut) + '</div>'
       + '<div class="ftd-subdate">' + (S.ended_at ? ttEsc(ttFmtDate(S.ended_at)) : '&nbsp;') + '</div></div>';
    h += '</div>';
    var dur = ttDurationStr(S);
    h += '<div style="margin-top:10px;font-size:12px;color:#64748b;font-weight:600;">Duration: <span style="color:#111827;font-weight:700;">' + (dur ? ttEsc(dur) : '—') + '</span></div>';
    // Lifecycle: START TIME IN → TIME OUT → MARK AS DONE → ✓ COMPLETED
    var act = '';
    if (!S.started_at) {
        act += '<button onclick="ticketTimeIn(' + ticketId + ')" class="btn btn-sm btn-primary" style="flex:1;"><i data-lucide="play" style="width:13px;height:13px;"></i> Start Time In</button>';
    } else if (!S.ended_at) {
        act += '<button onclick="ticketTimeOut(' + ticketId + ')" class="btn btn-sm btn-warning" style="flex:1;"><i data-lucide="square" style="width:13px;height:13px;"></i> Time Out</button>';
    } else if (status !== 'solved') {
        act += '<button onclick="ticketDone(' + ticketId + ')" class="btn btn-sm btn-success" style="flex:1;"><i data-lucide="check" style="width:13px;height:13px;"></i> Mark as Done</button>';
    } else {
        act += '<span class="badge" style="background:#f0fdf4;color:#16a34a;flex:1;text-align:center;padding:7px 10px;font-size:12px;font-weight:700;">&#10003; Completed</span>';
    }
    h += '<div style="display:flex;gap:8px;margin-top:12px;">' + act + '</div>';
    h += '<div id="to-' + ticketId + '-msg" style="font-size:11px;color:#64748b;margin-top:6px;min-height:15px;"></div>';
    h += '</div>';

    // ---- Issue / Task (multi-line preserved) ----
    h += '<div class="ftd-section"><div class="ftd-title">Issue / Task</div>'
       + '<div class="ftd-val" style="text-align:left;white-space:pre-wrap;line-height:1.6;">' + ttEsc(problem) + '</div></div>';

    // ---- Troubleshooting checklist (tap to check what you did + add custom rows) ----
    h += '<div class="ftd-section">'
       + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">'
       + '<div class="ftd-title" style="margin:0;">Troubleshooting Checklist</div>'
       + '<span id="chk-count-' + ticketId + '" style="font-size:11px;color:#64748b;font-weight:600;"></span></div>'
       + '<div id="chk-' + ticketId + '"><div style="font-size:12px;color:#94a3b8;">Loading checklist…</div></div>'
       + '<div id="chk-msg-' + ticketId + '" style="font-size:11px;color:#16a34a;min-height:15px;margin-top:2px;"></div>'
       + '</div>';
    h += '</div>'; // /left

    // ================= RIGHT: field guide + report =================
    h += '<div>';
    // Field Guide: tools / videos / tips — loaded from the DB for this issue + device
    h += '<div class="ftd-section" id="guide-' + ticketId + '" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:14px;">'
       + '<div class="ftd-title">Field Guide</div>'
       + '<div style="font-size:12px;color:#94a3b8;">Loading tools, videos & tips…</div></div>';
    h += '<div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 8px;">'
       + '<div class="ftd-title" style="margin:0;color:#1e40af;">Report</div>'
       + '<button onclick="ticketCopyReport(' + ticketId + ')" class="btn btn-sm btn-primary"><i data-lucide="copy" style="width:12px;height:12px;"></i> Copy Report</button>'
       + '</div>';
    // Live report preview (updates instantly as times/fields change)
    h += '<div id="report-' + ticketId + '" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:6px 14px 4px;margin-bottom:12px;"></div>';

    // Technician report inputs — saved on Time Out and mirrored in the report.
    var issueMemory = ttIssueFieldOptions[String(parseInt(S.issue_id || 0, 10))] || { result: [], recommendation: [] };
    var companyMemory = ttCompanyContacts[ttNormalizeName(S.company_name || '')] || [];
    function memorySelect(key, values, current, label) {
        if (!values || !values.length) return '';
        var html = '<select class="form-input tt-memory-select" onchange="ttApplyMemoryChoice(' + ticketId + ',\'' + key + '\',this.value)"><option value="">Saved ' + label + '…</option>';
        values.forEach(function(value) { html += '<option value="' + ttEsc(value) + '">' + ttEsc(value) + '</option>'; });
        return html + '</select>';
    }
    h += '<div class="ftd-field"><label class="ftd-lbl" for="rep-' + ticketId + '-result">Result of Checking</label>'
       + memorySelect('result', issueMemory.result, S.result_of_checking || '', 'results for this problem')
       + '<textarea id="rep-' + ticketId + '-result" class="form-input" rows="3" placeholder="Enter what you found..." style="width:100%;font-size:12.5px;padding:8px 10px;resize:vertical;">' + ttEsc(S.result_of_checking || '') + '</textarea></div>';
    h += '<div class="ftd-field"><label class="ftd-lbl" for="rep-' + ticketId + '-reco">Recommendation</label>'
       + memorySelect('reco', issueMemory.recommendation, S.recommendation || '', 'recommendations for this problem')
       + '<textarea id="rep-' + ticketId + '-reco" class="form-input" rows="2" placeholder="Enter your recommendation..." style="width:100%;font-size:12.5px;padding:8px 10px;resize:vertical;">' + ttEsc(S.recommendation || '') + '</textarea></div>';
    h += '<div class="ftd-field"><label class="ftd-lbl" for="rep-' + ticketId + '-confirm">Confirmed By</label>'
       + memorySelect('confirm', companyMemory, S.confirmed_by || '', 'contacts for this company')
       + '<input id="rep-' + ticketId + '-confirm" class="form-input" value="' + ttEsc(S.confirmed_by || (companyMemory[0] || '')) + '" placeholder="e.g. System Admin" style="width:100%;font-size:12.5px;padding:8px 10px;"></div>';

    // Steps done — live mirror of the checklist (auto-saved with every change)
    h += '<div class="ftd-section"><div class="ftd-title">Steps Done</div><div id="logged-' + ticketId + '" class="ftd-steps"></div></div>';
    h += ttDrawerSection('Parts & Tools', [['Parts Replaced', S.parts_replaced], ['Tools Used', S.tools_used]]);
    h += '</div>'; // /right

    h += '</div>'; // /ftd-grid

    var drawer = document.getElementById('ticket-drawer');
    var overlay = document.getElementById('ticket-drawer-overlay');
    if (overlay && overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }
    if (drawer && drawer.parentElement !== document.body) {
        document.body.appendChild(drawer);
    }

    var body = document.getElementById('ticket-drawer-body');
    if (!body) return;
    body.innerHTML = h;
    // Restore unsaved report input values after a re-render (time in/out/done)
    var cache = (window._ttReportCache || {})[ticketId];
    if (cache) {
        ['result', 'reco', 'confirm'].forEach(function(k) {
            var el = document.getElementById('rep-' + ticketId + '-' + k);
            if (el && cache[k]) { el.value = cache[k]; }
        });
    }
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    var addInput = document.getElementById('chk-add-' + ticketId);
    if (addInput) { addInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); ttAddStep(ticketId); } }); }
    if (drawer) {
        drawer.style.display = 'block';
        drawer.scrollTop = 0;
    }
    if (overlay) overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
    ticketRenderReport(ticketId);
    ttRenderLoggedSteps(ticketId);
    ttLoadGuide(ticketId);
    ttInitTicketLocationMap(ticketId, S);
    ['result', 'reco', 'confirm'].forEach(function(k) {
        var el = document.getElementById('rep-' + ticketId + '-' + k);
        if (el && !el.dataset.reportWired) {
            el.dataset.reportWired = '1';
            el.addEventListener('input', function() { ticketRenderReport(ticketId); });
        }
    });
}

function closeTicketDrawer() {
    var d = document.getElementById('ticket-drawer');
    var o = document.getElementById('ticket-drawer-overlay');
    if (d) d.style.display = 'none';
    if (o) o.style.display = 'none';
    document.body.style.overflow = '';
}

function ttApplyMemoryChoice(ticketId, key, value) {
    if (!value) return;
    var el = document.getElementById('rep-' + ticketId + '-' + key);
    if (el) { el.value = value; el.dispatchEvent(new Event('input', { bubbles: true })); }
}

function ttInitTicketLocationMap(ticketId, S) {
    var el = document.getElementById('ticket-location-map-' + ticketId);
    if (!el || !window.L) return;
    if (!S.latitude || !S.longitude) {
        var address = S.address || S.location || '';
        if (!address) return;
        el.innerHTML = '<div style="padding:22px;text-align:center;color:#64748b;font-size:12px;">Locating saved address…</div>';
        fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(address), { headers:{'Accept-Language':'en'} })
            .then(function(r){ return r.json(); })
            .then(function(rows){
                if (!rows || !rows.length) { el.innerHTML = '<div style="padding:22px;text-align:center;color:#64748b;font-size:12px;">Map coordinates are not available for this address.</div>'; return; }
                S.latitude = rows[0].lat; S.longitude = rows[0].lon;
                ttInitTicketLocationMap(ticketId, S);
            }).catch(function(){ el.innerHTML = '<div style="padding:22px;text-align:center;color:#64748b;font-size:12px;">Map is unavailable right now.</div>'; });
        return;
    }
    var point = [parseFloat(S.latitude), parseFloat(S.longitude)];
    el.innerHTML = '';
    var map = L.map(el, { zoomControl:true, attributionControl:false, dragging:true }).setView(point, 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19 }).addTo(map);
    L.marker(point).addTo(map).bindPopup('<b>' + ttEsc(S.company_name || 'Ticket location') + '</b><br>' + ttEsc(S.address || S.location || '')).openPopup();
    setTimeout(function(){ map.invalidateSize(); }, 100);
}

// ---- Troubleshooting checklist + field guide (real DB data, auto-saved) ----
function ttLoadGuide(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('chk-' + ticketId);
    if (!S || !box) { return; }
    var qs = 'issue_id=' + encodeURIComponent(S.issue_id || 0)
           + '&model=' + encodeURIComponent(S.model || '')
           + '&manufacturer=' + encodeURIComponent(S.manufacturer || '');
    api('/api/tickets/guide?' + qs).then(function(g) {
        S._guide = g || {};
        ttRenderChecklist(ticketId);
        ttRenderGuide(ticketId);
    }).catch(function() {
        box.innerHTML = '<div style="font-size:12px;color:#94a3b8;">Checklist unavailable right now.</div>';
        var g2 = document.getElementById('guide-' + ticketId);
        if (g2) { g2.innerHTML = '<div class="ftd-title">Field Guide</div><div style="font-size:12px;color:#94a3b8;">Guide unavailable right now.</div>'; }
    });
}

function ttNormalizeSteps(value) {
    if (Array.isArray(value)) {
        return value.map(function(s) { return String(s || '').trim(); }).filter(Boolean);
    }
    if (typeof value === 'string') {
        var trimmed = value.trim();
        if (!trimmed || trimmed === '[]') return [];
        try {
            var decoded = JSON.parse(trimmed);
            if (Array.isArray(decoded)) return ttNormalizeSteps(decoded);
            if (typeof decoded === 'string') return ttNormalizeSteps(decoded);
        } catch (e) {}
        return trimmed.split(/\r?\n|\r/).map(function(s) { return s.trim(); }).filter(Boolean);
    }
    return [];
}

// Renders the checkable rows: suggested steps + custom typed steps + add-row.
function ttRenderChecklist(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('chk-' + ticketId);
    if (!box || !S) { return; }
    var guide = S._guide || {};
    var suggestions = guide.steps || [];
    var done = ttNormalizeSteps(S.steps || S.steps_performed || []);
    S.steps = done;
    var doneLower = done.map(function(s) { return String(s).toLowerCase().trim(); });
    var sugLower = suggestions.map(function(st) { return String(st.title || '').toLowerCase().trim(); });
    var html = '';
    // Suggested steps for this issue (from the troubleshooting guide)
    suggestions.forEach(function(st) {
        var text = String(st.title || ('Step ' + st.n));
        var checked = doneLower.indexOf(text.toLowerCase().trim()) !== -1;
        var risk = st.risk === 'danger' ? ' ⚠' : (st.risk === 'caution' ? ' ⚡' : '');
        html += '<div class="ft-chk-row' + (checked ? ' done' : '') + '" data-step="' + ttEsc(text) + '" onclick="ttToggleStepEl(this, ' + ticketId + ')">'
              + '<span class="ft-chk-box">' + (checked ? '✓' : '') + '</span>'
              + '<span class="ft-chk-txt">' + ttEsc(text) + risk + '</span></div>';
    });
    // Custom steps the technician typed on site (removable)
    done.forEach(function(text) {
        if (sugLower.indexOf(String(text).toLowerCase().trim()) !== -1) { return; }
        html += '<div class="ft-chk-row done" data-step="' + ttEsc(text) + '">'
              + '<span class="ft-chk-box">✓</span>'
              + '<span class="ft-chk-txt" onclick="ttToggleStepEl(this.parentElement, ' + ticketId + ')">' + ttEsc(text) + '</span>'
              + '<button type="button" onclick="event.stopPropagation(); ttRemoveStepEl(this.closest(&quot;.ft-chk-row&quot;), ' + ticketId + ')" style="margin-left:auto;background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;padding:0 2px;flex-shrink:0;">✕</button></div>';
    });
    // Add-row: type anything extra you did (as many rows as needed)
    html += '<div style="display:flex;gap:6px;margin-top:8px;">'
          + '<input id="chk-add-' + ticketId + '" class="form-input" placeholder="Add what you did on site..." style="flex:1;min-width:0;font-size:12.5px;padding:8px 10px;">'
          + '<button type="button" onclick="ttAddStep(' + ticketId + ')" class="btn btn-sm btn-secondary" style="flex-shrink:0;">Add</button></div>';
    box.innerHTML = html;
    var countEl = document.getElementById('chk-count-' + ticketId);
    if (countEl) { countEl.textContent = done.length + (suggestions.length ? ' / ' + suggestions.length + ' done' : ' done'); }
    var addInput = document.getElementById('chk-add-' + ticketId);
    if (addInput) { addInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); ttAddStep(ticketId); } }); }
}

// Check / uncheck a step; auto-saves immediately.
function ttToggleStepEl(rowEl, ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    if (!S || !rowEl) { return; }
    var text = rowEl.dataset.step || '';
    if (!text) { return; }
    var done = ttNormalizeSteps(S.steps || S.steps_performed || []);
    var lower = done.map(function(s) { return String(s).toLowerCase().trim(); });
    var idx = lower.indexOf(text.toLowerCase().trim());
    if (idx !== -1) {
        done.splice(idx, 1);
        rowEl.classList.remove('done');
        var b1 = rowEl.querySelector('.ft-chk-box'); if (b1) { b1.textContent = ''; }
    } else {
        done.push(text);
        rowEl.classList.add('done');
        var b2 = rowEl.querySelector('.ft-chk-box'); if (b2) { b2.textContent = '✓'; }
    }
    S.steps = done;
    ttSaveSteps(ticketId);
    ttRenderLoggedSteps(ticketId);
    ticketRenderReport(ticketId);
    var countEl = document.getElementById('chk-count-' + ticketId);
    if (countEl) {
        var total = ((S._guide && S._guide.steps) || []).length;
        countEl.textContent = done.length + (total ? ' / ' + total + ' done' : ' done');
    }
}

// Remove a custom step row.
function ttRemoveStepEl(rowEl, ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    if (!S || !rowEl) { return; }
    var text = rowEl.dataset.step || '';
    S.steps = ttNormalizeSteps(S.steps || S.steps_performed || []).filter(function(s) { return String(s).toLowerCase().trim() !== text.toLowerCase().trim(); });
    ttSaveSteps(ticketId);
    ttRenderChecklist(ticketId);
    ttRenderLoggedSteps(ticketId);
    ticketRenderReport(ticketId);
}

// Add a manually typed step (as many as needed).
function ttAddStep(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var input = document.getElementById('chk-add-' + ticketId);
    if (!S || !input) { return; }
    var text = input.value.trim();
    if (!text) { showToast('Type what you did first.', 'warning'); return; }
    S.steps = ttNormalizeSteps(S.steps || S.steps_performed || []);
    var existing = S.steps.map(function(s) { return String(s).toLowerCase().trim(); });
    if (existing.indexOf(text.toLowerCase().trim()) !== -1) {
        showToast('That checklist item is already selected.', 'info');
        input.value = '';
        return;
    }
    S.steps.push(text);
    S._learnedSteps = S._learnedSteps || [];
    S._learnedSteps.push(text);
    input.value = '';
    ttSaveSteps(ticketId);
    ttRenderChecklist(ticketId);
    ttRenderLoggedSteps(ticketId);
    ticketRenderReport(ticketId);
}

// Persist the checklist to the DB (steps_performed JSON) after every change.
function ttSaveSteps(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    if (!S) { return; }
    var msgEl = document.getElementById('chk-msg-' + ticketId);
    S.steps = ttNormalizeSteps(S.steps || S.steps_performed || []);
    S.steps_performed = JSON.stringify(S.steps);
    api('/api/tickets/steps', { method: 'POST', body: { ticket_id: ticketId, steps: S.steps, learned_steps: S._learnedSteps || [] } })
        .then(function(res) {
            S._learnedSteps = [];
            if (msgEl) {
                msgEl.textContent = res.success ? 'Saved ✓' : 'Save failed';
                setTimeout(function() { msgEl.textContent = ''; }, 2000);
            }
            ttLoadGuide(ticketId);
        }).catch(function(err) {
            if (msgEl) { msgEl.textContent = 'Save failed'; }
            showToast('Checklist save failed: ' + err.message, 'error');
        });
}

// Field Guide panel: tools / videos / tips (only real DB data).
function ttRenderGuide(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('guide-' + ticketId);
    if (!box || !S) { return; }
    var g = S._guide || {};
    var html = '';
    if ((g.tools || []).length) {
        html += '<div class="ftd-title">Tools Needed</div><div style="margin-bottom:10px;">';
        g.tools.forEach(function(t) { html += '<span class="ft-tool-chip">🛠 ' + ttEsc(t) + '</span>'; });
        html += '</div>';
    }
    if ((g.videos || []).length) {
        html += '<div class="ftd-title">Videos &amp; Manuals</div><div style="margin-bottom:10px;">';
        g.videos.forEach(function(v) {
            html += '<a class="ft-video-link" href="' + ttEsc(v.url) + '" target="_blank" rel="noopener">▶ ' + ttEsc(v.label) + '</a>';
        });
        html += '</div>';
    }
    if ((g.tips || []).length) {
        html += '<div class="ftd-title">Tips &amp; Warnings</div>';
        g.tips.forEach(function(t) { html += '<div class="ft-tip">⚠ <span>' + ttEsc(t) + '</span></div>'; });
    }
    if (g.estimated_time) { html += '<div style="font-size:11.5px;color:#64748b;font-weight:600;">Estimated time: ' + ttEsc(g.estimated_time) + '</div>'; }
    box.innerHTML = html || '<div class="ftd-title">Field Guide</div><div style="font-size:12px;color:#94a3b8;">No guide data for this issue yet.</div>';
}

// Right-column mirror of the checked steps.
function ttRenderLoggedSteps(ticketId) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('logged-' + ticketId);
    if (!box || !S) { return; }
    var steps = ttNormalizeSteps(S.steps || S.steps_performed || []);
    S.steps = steps;
    if (!steps.length) {
        box.innerHTML = '<div style="font-size:12px;color:#cbd5e1;">No steps logged yet — check items in the checklist.</div>';
        return;
    }
    var html = '';
    steps.forEach(function(st, i) {
        html += '<div><strong style="color:#64748b;">Step ' + (i + 1) + '</strong> — ' + ttEsc(st) + '</div>';
    });
    box.innerHTML = html;
}
// ===== Card-level checklist (on-site, mobile-friendly) =====
// "Steps done" toggle on each ticket card: expand the suggested checklist,
// check off what you did, add custom rows, and it all saves to steps_performed.
function ttCardGuideToggle(ticketId, btn) {
    var body  = document.getElementById('fcgb-' + ticketId);
    var count = document.getElementById('ftgc-' + ticketId);
    if (!body) return;
    var open = body.style.display !== 'none';
    body.style.display = open ? 'none' : '';
    if (btn) {
        btn.classList.toggle('open', !open);
        var cv = btn.querySelector('.ft-card-guide-toggle-chevron');
        if (cv) { cv.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)'; }
    }
    if (!open) { ttRenderCardSuggest(ticketId); ttRenderCardDone(ticketId); }
}

function ttRenderCardSuggest(ticketId) {
    var S   = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('fcgs-' + ticketId);
    if (!box) return;
    var guide = S ? (S._guide || {}) : (window._ttCardGuide && window._ttCardGuide[ticketId]);
    var steps = (guide && guide.steps) || [];
    var done  = S ? ttNormalizeSteps(S.steps || S.steps_performed || []) : [];
    if (S) S.steps = done;
    var lower = done.map(function(s){ return String(s).toLowerCase().trim(); });
    var list  = steps.filter(function(s){ return s && lower.indexOf(String(s).toLowerCase().trim()) === -1; });
    if (!list.length) { box.innerHTML = ''; return; }
    var html = '<div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;">Suggested steps — tap to check</div>';
    list.forEach(function(s){
        html += '<div class="ft-chk-row" data-step="' + ttEsc(s) + '" onclick="ttCardGuideCheck(' + ticketId + ', this)">'
              +  '<span class="ft-chk-box"></span><span class="ft-chk-txt">' + ttEsc(s) + '</span></div>';
    });
    box.innerHTML = html;
}

function ttCardGuideCheck(ticketId, rowEl) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    if (!S || !rowEl) return;
    var text = rowEl.getAttribute('data-step') || '';
    if (!text) return;
    var done = ttNormalizeSteps(S.steps || S.steps_performed || []);
    var lower = done.map(function(s){ return String(s).toLowerCase().trim(); });
    var idx = lower.indexOf(text.toLowerCase().trim());
    if (idx !== -1) {
        done.splice(idx, 1);
        rowEl.classList.remove('done');
        var b = rowEl.querySelector('.ft-chk-box'); if (b) b.textContent = '';
    } else {
        done.push(text);
        rowEl.classList.add('done');
        var b = rowEl.querySelector('.ft-chk-box'); if (b) b.textContent = '✓';
    }
    S.steps = done;
    ttSaveSteps(ticketId);
    ttRenderCardDone(ticketId);
    ttRenderCardCount(ticketId);
    ticketRenderReport(ticketId);
}

function ttRenderCardDone(ticketId) {
    var S   = window.ttTicketData && window.ttTicketData[ticketId];
    var box = document.getElementById('fcgd-' + ticketId);
    if (!box) return;
    var steps = S ? ttNormalizeSteps(S.steps || S.steps_performed || []) : [];
    if (S) S.steps = steps;
    if (!steps.length) { box.innerHTML = '<div style="font-size:11.5px;color:#cbd5e1;padding:4px 2px;">No steps logged yet.</div>'; return; }
    var html = '';
    steps.forEach(function(s){
        html += '<div class="ft-chk-row done" data-step="' + ttEsc(s) + '" onclick="ttCardGuideCheck(' + ticketId + ', this)">'
              +  '<span class="ft-chk-box">✓</span><span class="ft-chk-txt">' + ttEsc(s) + '</span>'
              +  '<button type="button" onclick="event.stopPropagation();ttCardGuideRemove(' + ticketId + ', this)" style="margin-left:auto;background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;padding:0 2px;flex-shrink:0;">✕</button></div>';
    });
    box.innerHTML = html;
}

function ttRenderCardCount(ticketId) {
    var el = document.getElementById('ftgc-' + ticketId);
    if (!el) return;
    var S  = window.ttTicketData && window.ttTicketData[ticketId];
    var total = ((S && S._guide && S._guide.steps) || []).length;
    var done  = (S ? ttNormalizeSteps(S.steps || S.steps_performed || []) : []).length;
    el.textContent = done + (total ? ' / ' + total : '');
}

function ttCardGuideAdd(ticketId) {
    var S   = window.ttTicketData && window.ttTicketData[ticketId];
    var inp = document.getElementById('fcga-' + ticketId);
    if (!S || !inp) return;
    var text = inp.value.trim();
    if (!text) { showToast('Type what you did first.', 'warning'); return; }
    S.steps = ttNormalizeSteps(S.steps || S.steps_performed || []);
    var existing = S.steps.map(function(s) { return String(s).toLowerCase().trim(); });
    if (existing.indexOf(text.toLowerCase().trim()) !== -1) {
        showToast('That checklist item is already selected.', 'info');
        inp.value = '';
        return;
    }
    S.steps.push(text);
    S._learnedSteps = S._learnedSteps || [];
    S._learnedSteps.push(text);
    inp.value = '';
    ttSaveSteps(ticketId);
    ttRenderCardDone(ticketId);
    ttRenderCardCount(ticketId);
    ticketRenderReport(ticketId);
}

function ttCardGuideRemove(ticketId, btn) {
    var S   = window.ttTicketData && window.ttTicketData[ticketId];
    var row = btn && btn.closest && btn.closest('.ft-chk-row');
    if (!S || !row) return;
    var text = row.getAttribute('data-step') || '';
    S.steps = ttNormalizeSteps(S.steps || S.steps_performed || []).filter(function(s){ return String(s).toLowerCase().trim() !== text.toLowerCase().trim(); });
    ttSaveSteps(ticketId);
    ttRenderCardDone(ticketId);
    ttRenderCardCount(ticketId);
    ticketRenderReport(ticketId);
}

// "Guides & Tools" quick button on the card → stash the guide + open the drawer.
function ttCardGuideQuickOpen(ticketId, guideData, btn) {
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    if (S) { S._guide = guideData; }
    window._ttCardGuide = window._ttCardGuide || {};
    window._ttCardGuide[ticketId] = guideData;
    openTicketDrawer(ticketId);
}

// Add what you did on site (Enter key support on the card input).
(function(){
    document.addEventListener('click', function(e){
        var addBtn = e.target.closest && e.target.closest('.ft-card-guide-add button');
        if (!addBtn) return;
        var card = addBtn.closest && addBtn.closest('.ft-card-guide-add');
        var inp  = card && card.querySelector('input');
        if (inp) { inp.focus(); }
    });
})();

// ---- Time In: stamps the EXACT current server date + time (action.php) ----
function ticketTimeIn(ticketId) {
    api('/api/tickets/action', { method: 'POST', body: { action: 'timein', id: ticketId } })
        .then(function(res) {
            if (res.success) {
                var S = window.ttTicketData && window.ttTicketData[ticketId];
                if (S) {
                    if (res.session) {
                        Object.keys(res.session).forEach(function(k) {
                            if (res.session[k] !== undefined && res.session[k] !== null) { S[k] = res.session[k]; }
                        });
                    }
                    S.started_at = res.started_at || S.started_at;
                    if (S.status === 'new') { S.status = 'in_progress'; }
                }
                showToast('Time In recorded — ' + (S && S.started_at ? ttFmtTime(S.started_at) : ''), 'success');
                ttCacheReport(ticketId);
                openTicketDrawer(ticketId); // re-render with the actual recorded time
            } else {
                showToast('Time In failed: ' + (res.error || 'unknown'), 'error');
            }
        }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
}

// ---- Time Out: stamps the EXACT current server date + time (timeout.php) ----
// Saves report fields and checked troubleshooting steps on Time Out. Checklist
// items remain approval-pending until a supervisor approves them.
function ticketTimeOut(ticketId) {
    ttCacheReport(ticketId);
    var msgEl    = document.getElementById('to-' + ticketId + '-msg');
    var resultEl = document.getElementById('rep-' + ticketId + '-result');
    var recoEl   = document.getElementById('rep-' + ticketId + '-reco');
    var result      = resultEl ? resultEl.value.trim() : '';
    var reco        = recoEl ? recoEl.value.trim() : '';
    var S = window.ttTicketData && window.ttTicketData[ticketId];
    var checkedSteps = S ? ttNormalizeSteps(S.steps || S.steps_performed || []) : [];
    if (S) { S.steps = checkedSteps; }
    var notesParts = [result, reco].filter(function(p) { return p; });
    if (msgEl) msgEl.textContent = 'Saving Time Out…';
    api('/api/tickets/timeout', { method: 'POST', body: {
        ticket_id: ticketId,
        resolution: checkedSteps.join('\n'),
        resolution_type: 'completed',
        notes: notesParts.join('\n'),
        result_of_checking: result,
        recommendation: reco,
        confirmed_by: ttRepInput(ticketId, 'confirm') || '',
        status: 'solved',
        steps_performed: checkedSteps
    }}).then(function(res) {
        if (msgEl) msgEl.textContent = '';
        if (res.success) {
            var S2 = window.ttTicketData && window.ttTicketData[ticketId];
            if (S2) {
                var sess = res.session || {};
                S2.ended_at = res.ended_at || sess.ended_at || S2.ended_at;
                if (sess.time_spent_minutes !== undefined && sess.time_spent_minutes !== null) { S2.time_spent_minutes = sess.time_spent_minutes; }
                if (sess.resolution) { S2.resolution = sess.resolution; }
                if (sess.result_of_checking !== undefined) { S2.result_of_checking = sess.result_of_checking || ''; }
                if (sess.recommendation !== undefined) { S2.recommendation = sess.recommendation || ''; }
                if (sess.confirmed_by !== undefined) { S2.confirmed_by = sess.confirmed_by || ''; }
                if (sess.status) { S2.status = sess.status; }
                if (sess.steps_performed !== undefined) {
                    S2.steps_performed = sess.steps_performed || '[]';
                    S2.steps = ttNormalizeSteps(sess.steps_performed);
                }
                S2.steps_approved = sess.steps_approved ? true : false;
                S2.steps_approved_by = sess.steps_approved_by || '';
                var issueKey = String(parseInt(S2.issue_id || 0, 10));
                if (issueKey !== '0') {
                    ttIssueFieldOptions[issueKey] = ttIssueFieldOptions[issueKey] || { result: [], recommendation: [] };
                    if (result && ttIssueFieldOptions[issueKey].result.indexOf(result) === -1) ttIssueFieldOptions[issueKey].result.unshift(result);
                    if (reco && ttIssueFieldOptions[issueKey].recommendation.indexOf(reco) === -1) ttIssueFieldOptions[issueKey].recommendation.unshift(reco);
                }
                var companyKey = ttNormalizeName(S2.company_name || '');
                var confirmed = sess.confirmed_by || '';
                if (companyKey && confirmed) {
                    ttCompanyContacts[companyKey] = ttCompanyContacts[companyKey] || [];
                    if (ttCompanyContacts[companyKey].indexOf(confirmed) === -1) ttCompanyContacts[companyKey].unshift(confirmed);
                }
            }
            showToast('Time Out recorded.', 'success');
            openTicketDrawer(ticketId);
        } else {
            showToast('Time Out failed: ' + (res.error || 'unknown'), 'error');
        }
    }).catch(function(err) {
        if (msgEl) msgEl.textContent = '';
        showToast('Error: ' + err.message, 'error');
    });
}

// ---- Done: finalizes the ticket (after Time Out) ----
function ticketDone(ticketId) {
    swalConfirm('Mark as Done?', 'This ticket will be marked as solved and completed.', function() {
        ttCacheReport(ticketId);
        api('/api/tickets/action', { method: 'POST', body: { action: 'resolve', id: ticketId } })
            .then(function(res) {
                if (res.success) {
                    var S = window.ttTicketData && window.ttTicketData[ticketId];
                    if (S) { S.status = 'solved'; }
                    showToast('Ticket marked as Done.', 'success');
                    openTicketDrawer(ticketId);
                } else {
                    showToast('Done failed: ' + (res.error || 'unknown'), 'error');
                }
            }).catch(function(err) { showToast('Error: ' + err.message, 'error'); });
    });
}

// ---- Report generation (exact template for copy/paste reports) ----
function ttRepInput(ticketId, key) {
    var el = document.getElementById('rep-' + ticketId + '-' + key);
    return el ? el.value.trim() : '';
}

// Date as MM/DD/YYYY (e.g. 09/19/2026)
function ttFmtDate(dstr) {
    if (!dstr) return '';
    var d = new Date(String(dstr).replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    return ('0' + (d.getMonth() + 1)).slice(-2) + '/' + ('0' + d.getDate()).slice(-2) + '/' + d.getFullYear();
}

// Time as h:mm AM/PM (e.g. 5:11 PM)
function ttFmtTime(dstr) {
    if (!dstr) return '';
    var d = new Date(String(dstr).replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    var h24 = d.getHours();
    var h = h24 % 12; if (h === 0) h = 12;
    return h + ':' + ('0' + d.getMinutes()).slice(-2) + ' ' + (h24 >= 12 ? 'PM' : 'AM');
}

// Shared report rows: single source of truth for the preview AND the copy text.
// NULL Time In / Time Out render as "00" (visual placeholder for "not started").
function ttReportRows(ticketId, S) {
    var result    = ttRepInput(ticketId, 'result') || S.result_of_checking || '';
    var reco      = ttRepInput(ticketId, 'reco') || S.recommendation || '';
    var confirmed = ttRepInput(ticketId, 'confirm') || S.confirmed_by || S.customer_name || '';
    var problem   = ttReportProblem(S);
    var actionTaken = ttReportActionTaken(S);
    return [
        ['Company Name',       S.company_name || ''],
        ['Ticket#',            S.ticket_number || ('TK-' + (S.id || ticketId))],
        ['Serial Number',      S.serial_number || ''],
        ['Date',               ttFmtDate(S.created_at || S.started_at)],
        ['Time In',            S.started_at ? ttFmtTime(S.started_at) : '00'],
        ['Time Out',           S.ended_at ? ttFmtTime(S.ended_at) : '00'],
        ['Problem/Task',       problem],
        ['Action Taken',       actionTaken],
        ['Result of Checking', result],
        ['Recommendation',     reco],
        ['Confirmed By',       confirmed]
    ];
}

function ttReportActionTaken(S) {
    return ttNormalizeSteps(S.steps || S.steps_performed || []).join('\n');
}

function ttReportProblem(S) {
    if (S.task) { return S.task; }
    if (S.issue_title) { return S.issue_title; }
    var problem = S.problem_description || S.task || S.title || '';
    var task = S.task || '';
    if (task && problem.indexOf(task + '\n') === 0) {
        problem = problem.slice(task.length + 1).trim();
    }
    return problem;
}

function ticketBuildReport(ticketId, session) {
    var S = (window.ttTicketData && window.ttTicketData[ticketId]) || {};
    if (session) { S = Object.assign({}, S, session); }
    return ttReportRows(ticketId, S).map(function(r) {
        var value = String(r[1] == null || r[1] === '' ? '—' : r[1]);
        return r[0] + ': ' + value;
    }).join('\n');
}

function ticketRenderReport(ticketId, session) {
    var reportEl = document.getElementById('report-' + ticketId);
    if (!reportEl) return;
    var S = (window.ttTicketData && window.ttTicketData[ticketId]) || {};
    if (session) { S = Object.assign({}, S, session); }
    var html = '';
    ttReportRows(ticketId, S).forEach(function(r) {
        var label = r[0];
        var value = String(r[1] == null ? '' : r[1]);
        var muted = (value === '' || value === '00');
        html += '<div style="display:grid;grid-template-columns:150px minmax(0,1fr);gap:12px;padding:6px 0;border-bottom:1px solid #eef2f7;align-items:start;">'
              + '<span style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;padding-top:2px;">' + ttEsc(label) + '</span>'
              + '<span style="font-size:12.5px;font-weight:600;color:' + (muted ? '#cbd5e1' : '#111827') + ';text-align:left;word-break:break-word;white-space:pre-wrap;line-height:1.45;">'
              + ttEsc(value === '' ? '—' : value) + '</span></div>';
    });
    // Approval-pending indicator for checklist steps saved but not yet approved by a supervisor.
    var steps = (S.steps && S.steps.length) ? S.steps : [];
    if (steps.length && S.ended_at && !S.steps_approved) {
        html += '<div style="margin-top:8px;padding:6px 10px;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;font-size:10.5px;color:#92400e;font-weight:600;text-align:center;">⏳ Checklist pending approval — visible to you; awaiting supervisor approval before visible to others.</div>';
    }
    reportEl.innerHTML = html;
}

function ticketCopyReport(ticketId) {
    var reportEl = document.getElementById('report-' + ticketId);
    if (!reportEl) { showToast('Report not ready.', 'warning'); return; }
    function copyText(text) {
        if (!text) { showToast('Nothing to copy.', 'warning'); return; }
        if (!navigator.clipboard) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); showToast('✓ Report copied!', 'success'); }
            catch(e) { showToast('Copy failed — select and copy manually.', 'warning'); }
            document.body.removeChild(ta);
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            showToast('✓ Report copied!', 'success');
        }).catch(function() {
            showToast('Clipboard blocked — select and copy manually.', 'warning');
        });
    }
    function buildAndCopy(session) {
        if (session) { ticketRenderReport(ticketId, session); }
        copyText(ticketBuildReport(ticketId, session));
    }
    if (typeof ticketRefreshReportFromDb === 'function') {
        ticketRefreshReportFromDb(ticketId).then(buildAndCopy);
        return;
    }
    buildAndCopy(null);
}

function ticketRegenReport(ticketId) {
    // Re-render from the DB if possible; otherwise from the current DOM state.
    if (typeof ticketRefreshReportFromDb === 'function') {
        ticketRefreshReportFromDb(ticketId).then(function(s) {
            if (s) { ticketRenderReport(ticketId, s); showToast('Report refreshed from database.', 'info'); }
            else { ticketRenderReport(ticketId, null); showToast('Report regenerated from page data.', 'info'); }
        });
        return;
    }
    ticketRenderReport(ticketId, null);
    showToast('Report regenerated from page data.', 'info');
}

function ticketRefreshReportFromDb(ticketId) {
    // Fetch the latest session row so the report reflects the most recent time-out.
    return api('/api/tickets/timein?ticket_id=' + ticketId).then(function(data) {
        var session = data.session || null;
        var S = window.ttTicketData && window.ttTicketData[ticketId];
        if (session && S) {
            Object.keys(session).forEach(function(k) {
                if (session[k] !== undefined && session[k] !== null) { S[k] = session[k]; }
            });
        }
        return session;
    }).catch(function() { return null; });
}

// ---- Route to next nearest ticket (mini-map) ----
function ticketRouteToNext(ticketId) {
    var el = document.getElementById('route-' + ticketId);
    if (!el) { showToast('Route card not found.', 'warning'); return; }
    el.textContent = 'Loading route…';
    api('/api/tickets/route-next?ticket_id=' + ticketId).then(function(data) {
        el.innerHTML = '';
        if (!data || !data.route_next) {
            el.innerHTML = '<div style="color:#475569;font-size:12px;">No next nearest job found. Open a new ticket to continue routing.</div>';
            return;
        }
        var r = data.route_next;
        var company = data.company_address || '';
        var dest = r.destination_addr || 'Unknown destination';
        ttRouteMap(ticketId, r.destination_lat, r.destination_lng, null, null, company, dest);
    }).catch(function() { el.textContent = 'Failed to load route.'; });
}

// ---- MODAL WIRING: attach to the new-ticket-modal events ----
function wireNewTicketModal() {
    var panel = document.getElementById('new-ticket-panel');
    if (!panel) return;
    var next2 = document.getElementById('tt-next-2');
    if (next2) next2.onclick = ticketSubmitTimeIn;
    var next1 = document.getElementById('tt-next-1');
    if (next1) next1.onclick = ticketGoToStep2;

    // Step 1 — device dropdown drives equipment + troubleshooting.
    var deviceSel = document.getElementById('tt-device');
    if (deviceSel) deviceSel.onchange = ttDeviceChanged;
    var deviceOther = document.getElementById('tt-device-other');
    if (deviceOther) deviceOther.oninput = function() { ttDeviceOtherTyped(this.value); };

    var equipSearch = document.getElementById('tt-equip-search');
    if (equipSearch) {
        equipSearch.oninput = function() { ttRenderEquipResults(); };
        equipSearch.onfocus = function() { ttRenderEquipResults(); };
    }
    var modelSearch = document.getElementById('tt-model-search');
    if (modelSearch) modelSearch.oninput = function() { ttModelFreeText(this.value); };

    ttPopulateDeviceDropdown();

    // Step 2 — problem picker
    var issue = document.getElementById('tt-issue');
    if (issue) issue.onchange = ttIssueChanged;

    // Step 2 — address suggestions follow the company typed in Step 1.
    var companyInput = document.getElementById('tt-company');
    if (companyInput) {
        companyInput.onchange = function() {
            ttSuggestionChanged('company');
            ttRenderAddressDatalist();
            // Drop stale coordinates when the company changes mid-form.
            var latInput = document.getElementById('tt-lat');
            var lngInput = document.getElementById('tt-lng');
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            ttApplySavedAddress();
        };
    }
    var companyOther = document.getElementById('tt-company-other');
    if (companyOther) companyOther.oninput = function() { ttOtherSuggestionTyped('company'); ttRenderAddressDatalist(); };

    var taskInput = document.getElementById('tt-task');
    if (taskInput) taskInput.onchange = function() { ttSuggestionChanged('task'); ttIssueChanged(); };
    var taskOther = document.getElementById('tt-task-other');
    if (taskOther) taskOther.oninput = function() { ttOtherSuggestionTyped('task'); ttIssueChanged(); };

    var addrInput = document.getElementById('tt-address');
    if (addrInput) {
        addrInput.addEventListener('change', ttApplySavedAddress);
        addrInput.addEventListener('input', function() {
            // Manual edit: clear old coords so a wrong pin isn't saved.
            var latInput = document.getElementById('tt-lat');
            var lngInput = document.getElementById('tt-lng');
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
        });
    }

    // Time In is recorded automatically by the server on submit, so there is no
    // date/time picker to wire up here.
}

// Ticket cards are rendered server-side; search / sort / filter run inline via
// ticketApplyFilters(). Reports render inside the drawer (openTicketDrawer).

// ==================== End New Ticket ==================== 

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
