<!DOCTYPE html>
<?php
$uBase = '/';
if (preg_match('#^(.*/public)#', $_SERVER['REQUEST_URI'] ?? '', $um)) { $uBase = $um[1] . '/'; }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found · Field IT Support Hub</title>
    <link rel="icon" type="image/svg+xml" href="<?= $uBase ?>assets/img/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: linear-gradient(135deg, #0f172a, #1e293b 50%, #172554); overflow: hidden; position: relative; }
        .orb { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .45; }
        .orb-1 { width: 420px; height: 420px; top: -120px; right: -100px; background: radial-gradient(circle, rgba(37,99,235,.5), transparent 70%); }
        .orb-2 { width: 380px; height: 380px; bottom: -140px; left: -100px; background: radial-gradient(circle, rgba(139,92,246,.4), transparent 70%); }
        .card { position: relative; z-index: 1; background: rgba(255,255,255,.98); border-radius: 24px; padding: 44px 40px 40px; text-align: center; max-width: 460px; width: 100%; box-shadow: 0 30px 60px -15px rgba(0,0,0,.5); animation: in .6s cubic-bezier(.16,1,.3,1) both; }
        @keyframes in { from { opacity: 0; transform: translateY(24px) scale(.96); } to { opacity: 1; transform: none; } }
        .num { font-size: 74px; font-weight: 800; line-height: 1; letter-spacing: -0.05em; background: linear-gradient(135deg, #2563eb, #7c3aed); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; animation: pop .6s .12s cubic-bezier(.34,1.56,.64,1) both; }
        @keyframes pop { from { opacity: 0; transform: scale(.4) rotate(-8deg); } to { opacity: 1; transform: none; } }
        .code { font-size: 13px; font-weight: 800; letter-spacing: .12em; color: #2563eb; text-transform: uppercase; margin-top: 10px; }
        h1 { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin: 6px 0 8px; }
        p { font-size: 13.5px; color: #64748b; line-height: 1.65; margin-bottom: 26px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; box-shadow: 0 6px 18px rgba(37,99,235,.3); transition: all .25s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(37,99,235,.4); }
        .btn i { width: 16px; height: 16px; }
        .brand { display: flex; align-items: center; justify-content: center; gap: 9px; margin-bottom: 22px; }
        .brand img { width: 30px; height: 30px; }
        .brand span { font-size: 13px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="card">
        <div class="brand"><img src="<?= $uBase ?>assets/img/logo.svg" alt="Field IT Hub"><span>Field IT Support Hub</span></div>
        <div class="num">404</div>
        <div class="code">Page Not Found</div>
        <h1>This page went walkabout</h1>
        <p>The page you're looking for doesn't exist or has been moved. Use the search or head back to your dashboard.</p>
        <a href="<?= $uBase ?>" class="btn"><i data-lucide="home"></i> Return to Dashboard</a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
