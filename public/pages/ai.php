<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

$page_title = 'IT Support AI';
$active_menu = 'ai';
require APP_ROOT . '/includes/layout_header.php';
?>

<style>
.chat-container {
    max-width: 880px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 8rem);
    padding: 12px 0 0;
}
.chat-card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: blur(18px);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}
.dark .chat-card {
    background: rgba(15, 23, 42, 0.82);
    border-color: rgba(148, 163, 184, 0.2);
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.35);
}
.chat-header {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    display: flex;
    align-items: center;
    gap: 14px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(59, 130, 246, 0.04));
}
.chat-header-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #8b5cf6, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 24px rgba(99, 102, 241, 0.24);
    flex-shrink: 0;
}
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 22px 20px 14px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.45), rgba(255, 255, 255, 0));
}
.dark .chat-messages {
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.15), rgba(15, 23, 42, 0));
}
.chat-input-area {
    padding: 16px 20px 18px;
    border-top: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(255, 255, 255, 0.52);
}
.dark .chat-input-area {
    background: rgba(15, 23, 42, 0.38);
}
.chat-input-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}
.chat-input {
    flex: 1;
    padding: 14px 16px;
    border: 1px solid rgba(148, 163, 184, 0.5);
    border-radius: 16px;
    font-size: 14px;
    resize: none;
    outline: none;
    transition: all 0.18s ease;
    font-family: inherit;
    line-height: 1.5;
    max-height: 120px;
    overflow-y: auto;
    background: rgba(255, 255, 255, 0.92);
    color: #0f172a;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}
.dark .chat-input {
    background: rgba(15, 23, 42, 0.85);
    border-color: rgba(148, 163, 184, 0.33);
    color: #f8fafc;
}
.chat-input:focus {
    border-color: rgba(37, 99, 235, 0.9);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}
.chat-send-btn {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.18s ease;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
    flex-shrink: 0;
}
.chat-send-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 24px rgba(37, 99, 235, 0.34);
}
.bubble-ai {
    background: rgba(241, 245, 249, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 18px;
    padding: 14px 16px;
    max-width: min(82%, 640px);
    line-height: 1.65;
    font-size: 14px;
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.04);
}
.dark .bubble-ai {
    background: rgba(15, 23, 42, 0.72);
    border-color: rgba(148, 163, 184, 0.15);
}
.bubble-user {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border-radius: 18px;
    padding: 12px 16px;
    max-width: min(72%, 480px);
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
}
.quick-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid rgba(148, 163, 184, 0.18);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.quick-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}
.src-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    background: rgba(148, 163, 184, 0.12);
    color: #475569;
}
.dark .src-badge {
    background: rgba(148, 163, 184, 0.12);
    color: #cbd5e1;
}
.conf-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
}
.typing-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #94a3b8;
    animation: typing 1.4s infinite ease-in-out;
}
.typing-dot:nth-child(2) { animation-delay: 0.15s; }
.typing-dot:nth-child(3) { animation-delay: 0.3s; }
@keyframes typing {
    0%, 60%, 100% { opacity: 0.35; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-3px); }
}
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.45);
    border-radius: 999px;
}
@media (max-width: 640px) {
    .chat-container { padding-top: 0; }
    .chat-card { border-radius: 18px; }
    .chat-header { padding: 14px 16px; }
    .chat-messages { padding: 18px 16px 12px; }
    .chat-input-area { padding: 12px 16px 14px; }
    .quick-btn { font-size: 11px; }
}
</style>

