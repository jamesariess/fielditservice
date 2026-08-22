<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Field IT Support Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        .login-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #1e3a8a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; position: relative; overflow: hidden; }
        .login-bg::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%); border-radius: 50%; }
        .login-bg::after { content: ''; position: absolute; bottom: -30%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(139,92,246,0.1) 0%, transparent 70%); border-radius: 50%; }
        .login-card { background: #fff; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 420px; padding: 40px; position: relative; z-index: 1; }
        .login-input { width: 100%; padding: 11px 12px 11px 40px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13.5px; font-family: 'Inter', system-ui, sans-serif; outline: none; transition: all 0.15s; background: #f9fafb; }
        .login-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); background: #fff; }
        .login-btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Inter', system-ui, sans-serif; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.4); }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; }
        .input-wrap .toggle-pw { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; }
        .input-wrap .toggle-pw:hover { color: #475569; }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="login-card">
            <!-- Logo -->
            <div style="text-align:center;margin-bottom:28px;">
                <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 12px rgba(37,99,235,0.3);">
                    <i data-lucide="headphones" style="width:26px;height:26px;color:#fff;"></i>
                </div>
                <h1 style="font-size:22px;font-weight:800;color:#111827;letter-spacing:-0.03em;">Field IT Support Hub</h1>
                <p style="font-size:13px;color:#94a3b8;margin-top:4px;">Troubleshooting & Knowledge Management</p>
            </div>

            <h2 style="font-size:17px;font-weight:700;color:#111827;margin-bottom:4px;">Sign in to your account</h2>
            <p style="font-size:13px;color:#64748b;margin-bottom:24px;">Enter your credentials to access the platform</p>

            <div id="login-error" style="display:none;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;color:#991b1b;font-size:13px;margin-bottom:16px;"></div>

            <form id="login-form" onsubmit="handleLogin(event)">
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:5px;">Email Address</label>
                    <div class="input-wrap">
                        <i data-lucide="mail"></i>
                        <input type="email" name="email" required class="login-input" placeholder="you@company.com" value="admin@fieldit.local">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:5px;">Password</label>
                    <div class="input-wrap">
                        <i data-lucide="lock"></i>
                        <input type="password" name="password" required class="login-input" placeholder="Enter your password" value="password" id="pw-input">
                        <button type="button" class="toggle-pw" onclick="togglePw()"><i data-lucide="eye" id="pw-icon" style="width:16px;height:16px;"></i></button>
                    </div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:20px;cursor:pointer;">
                    <input type="checkbox" name="remember" style="width:16px;height:16px;accent-color:#2563eb;border-radius:4px;">
                    <span style="font-size:13px;color:#64748b;">Remember me</span>
                </label>
                <button type="submit" id="login-btn" class="login-btn">
                    <i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign In
                </button>
            </form>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;text-align:center;">
                <p style="font-size:11px;color:#94a3b8;line-height:1.6;">
                    All accounts password: <b>password</b><br>
                    Admin: <b>admin@fieldit.local</b> | Field IT: <b>fieldit@fieldit.local</b>
                </p>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
    <script>
    function togglePw() {
        const inp = document.getElementById('pw-input');
        const icon = document.getElementById('pw-icon');
        if (inp.type === 'password') { inp.type = 'text'; icon.setAttribute('data-lucide','eye-off'); }
        else { inp.type = 'password'; icon.setAttribute('data-lucide','eye'); }
        lucide.createIcons();
    }
    async function handleLogin(e) {
        e.preventDefault();
        const btn = document.getElementById('login-btn');
        const errDiv = document.getElementById('login-error');
        btn.disabled = true;
        btn.innerHTML = '<div style="width:18px;height:18px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></div> Signing in...';
        errDiv.style.display = 'none';
        const fd = new FormData(e.target);
        try {
            const r = await fetch('/api/auth/login', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({email:fd.get('email'),password:fd.get('password'),remember:fd.get('remember')==='on'}) });
            const d = await r.json();
            if (r.ok && d.success) window.location.href = d.redirect || '/';
            else { errDiv.textContent = d.error || 'Invalid credentials'; errDiv.style.display = ''; }
        } catch(err) { errDiv.textContent = 'Connection error.'; errDiv.style.display = ''; }
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign In';
        lucide.createIcons();
    }
    </script>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</body>
</html>
