<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

if (session_status() === PHP_SESSION_NONE) session_start();
$uBase = '/';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Field IT Support Hub</title>
    <link rel="icon" type="image/svg+xml" href="<?= $uBase ?>assets/img/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        .login-bg { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; overflow: hidden; background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #172554 100%); }
        .login-bg::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px); background-size: 44px 44px; -webkit-mask-image: radial-gradient(900px 620px at 50% 28%, #000 25%, transparent 75%); mask-image: radial-gradient(900px 620px at 50% 28%, #000 25%, transparent 75%); }
        .orb { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .5; animation: floatOrb 16s ease-in-out infinite; pointer-events: none; }
        .orb-1 { width: 480px; height: 480px; top: -150px; right: -130px; background: radial-gradient(circle, rgba(37,99,235,.55), transparent 70%); }
        .orb-2 { width: 420px; height: 420px; bottom: -170px; left: -130px; background: radial-gradient(circle, rgba(139,92,246,.5), transparent 70%); animation-delay: -6s; }
        .orb-3 { width: 300px; height: 300px; top: 38%; left: 58%; background: radial-gradient(circle, rgba(14,165,233,.4), transparent 70%); animation-delay: -10s; }
        @keyframes floatOrb { 0%, 100% { transform: translate(0,0) scale(1); } 33% { transform: translate(34px,-44px) scale(1.08); } 66% { transform: translate(-26px,30px) scale(.94); } }

        .login-card { position: relative; z-index: 2; width: 100%; max-width: 430px; background: rgba(255,255,255,.97); border-radius: 24px; padding: 38px 38px 28px; box-shadow: 0 30px 60px -15px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.08) inset; animation: cardIn .7s cubic-bezier(.16,1,.3,1) both; }
        @keyframes cardIn { from { opacity: 0; transform: translateY(28px) scale(.95); } to { opacity: 1; transform: none; } }

        .brand-mark { position: relative; width: 60px; height: 60px; margin: 0 auto 16px; border-radius: 18px; background: linear-gradient(135deg, #2563eb, #7c3aed); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(37,99,235,.35); animation: markIn .7s .12s cubic-bezier(.34,1.56,.64,1) both; }
        .brand-mark::after { content: ''; position: absolute; inset: -3px; border-radius: 21px; background: linear-gradient(135deg, #3b82f6, #a855f7); filter: blur(12px); z-index: -1; opacity: .5; animation: glowPulse 3.2s ease-in-out infinite; }
        .brand-mark img { width: 32px; height: 32px; display: block; }
        @keyframes markIn { from { opacity: 0; transform: scale(.4) rotate(-14deg); } to { opacity: 1; transform: none; } }
        @keyframes glowPulse { 0%, 100% { opacity: .4; } 50% { opacity: .75; } }

        .login-title { font-size: 21px; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; text-align: center; }
        .login-sub { font-size: 13px; color: #64748b; margin: 6px auto 26px; text-align: center; max-width: 280px; line-height: 1.5; }
        .login-head-2 { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 3px; }
        .login-head-p { font-size: 12.5px; color: #64748b; margin-bottom: 18px; }

        .input-wrap { position: relative; margin-bottom: 14px; }
        .input-wrap > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; color: #94a3b8; pointer-events: none; transition: color .2s; }
        .login-input { width: 100%; padding: 12px 44px 12px 42px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 13.5px; font-family: 'Inter', system-ui, sans-serif; outline: none; background: #f8fafc; color: #0f172a; transition: all .25s cubic-bezier(.4,0,.2,1); }
        .login-input:focus { background: #fff; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }
        .input-wrap:focus-within > i { color: #2563eb; }
        .toggle-pw { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 6px; border-radius: 8px; transition: all .2s; }
        .toggle-pw:hover { color: #2563eb; background: #eff6ff; }

        .login-btn { position: relative; overflow: hidden; width: 100%; padding: 13px; margin-top: 4px; background: linear-gradient(135deg, #2563eb, #1d4ed8 60%, #4f46e5); background-size: 180% 180%; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; font-family: 'Inter', system-ui, sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 6px 18px rgba(37,99,235,.3); transition: all .25s; }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(37,99,235,.4); background-position: 100% 0; }
        .login-btn:active { transform: translateY(0) scale(.98); }
        .login-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%); transform: translateX(-140%); }
        .login-btn:hover::after { transform: translateX(140%); transition: transform .8s ease; }

        .login-error { display: none; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b; font-size: 13px; margin-bottom: 14px; font-weight: 500; }

        .demo-box { margin-top: 22px; padding: 12px 14px; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center; color: #94a3b8; font-size: 11px; line-height: 1.7; }
        .demo-box b { color: #475569; }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="login-card">
            <!-- Brand -->
            <div class="brand-mark"><img src="<?= $uBase ?>assets/img/logo.svg" alt="Field IT Support Hub"></div>
            <h1 class="login-title">Field IT Support Hub</h1>
            <p class="login-sub">Troubleshooting &amp; Knowledge Management for your field team</p>

            <div class="login-head-2">Sign in to your account</div>
            <p class="login-head-p">Enter your credentials to access the platform</p>

            <div id="login-error" class="login-error"></div>

            <form id="login-form" onsubmit="handleLogin(event)">
                <div style="margin-bottom:6px;">
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;">Email Address</label>
                </div>
                <div class="input-wrap">
                    <i data-lucide="mail"></i>
                    <input type="email" name="email" required class="login-input" placeholder="you@company.com" value="admin@fieldit.local" autocomplete="email">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:2px 0 6px;">
                    <label style="font-size:12.5px;font-weight:600;color:#374151;">Password</label>
                    <a href="#" onclick="return false;" style="font-size:11.5px;font-weight:600;color:#2563eb;text-decoration:none;">Forgot password?</a>
                </div>
                <div class="input-wrap">
                    <i data-lucide="lock"></i>
                    <input type="password" name="password" required class="login-input" id="pw-input" placeholder="Enter your password" value="password" autocomplete="current-password">
                    <button type="button" class="toggle-pw" onclick="togglePw()" tabindex="-1"><i data-lucide="eye" id="pw-icon" style="width:16px;height:16px;"></i></button>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin:4px 0 16px;cursor:pointer;">
                    <input type="checkbox" name="remember" style="width:16px;height:16px;accent-color:#2563eb;border-radius:4px;">
                    <span style="font-size:13px;color:#64748b;">Remember me</span>
                </label>
                <button type="submit" id="login-btn" class="login-btn">
                    <i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign In
                </button>
            </form>

            <div class="demo-box">
                All accounts password: <b>password</b><br>
                Admin: <b>admin@fieldit.local</b> · Field IT: <b>fieldit@fieldit.local</b>
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
            var base = (window.location.pathname.match(/^(.*\/public)/) || ['',''])[1]; const r = await fetch(base + '/api/auth/login', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({email:fd.get('email'),password:fd.get('password'),remember:fd.get('remember')==='on'}) });
            const d = await r.json();
            if (r.ok && d.success) window.location.href = (d.redirect || '/').replace(/^\//, base + '/');
            else { errDiv.innerHTML = '<i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i><span>' + (d.error || 'Invalid credentials') + '</span>'; errDiv.style.display = 'flex'; lucide.createIcons(); }
        } catch(err) { errDiv.innerHTML = '<i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i><span>Connection error.</span>'; errDiv.style.display = 'flex'; lucide.createIcons(); }
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign In';
        lucide.createIcons();
    }
    </script>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</body>
</html>