<div class="chat-container">
    <div class="chat-card">
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-avatar">
                <i data-lucide="bot" style="width:20px;height:20px;color:#fff;"></i>
            </div>
            <div style="flex:1;">
                <h1 style="font-size:15px;font-weight:700;color:#111827;margin:0;">IT Bot</h1>
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#16a34a;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                    Online — AI-Powered Assistant
                </div>
            </div>
            <div style="display:flex;gap:6px;align-items:center;">
                <span id="ai-quota-chip" style="display:none;font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;background:rgba(139,92,246,.12);color:#7c3aed;"></span>
                <span id="training-badge" style="font-size:10px;color:#94a3b8;"></span>
                <button onclick="clearChat()" style="padding:5px 10px;font-size:11px;font-weight:600;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">New Chat</button>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" class="chat-messages custom-scroll"></div>

        <!-- Quick Start -->
        <div id="quick-start" style="padding:0 20px 12px;display:flex;gap:8px;flex-wrap:wrap;">
            <button class="quick-btn" style="background:#eff6ff;color:#2563eb;" onclick="aiSendQuick('No display on my monitor')">
                <i data-lucide="monitor" style="width:12px;height:12px;"></i> No Display
            </button>
            <button class="quick-btn" style="background:#fef2f2;color:#dc2626;" onclick="aiSendQuick('Computer won\\'t turn on')">
                <i data-lucide="power" style="width:12px;height:12px;"></i> No Power
            </button>
            <button class="quick-btn" style="background:#f0fdf4;color:#16a34a;" onclick="aiSendQuick('WiFi not connecting')">
                <i data-lucide="wifi" style="width:12px;height:12px;"></i> Network
            </button>
            <button class="quick-btn" style="background:#fffbeb;color:#d97706;" onclick="aiSendQuick('Computer is running slow')">
                <i data-lucide="gauge" style="width:12px;height:12px;"></i> Slow PC
            </button>
            <button class="quick-btn" style="background:#fff7ed;color:#ea580c;" onclick="aiSendQuick('Printer not printing')">
                <i data-lucide="printer" style="width:12px;height:12px;"></i> Printer
            </button>
            <button class="quick-btn" style="background:#f5f3ff;color:#7c3aed;" onclick="aiSendQuick('Blue screen BSOD error')">
                <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i> BSOD
            </button>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <form id="chat-form" onsubmit="aiSendMessage(event)" class="chat-input-form">
                <textarea id="chat-input" rows="1" class="chat-input" placeholder="Ask IT Bot anything about your technical issue..."
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();aiSendMessage(event);}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px';"></textarea>
                <button type="submit" class="chat-send-btn" id="send-btn">
                    <i data-lucide="send" style="width:17px;height:17px;"></i>
                </button>
            </form>
            <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:10px;color:#94a3b8;">Powered by IT Bot — Responses should be verified against approved procedures.</span>
                <span style="font-size:10px;color:#94a3b8;">Enter to send · Shift+Enter for new line</span>
            </div>
        </div>
    </div>
</div>

<script>
var aiProcessing = false;
var aiSessionId = 'session_' + Date.now();
var aiHistory = [];
var botName = 'IT Bot';

// Load personality and history on init
(function initChat() {
    fetch(APP_BASE + 'api/ai/chat', { method: 'GET' })
        .then(r => r.json())
        .then(data => {
            if (data.personality) {
                botName = data.personality.bot_name || 'IT Bot';
                document.querySelector('.chat-header h1').textContent = botName;
                addWelcomeMessage(data.personality.greeting);
            }
            if (data.training_count > 0) {
                document.getElementById('training-badge').textContent = data.training_count + ' trained docs';
            }
            updateQuotaChip(data.quota);
            if (data.history && data.history.length > 0) {
                data.history.forEach(function(msg) {
                    addMessage(msg.content, msg.role === 'user' ? 'user' : 'ai', false);
                    aiHistory.push({ role: msg.role === 'user' ? 'user' : 'assistant', content: msg.content });
                });
                scheduleRatingPrompt();
            }
        })
        .catch(() => addWelcomeMessage("Hi! I'm **IT Bot**, your IT support assistant. How can I help you today?"));
})();

function addWelcomeMessage(text) {
    addMessage(text, 'ai', false);
    lucide.createIcons();
}

function aiSendQuick(msg) {
    var inp = document.getElementById('chat-input');
    if (inp) { inp.value = msg; aiSendMessage(); }
    document.getElementById('quick-start').style.display = 'none';
}

function aiSendMessage(e) {
    if (e) e.preventDefault();
    var input = document.getElementById('chat-input');
    if (!input) return;
    var msg = input.value.trim();
    if (!msg || aiProcessing) return;
    aiProcessing = true;
    clearRatingPrompt();
    if (sessionEnded) startNewConversation();
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('quick-start').style.display = 'none';
    addMessage(msg, 'user');
    var tid = addTyping();
    
    fetch(APP_BASE + 'api/ai/chat', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: msg, session_id: aiSessionId, history: aiHistory.slice(-6) })
    })
    .then(r => r.json())
    .then(data => {
        removeTyping(tid);
        if (data.error) {
            addMessage('⚠️ ' + data.error, 'ai');
            aiProcessing = false;
            return;
        }
        addMessage(data.response, 'ai', true, data.sources, data.confidence);
        aiHistory.push({ role: 'user', content: msg });
        aiHistory.push({ role: 'assistant', content: data.response });
        aiProcessing = false;
        scheduleRatingPrompt();
        updateQuotaChip(data.quota);
    })
    .catch(() => {
        removeTyping(tid);
        addMessage("Sorry, I encountered an error. Please try again or describe your issue differently.", 'ai');
        aiProcessing = false;
    });
}

