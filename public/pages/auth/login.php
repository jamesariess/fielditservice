<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

if (session_status() === PHP_SESSION_NONE) session_start();
// App base path (/, /fielditservice/, ...) — injected into JS below for fetch() URLs.
$uBase = app_base();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Field IT Support Hub</title>
    <meta name="csrf-token" content="<?= Auth::generateCsrfToken() ?>">
    <link rel="icon" type="image/svg+xml" href="<?= $uBase ?>assets/img/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; color: #182235; }
        .login-bg { min-height: 100vh; display:flex; align-items:center; justify-content:center; padding:32px; background:#eaf0f6; background-image:linear-gradient(#dbe4ee 1px,transparent 1px),linear-gradient(90deg,#dbe4ee 1px,transparent 1px); background-size:44px 44px; }
        .login-shell { width:min(1080px,100%); min-height:640px; display:grid; grid-template-columns:minmax(360px,.94fr) minmax(400px,1.06fr); overflow:hidden; border:1px solid #cfd9e5; border-radius:12px; background:#fff; box-shadow:0 24px 56px rgba(30,41,59,.16); }
        .login-context { position:relative; display:flex; flex-direction:column; padding:44px; overflow:hidden; color:#e7edf5; background:#10243d; }
        .login-context::before { content:''; position:absolute; inset:0; opacity:.18; background-image:linear-gradient(rgba(255,255,255,.25) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.25) 1px,transparent 1px); background-size:38px 38px; }
        .context-brand,.context-copy,.context-footer { position:relative; z-index:1; }
        .context-brand { display:flex; align-items:center; gap:12px; font-size:14px; font-weight:800; letter-spacing:0; }
        .context-brand-mark { display:grid; place-items:center; width:38px; height:38px; border-radius:8px; background:#2563eb; box-shadow:inset 0 0 0 1px rgba(255,255,255,.18); }
        .context-brand-mark img { width:23px; height:23px; }
        .context-copy { margin:auto 0; max-width:330px; }
        .context-kicker { display:flex; align-items:center; gap:8px; color:#7dd3fc; font-size:11px; font-weight:800; letter-spacing:0; text-transform:uppercase; }
        .context-kicker i { width:15px; height:15px; }
        .context-copy h1 { margin:16px 0 14px; color:#fff; font-size:35px; line-height:1.12; letter-spacing:0; }
        .context-copy p { color:#b9c8d9; font-size:14px; line-height:1.7; }
        .context-status { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:30px; }
        .context-status div { padding:12px 0; border-top:1px solid rgba(203,213,225,.24); color:#cbd5e1; font-size:12px; }
        .context-status strong { display:block; margin-bottom:3px; color:#fff; font-size:13px; }
        .context-footer { color:#8fa4bb; font-size:11px; }

        .login-card { display:flex; flex-direction:column; justify-content:center; width:100%; padding:58px clamp(34px,6vw,76px); background:#fff; }
        .form-brand { display:flex; align-items:center; gap:11px; margin-bottom:44px; color:#52627a; font-size:12px; font-weight:700; }
        .form-brand .brand-mark { display:grid; place-items:center; width:36px; height:36px; border-radius:8px; border:1px solid #d8e4f0; background:#eff6ff; }
        .form-brand .brand-mark img { width:21px; height:21px; }
        .login-title { font-size:27px; line-height:1.18; font-weight:800; color:#172033; letter-spacing:0; }
        .login-sub { font-size:13.5px; color:#66758a; margin:9px 0 30px; line-height:1.55; }
        .login-head-2 { font-size:14px; font-weight:800; color:#263348; margin-bottom:5px; }
        .login-head-p { font-size:12.5px; color:#758399; margin-bottom:20px; }

        .input-wrap { position: relative; margin-bottom: 14px; }
        .input-wrap > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; color: #94a3b8; pointer-events: none; transition: color .2s; }
        .login-input { width: 100%; padding: 12px 44px 12px 42px; border: 1px solid #cfdbe8; border-radius:8px; font-size:13.5px; font-family:'Inter',system-ui,sans-serif; outline:none; background:#fbfcfe; color:#172033; transition:border-color .2s,box-shadow .2s; }
        .login-input:focus { background:#fff; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .input-wrap:focus-within > i { color: #2563eb; }
        .toggle-pw { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 6px; border-radius: 8px; transition: all .2s; }
        .toggle-pw:hover { color: #2563eb; background: #eff6ff; }

        .login-btn { width:100%; min-height:46px; margin-top:4px; background:#2563eb; color:#fff; border:0; border-radius:8px; font-size:14px; font-weight:700; font-family:'Inter',system-ui,sans-serif; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 5px 12px rgba(37,99,235,.22); transition:background .2s,transform .2s,box-shadow .2s; }
        .login-btn:hover { background:#1d4ed8; transform:translateY(-1px); box-shadow:0 8px 16px rgba(37,99,235,.28); }
        .login-btn:active { transform:translateY(0); }

        .login-error { display: none; padding: 10px 14px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b; font-size: 13px; margin-bottom: 14px; font-weight: 500; }

        .login-security { display:flex; align-items:center; gap:7px; margin-top:22px; color:#7b8ba1; font-size:11.5px; }
        .login-security i { width:14px; height:14px; color:#16a34a; }
        @media (max-width:760px) { .login-bg { padding:0; background:#fff; } .login-shell { min-height:100vh; grid-template-columns:1fr; border:0; border-radius:0; box-shadow:none; } .login-context { min-height:190px; padding:25px 28px; } .context-copy { margin:24px 0 0; } .context-copy h1 { margin:9px 0 6px; font-size:25px; } .context-copy p,.context-status,.context-footer { display:none; } .login-card { justify-content:flex-start; padding:35px 28px; } .form-brand { display:none; } }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="login-shell">
        <aside class="login-context" aria-label="Field IT Support Hub">
            <div class="context-brand"><span class="context-brand-mark"><img src="<?= $uBase ?>assets/img/logo.svg" alt=""></span>Field IT Support Hub</div>
            <div class="context-copy">
                <div class="context-kicker"><i data-lucide="radio-tower"></i>Service operations</div>
                <h1>Keep field work moving.</h1>
                <p>A focused workspace for field technicians, support teams, and service managers.</p>
                <div class="context-status"><div><strong>Field tickets</strong>Track work in one place</div><div><strong>Service records</strong>Document every visit</div></div>
            </div>
            <div class="context-footer">Field IT Support Hub &middot; Secure service workspace</div>
        </aside>
        <div class="login-card">
            <div class="form-brand"><span class="brand-mark"><img src="<?= $uBase ?>assets/img/logo.svg" alt=""></span>FIELD IT SUPPORT HUB</div>
            <h1 class="login-title">Welcome back</h1>
            <p class="login-sub">Sign in to access your field service workspace.</p>

            <div class="login-head-2">Sign in to your account</div>
            <p class="login-head-p">Enter your credentials to access the platform</p>

            <div id="login-error" class="login-error"></div>

            <form id="login-form" onsubmit="handleLogin(event)">
                <div style="margin-bottom:6px;">
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;">Email Address</label>
                </div>
                <div class="input-wrap">
                    <i data-lucide="mail"></i>
                    <input type="email" name="email" required class="login-input" placeholder="you@company.com" autocomplete="email">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin:2px 0 6px;">
                    <label style="font-size:12.5px;font-weight:600;color:#374151;">Password</label>
                    <a href="#" onclick="return false;" style="font-size:11.5px;font-weight:600;color:#2563eb;text-decoration:none;">Forgot password?</a>
                </div>
                <div class="input-wrap">
                    <i data-lucide="lock"></i>
                    <input type="password" name="password" required class="login-input" id="pw-input" placeholder="Enter your password" autocomplete="current-password">
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
            <div class="login-security"><i data-lucide="shield-check"></i>Your account is protected with secure sign-in.</div>
        </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
    <script>
    var appBase = <?= json_encode($uBase) ?>;
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
            const r = await fetch(appBase + 'api/auth/login', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':document.querySelector('meta[name="csrf-token"]')?.content||''}, body:JSON.stringify({email:fd.get('email'),password:fd.get('password'),remember:fd.get('remember')==='on'}) });
            const d = await r.json();
            if (r.status === 419) { location.reload(); return; }
            if (r.ok && d.success) window.location.href = appBase + String(d.redirect || '/').replace(/^\//, '');
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
