(function () {
    'use strict';
    var key = 'fieldmate-session-' + window.fieldMateUserId;
    var session = '';
    var history = [];
    var busy = false;
    try { session = sessionStorage.getItem(key) || ''; } catch (e) {}
    function remember() {
        if (!session) session = 'fieldmate_' + window.fieldMateUserId + '_' + Date.now() + '_' + Math.random().toString(36).slice(2);
        try { sessionStorage.setItem(key, session); } catch (e) {}
    }
    remember();
    function message(text, role) {
        var welcome = document.getElementById('fieldmate-welcome');
        if (welcome) welcome.remove();
        var list = document.getElementById('chat-messages');
        var row = document.createElement('div');
        row.className = 'tc-msg' + (role === 'user' ? ' me' : '');
        var avatar = document.createElement('span');
        avatar.className = 'tc-avatar' + (role === 'user' ? '' : ' tc-fieldmate');
        avatar.innerHTML = role === 'user' ? '<i data-lucide="user"></i>' : '<i data-lucide="bot"></i>';
        var wrap = document.createElement('div');
        wrap.className = 'tc-bubble-wrap';
        var label = document.createElement('div');
        label.className = 'tc-msg-top';
        label.textContent = role === 'user' ? 'You' : 'FieldMate';
        var bubble = document.createElement('div');
        bubble.className = 'tc-bubble';
        var displayText = String(text).replace(/\bIT Bot\b/g, 'FieldMate');
        if (role === 'user') bubble.textContent = displayText;
        else displayText.split(/(\*\*[^*]+\*\*)/g).forEach(function (part) {
            if (part.startsWith('**') && part.endsWith('**')) {
                var bold = document.createElement('strong'); bold.textContent = part.slice(2,-2); bubble.append(bold);
            } else bubble.append(document.createTextNode(part));
        });
        wrap.append(label, bubble); row.append(avatar, wrap); list.append(row);
        list.scrollTop = list.scrollHeight;
        if (window.lucide) window.lucide.createIcons();
        return row;
    }
    function controls() {
        document.querySelector('.tc-send').disabled = busy;
        document.querySelector('.tc-new').disabled = busy;
    }
    window.fieldMateSend = function (event) {
        if (event) event.preventDefault();
        var input = document.getElementById('chat-msg-input');
        var text = input.value.trim();
        if (!text || busy) return;
        busy = true; controls();
        var sent = message(text, 'user'); input.value = ''; input.style.height = '';
        var pending = message('Checking your issue...', 'assistant');
        pending.setAttribute('role', 'status');
        api('/api/ai/chat', {method:'POST', body:{message:text, session_id:session, history:history.slice(-20)}}).then(function (data) {
            if (!data.response) throw new Error('No response received. Please retry.');
            pending.remove(); message(data.response, 'assistant');
            history.push({role:'user',content:text}, {role:'assistant',content:data.response});
            if (data.session_id) {session=data.session_id;remember();}
        }).catch(function (error) {
            pending.remove();
            sent.remove();
            input.value = text;
            showToast(error.message || 'Could not send. Your message is ready to retry.', 'error');
        }).finally(function () { busy=false; controls(); input.focus(); });
    };
    window.fieldMateQuick = function (text) {
        if (busy) return;
        document.getElementById('chat-msg-input').value = text;
        window.fieldMateSend();
    };
    window.fieldMateNew = function () {
        if (busy) return;
        try {sessionStorage.removeItem(key);} catch (e) {}
        window.location.href = APP_BASE + 'team-messages?assistant=1';
    };
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('chat-msg-input');
        input.addEventListener('input', function () {input.style.height='auto';input.style.height=Math.min(120,input.scrollHeight)+'px';});
        var viewport = window.visualViewport;
        function fitViewport() {
            var shell=document.querySelector('.team-chat-shell');
            if (!window.matchMedia('(max-width:800px)').matches) {shell.style.height='';document.body.classList.remove('fieldmate-keyboard');return;}
            var keyboard=viewport && window.innerHeight-viewport.height>140 && document.activeElement===input;
            document.body.classList.toggle('fieldmate-keyboard',!!keyboard);
            var nav=document.querySelector('.bottom-nav');
            var bottom=keyboard ? 0 : (nav ? nav.getBoundingClientRect().height : 72);
            var available=(viewport ? viewport.height+viewport.offsetTop : window.innerHeight)-shell.getBoundingClientRect().top-bottom-12;
            shell.style.height=Math.max(180,available)+'px';
        }
        if(viewport) {viewport.addEventListener('resize',fitViewport);viewport.addEventListener('scroll',fitViewport);}
        window.addEventListener('resize',fitViewport);input.addEventListener('focus',fitViewport);input.addEventListener('blur',fitViewport);fitViewport();
        busy=true;controls();
        api('/api/ai/chat?session=' + encodeURIComponent(session)).then(function (data) {
            history = Array.isArray(data.history) ? data.history : [];
            history.forEach(function (turn) {message(turn.content,turn.role);});
        }).catch(function (error) {showToast(error.message || 'Could not load FieldMate history.', 'error');})
        .finally(function () {busy=false;controls();});
    });
})();