function updateQuotaChip(quota) {
    var el = document.getElementById('ai-quota-chip');
    if (!el || !quota) return;
    if (quota.unlimited) {
        el.textContent = '∞ Unlimited';
        el.style.display = '';
        el.style.background = 'rgba(34,197,94,.12)';
        el.style.color = '#059669';
    } else if (typeof quota.remaining_today === 'number') {
        el.textContent = quota.remaining_today + ' AI messages left today';
        el.style.display = '';
        if (quota.remaining_today <= 5) { el.style.background = 'rgba(239,68,68,.12)'; el.style.color = '#dc2626'; }
        else if (quota.remaining_today <= 15) { el.style.background = 'rgba(245,158,11,.15)'; el.style.color = '#b45309'; }
        else { el.style.background = 'rgba(139,92,246,.12)'; el.style.color = '#7c3aed'; }
    }
}

// ==================== Conversation rating (after 10 min inactivity) ====================
var RATING_DELAY_MS = 10 * 60 * 1000; // 10 minutes
var ratingTimer = null;
var chatRated = false;
var sessionEnded = false;

function scheduleRatingPrompt() {
    clearTimeout(ratingTimer);
    if (chatRated) return;
    ratingTimer = setTimeout(showRatingCard, RATING_DELAY_MS);
}

function clearRatingPrompt() {
    clearTimeout(ratingTimer);
    var card = document.getElementById('conv-rating-card');
    if (card) card.remove();
}

function showRatingCard() {
    if (chatRated || aiProcessing || aiHistory.length === 0) return;
    var c = document.getElementById('chat-messages');
    if (!c || document.getElementById('conv-rating-card')) return;
    var stars = '';
    for (var i = 1; i <= 5; i++) {
        stars += '<span class="rate-star" data-v="' + i + '" onmouseover="hoverStars(' + i + ')" onmouseout="resetStars()" onclick="pickStar(' + i + ')" style="font-size:26px;cursor:pointer;color:#cbd5e1;transition:color .15s,transform .15s;display:inline-block;">★</span>';
    }
    var html = '<div id="conv-rating-card" style="display:flex;flex-direction:column;align-items:center;margin:18px auto;max-width:420px;" class="anim-scale">';
    html += '<div style="display:flex;gap:10px;align-items:flex-start;width:100%;">';
    html += '<div style="width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="star" style="width:14px;height:14px;color:#fff;"></i></div>';
    html += '<div class="bubble-ai" style="max-width:100%;width:100%;">';
    html += '<div style="font-size:14px;font-weight:700;margin-bottom:2px;">How was this conversation?</div>';
    html += '<div style="font-size:12px;color:#64748b;margin-bottom:10px;">Your rating helps us improve ' + escHtml(botName) + '.</div>';
    html += '<div id="rate-stars" style="text-align:center;margin-bottom:10px;">' + stars + '</div>';
    html += '<textarea id="rate-comment" placeholder="Anything we could do better? (optional)" rows="2" style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12.5px;font-family:inherit;resize:vertical;" onfocus="this.style.borderColor=\'#8b5cf6\'" onblur="this.style.borderColor=\'#e2e8f0\'"></textarea>';
    html += '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">';
    html += '<button onclick="dismissRatingCard(this)" style="padding:7px 14px;font-size:12px;font-weight:600;background:#f1f5f9;color:#475569;border:none;border-radius:8px;cursor:pointer;">Later</button>';
    html += '<button id="rate-submit" onclick="submitConvRating(this)" disabled style="padding:7px 14px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;border:none;border-radius:8px;cursor:pointer;opacity:.5;transition:opacity .2s;">Submit</button>';
    html += '</div></div></div></div>';
    c.insertAdjacentHTML('beforeend', html);
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
}

var _starVal = 0;
function pickStar(v) {
    _starVal = v;
    paintStars(v);
    var b = document.getElementById('rate-submit');
    if (b) { b.disabled = false; b.style.opacity = '1'; }
}
function hoverStars(v) { paintStars(v); }
function resetStars() { paintStars(_starVal); }
function paintStars(active) {
    document.querySelectorAll('#rate-stars .rate-star').forEach(function(s) {
        var on = parseInt(s.dataset.v) <= active;
        s.style.color = on ? '#f59e0b' : '#cbd5e1';
        s.style.transform = on ? 'scale(1.12)' : 'scale(1)';
    });
}

function submitConvRating(btn) {
    if (_starVal < 1) return;
    var comment = (document.getElementById('rate-comment') || {}).value || '';
    btn.disabled = true; btn.textContent = 'Sending…'; btn.style.opacity = '.6';
    fetch(APP_BASE + 'api/ai/rate-conversation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ session_id: aiSessionId, rating: _starVal, comment: comment })
    })
    .then(function(r) { return r.json(); })
    .then(function() {
        chatRated = true;
        sessionEnded = true;
        clearTimeout(ratingTimer);
        appendConvDivider('Conversation ended');
        var card = document.getElementById('conv-rating-card');
        if (card) {
            card.innerHTML = '<div class="bubble-ai" style="display:flex;align-items:center;gap:8px;"><i data-lucide="party-popper" style="width:16px;height:16px;color:#16a34a;"></i><span style="font-size:13px;font-weight:600;">Thanks for your feedback!</span></div>';
            try { lucide.createIcons(); } catch(e) {}
            setTimeout(function() { card.style.transition = 'opacity .6s'; card.style.opacity = '0'; setTimeout(function() { if (card) card.remove(); }, 650); }, 3500);
        }
    })
    .catch(function() {
        btn.disabled = false; btn.textContent = 'Submit'; btn.style.opacity = '1';
        if (typeof showToast === 'function') showToast('Could not send rating. Try again.', 'error');
    });
}

function dismissRatingCard() {
    var card = document.getElementById('conv-rating-card');
    if (card) card.remove();
    scheduleRatingPrompt(); // offer again after another 10 quiet minutes
}

function appendConvDivider(text) {
    var c = document.getElementById('chat-messages');
    if (!c) return;
    var html = '<div style="display:flex;align-items:center;gap:12px;margin:18px 0;" class="fade-in">'
             + '<div style="flex:1;height:1px;background:linear-gradient(90deg,transparent,#e2e8f0,transparent);"></div>'
             + '<span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">' + escHtml(text) + '</span>'
             + '<div style="flex:1;height:1px;background:linear-gradient(90deg,#e2e8f0,transparent);"></div>'
             + '</div>';
    c.insertAdjacentHTML('beforeend', html);
    c.scrollTop = c.scrollHeight;
}

function startNewConversation() {
    aiSessionId = 'session_' + Date.now(); // fresh thread on the server too
    aiHistory = [];
    chatRated = false;
    sessionEnded = false;
    appendConvDivider('New conversation');
    scheduleRatingPrompt(); // timer runs again for the new conversation
}

function addMessage(text, type, showFeedback, sources, confidence) {
    var c = document.getElementById('chat-messages');
    if (!c) return;
    var isUser = type === 'user';
    var html = '<div style="display:flex;gap:10px;' + (isUser ? 'flex-direction:row-reverse;' : '') + 'padding-left:' + (isUser ? '60px' : '0') + ';padding-right:' + (!isUser ? '60px' : '0') + ';">';
    
    if (!isUser) {
        html += '<div style="width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">';
        html += '<i data-lucide="bot" style="width:14px;height:14px;color:#fff;"></i></div>';
    }
    
    if (isUser) {
        html += '<div class="bubble-user">' + escHtml(text) + '</div>';
    } else {
        html += '<div class="bubble-ai"><div class="ai-content">' + formatMarkdown(text) + '</div>';
        
        if (showFeedback) {
            html += '<div style="margin-top:10px;padding-top:8px;border-top:1px solid #e2e8f0;display:flex;gap:6px;align-items:center;">';
            html += '<button onclick="aiRate(this,\'yes\')" style="padding:3px 10px;font-size:11px;font-weight:600;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:16px;cursor:pointer;">✓ Helpful</button>';
            html += '<button onclick="aiRate(this,\'no\')" style="padding:3px 10px;font-size:11px;font-weight:600;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:16px;cursor:pointer;">✗ Not helpful</button>';
            if (confidence) {
                var cc = confidence === 'high' ? '#16a34a' : confidence === 'medium' ? '#d97706' : '#dc2626';
                html += '<span class="conf-badge" style="background:' + cc + '15;color:' + cc + ';">' + confidence.toUpperCase() + '</span>';
            }
            html += '</div>';
        }
        if (sources && sources.length) {
            html += '<div style="margin-top:6px;display:flex;gap:4px;flex-wrap:wrap;">';
            sources.forEach(function(s) { html += '<span class="src-badge">' + s + '</span>'; });
            html += '</div>';
        }
        html += '</div>';
    }
    html += '</div>';
    c.insertAdjacentHTML('beforeend', html);
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
}

function formatMarkdown(text) {
    if (!text) return '';
    var html = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`(.*?)`/g, '<code style="padding:2px 6px;background:#e2e8f0;border-radius:4px;font-size:12px;font-family:monospace;">$1</code>')
        .replace(/### (.*?)(\n|$)/g, '<h4 style="font-weight:700;font-size:14px;margin:16px 0 8px;color:#0f172a;">$1</h4>')
        .replace(/## (.*?)(\n|$)/g, '<h3 style="font-weight:700;font-size:15px;margin:18px 0 10px;color:#0f172a;">$1</h3>')
        .replace(/_(.*?)_/g, '<em style="color:#64748b;">$1</em>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" style="color:#2563eb;text-decoration:underline;">$1</a>');
    // Handle lists and paragraphs
    var lines = html.split('\n');
    var result = '';
    var inCode = false;
    var lastWasEmpty = false;
    lines.forEach(function(line) {
        if (line.trim().startsWith('```')) { inCode = !inCode; lastWasEmpty = false; return; }
        if (inCode) { result += '<div style="padding:4px 10px;font-size:12px;font-family:monospace;background:#1e293b;color:#e2e8f0;border-radius:4px;margin:2px 0;">' + line + '</div>'; lastWasEmpty = false; return; }
        if (line.trim() === '') {
            if (!lastWasEmpty) result += '<div style="height:10px;"></div>';
            lastWasEmpty = true;
            return;
        }
        lastWasEmpty = false;
        if (line.trim().startsWith('- ')) {
            result += '<div style="padding:3px 0 3px 18px;position:relative;line-height:1.5;"><span style="position:absolute;left:2px;color:#2563eb;">•</span>' + line.substring(2) + '</div>';
        } else if (/^\d+\.\s/.test(line.trim())) {
            var m = line.trim().match(/^(\d+)\.\s(.*)$/);
            if (m) result += '<div style="padding:3px 0 3px 22px;position:relative;line-height:1.5;"><span style="position:absolute;left:0;font-weight:700;color:#2563eb;font-size:12px;">' + m[1] + '.</span>' + m[2] + '</div>';
        } else if (line.trim().startsWith('→') || line.trim().startsWith('►')) {
            result += '<div style="padding:3px 0 3px 18px;position:relative;line-height:1.5;color:#475569;"><span style="position:absolute;left:0;color:#8b5cf6;">→</span>' + line.trim().substring(1) + '</div>';
        } else {
            result += '<div style="line-height:1.6;">' + line + '</div>';
        }
    });
    return result;
}

function addTyping() {
    var c = document.getElementById('chat-messages');
    var id = 't-' + Date.now();
    c.insertAdjacentHTML('beforeend', '<div id="' + id + '" style="display:flex;gap:10px;"><div style="width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="bot" style="width:14px;height:14px;color:#fff;"></i></div><div style="padding:12px 16px;background:#f1f5f9;border-radius:16px;display:flex;gap:5px;"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div></div>');
    c.scrollTop = c.scrollHeight;
    try { lucide.createIcons(); } catch(e) {}
    return id;
}
function removeTyping(id) { var el = document.getElementById(id); if (el) el.remove(); }
function aiRate(btn, r) { btn.parentElement.innerHTML = '<span style="font-size:11px;color:#64748b;">Thanks for your feedback! 👍</span>'; }
function clearChat() {
    aiHistory = [];
    aiSessionId = 'session_' + Date.now();
    document.getElementById('chat-messages').innerHTML = '';
    document.getElementById('quick-start').style.display = 'flex';
    addWelcomeMessage("Hi! I'm **" + botName + "**, your IT support assistant. How can I help you today?");
}
function escHtml(s) { return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }

lucide.createIcons();
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
