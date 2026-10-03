<?php
if (!defined('APP_ROOT')) { @header('Location: /fielditservice/'); exit; }

if (session_status() === PHP_SESSION_NONE) session_start();
// App base path (/, /fielditservice/, ...) — injected into JS below for fetch() URLs.
$uBase = app_base();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        // Apply the saved/OS theme before first paint so there is no flash.
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                if (saved === 'dark' || (!saved && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#10243d">
    <meta name="csrf-token" content="<?= Auth::generateCsrfToken() ?>">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= $uBase ?>assets/img/favicon.svg">
    <link rel="apple-touch-icon" href="<?= $uBase ?>assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --brand-500: #3b82f6;
            --brand-600: #2563eb;
            --brand-700: #1d4ed8;
            --accent: #7c3aed;
            --page-bg: #eef2f8;
            --grid-line: rgba(148, 163, 184, .22);
            --surface: #ffffff;
            --surface-alt: #f7f9fc;
            --border: #dbe3ee;
            --border-strong: #c6d2e2;
            --ink: #0f172a;
            --text: #3d4b61;
            --muted: #6b7a90;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #b91c1c;
            --danger-accent: #dc2626;
            --warn-bg: #fffbeb;
            --warn-border: #fde68a;
            --warn-text: #92400e;
            --warn-accent: #d97706;
            --info-bg: #eff6ff;
            --info-border: #bfdbfe;
            --info-text: #1e40af;
            --radius: 10px;
            --radius-lg: 18px;
            --shadow-shell: 0 30px 70px -28px rgba(15, 23, 42, .38), 0 4px 14px -8px rgba(15, 23, 42, .18);
        }
        html.dark {
            --page-bg: #070d18;
            --grid-line: rgba(148, 163, 184, .09);
            --surface: #0f1a2b;
            --surface-alt: #131f33;
            --border: #26374e;
            --border-strong: #35496a;
            --ink: #eaf0fa;
            --text: #b7c4d6;
            --muted: #8496ad;
            --danger-bg: rgba(220, 38, 38, .12);
            --danger-border: rgba(248, 113, 113, .35);
            --danger-text: #fca5a5;
            --danger-accent: #f87171;
            --warn-bg: rgba(217, 119, 6, .13);
            --warn-border: rgba(251, 191, 36, .35);
            --warn-text: #fcd34d;
            --warn-accent: #fbbf24;
            --info-bg: rgba(37, 99, 235, .14);
            --info-border: rgba(96, 165, 250, .35);
            --info-text: #93c5fd;
            --shadow-shell: 0 30px 70px -28px rgba(0, 0, 0, .7), 0 4px 14px -8px rgba(0, 0, 0, .5);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text);
            background: var(--page-bg);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
        }
        button, input { font: inherit; }
        :focus-visible { outline: 2px solid var(--brand-500); outline-offset: 2px; border-radius: 4px; }

        /* ===== Page frame ===== */
        .auth-page {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 4vw, 40px);
            padding-top: calc(clamp(16px, 4vw, 40px) + env(safe-area-inset-top, 0px));
            padding-bottom: calc(clamp(16px, 4vw, 40px) + env(safe-area-inset-bottom, 0px));
            background:
                radial-gradient(760px 520px at 82% -6%, rgba(37, 99, 235, .13), transparent 60%),
                radial-gradient(600px 420px at 4% 104%, rgba(124, 58, 237, .10), transparent 58%),
                var(--page-bg);
            overflow-x: hidden;
        }
        /* Faint blueprint grid, faded toward the edges. */
        .auth-page::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(120% 90% at 50% 20%, #000 0%, transparent 78%);
            mask-image: radial-gradient(120% 90% at 50% 20%, #000 0%, transparent 78%);
        }

        .auth-shell {
            position: relative;
            z-index: 1;
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: minmax(0, .96fr) minmax(0, 1.04fr);
            min-height: min(680px, calc(100dvh - 80px));
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: var(--surface);
            box-shadow: var(--shadow-shell);
            overflow: hidden;
        }

        /* ===== Context panel (desktop) ===== */
        .auth-aside {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 44px;
            color: #e7eefb;
            background: linear-gradient(158deg, #123058 0%, #10243d 46%, #0a1729 100%);
            overflow: hidden;
        }
        .auth-aside::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: .16;
            background-image:
                linear-gradient(rgba(255, 255, 255, .22) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .22) 1px, transparent 1px);
            background-size: 42px 42px;
        }
        .auth-aside::after {
            content: '';
            position: absolute;
            top: -30%;
            right: -24%;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(96, 165, 250, .55), transparent 66%);
            filter: blur(28px);
            pointer-events: none;
        }
        .auth-aside > * { position: relative; z-index: 1; }

        .auth-brand { display: flex; align-items: center; gap: 12px; }
        .auth-brand-mark {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--brand-600), var(--accent));
            box-shadow: 0 8px 22px -6px rgba(37, 99, 235, .7), inset 0 0 0 1px rgba(255, 255, 255, .18);
        }
        .auth-brand-mark img { width: 24px; height: 24px; display: block; }
        .auth-brand-name { display: block; font-size: 14px; font-weight: 800; color: #fff; letter-spacing: .01em; }
        .auth-brand-sub { display: block; font-size: 11px; font-weight: 500; color: #8fa6c4; letter-spacing: .02em; }

        .auth-aside-body { margin: auto 0; max-width: 360px; }
        .auth-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #7dd3fc;
        }
        .auth-kicker i, .auth-kicker svg { width: 15px; height: 15px; }
        .auth-aside h1 {
            margin: 16px 0 14px;
            font-size: 34px;
            line-height: 1.12;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }
        .auth-aside-p { color: #b9c8dc; font-size: 14px; line-height: 1.7; }

        .auth-features { list-style: none; margin-top: 30px; display: grid; gap: 14px; }
        .auth-features li { display: flex; gap: 12px; align-items: flex-start; }
        .auth-features .fi {
            flex-shrink: 0;
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            color: #bfdbfe;
            background: rgba(96, 165, 250, .16);
            border: 1px solid rgba(147, 197, 253, .25);
        }
        .auth-features .fi i, .auth-features .fi svg { width: 17px; height: 17px; }
        .auth-features strong { display: block; font-size: 13px; font-weight: 700; color: #fff; }
        .auth-features .ft-desc { display: block; font-size: 12px; color: #9fb3cc; }
        .auth-aside-foot {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: #7f95b0;
        }
        .auth-aside-foot i, .auth-aside-foot svg { width: 14px; height: 14px; color: #4ade80; }

        /* ===== Form panel ===== */
        .auth-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(28px, 4.4vw, 56px) clamp(24px, 4.6vw, 62px);
            background: var(--surface);
        }
        .auth-card-brand { display: none; align-items: center; gap: 11px; margin-bottom: 22px; }
        .auth-card-brand .auth-brand-mark { width: 38px; height: 38px; }
        .auth-card-brand .auth-brand-name { color: var(--ink); }
        .auth-title { font-size: clamp(24px, 3.4vw, 27px); font-weight: 800; color: var(--ink); letter-spacing: -.02em; line-height: 1.18; }
        .auth-sub { margin: 9px 0 28px; font-size: 13.5px; color: var(--muted); line-height: 1.6; }

        .auth-alert, .auth-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            padding: 11px 13px;
            margin-bottom: 16px;
            border: 1px solid var(--danger-border);
            border-radius: var(--radius);
            background: var(--danger-bg);
            color: var(--danger-text);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.45;
        }
        .auth-alert[hidden], .auth-note[hidden] { display: none; }
        .auth-alert i, .auth-alert svg, .auth-note i, .auth-note svg { flex-shrink: 0; width: 16px; height: 16px; margin-top: 1px; }
        .auth-alert.is-locked { background: var(--warn-bg); border-color: var(--warn-border); color: var(--warn-text); }
        .auth-alert.is-locked i, .auth-alert.is-locked svg { color: var(--warn-accent); }
        .auth-alert:not(.is-locked) i, .auth-alert:not(.is-locked) svg { color: var(--danger-accent); }
        .auth-note { background: var(--info-bg); border-color: var(--info-border); color: var(--info-text); }

        .auth-field { margin-bottom: 15px; }
        .auth-field-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
        .auth-label { font-size: 12.5px; font-weight: 600; color: var(--ink); }
        .auth-link {
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--brand-600);
            border-radius: 4px;
        }
        .auth-link:hover { text-decoration: underline; }
        html.dark .auth-link { color: #7db0f7; }

        .auth-input-wrap { position: relative; }
        .auth-input-wrap .auth-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: var(--muted);
            pointer-events: none;
            transition: color .2s;
        }
        .auth-input {
            width: 100%;
            min-height: 46px;
            padding: 11px 44px 11px 42px;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius);
            background: var(--surface-alt);
            color: var(--ink);
            font-size: 14px;
            line-height: 1.4;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .auth-input::placeholder { color: var(--muted); opacity: .85; }
        .auth-input:hover { border-color: #a9bcd3; }
        html.dark .auth-input:hover { border-color: var(--border-strong); }
        .auth-input:focus {
            background: var(--surface);
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .16);
        }
        .auth-input-wrap:focus-within .auth-input-icon { color: var(--brand-600); }
        html.dark .auth-input-wrap:focus-within .auth-input-icon { color: #7db0f7; }
        .auth-input[aria-invalid="true"] { border-color: var(--danger-accent); }
        .auth-input[aria-invalid="true"]:focus { box-shadow: 0 0 0 3px rgba(220, 38, 38, .16); }

        .auth-pw-toggle {
            position: absolute;
            right: 7px;
            top: 50%;
            transform: translateY(-50%);
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: color .2s, background .2s;
        }
        .auth-pw-toggle:hover { color: var(--brand-600); background: rgba(37, 99, 235, .09); }
        .auth-pw-toggle i, .auth-pw-toggle svg { width: 17px; height: 17px; }

        .auth-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--warn-text);
        }
        .auth-hint[hidden] { display: none; }
        .auth-hint i, .auth-hint svg { width: 14px; height: 14px; color: var(--warn-accent); }

        .auth-remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 4px 0 20px;
            cursor: pointer;
            font-size: 13px;
            color: var(--muted);
            user-select: none;
        }
        .auth-remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--brand-600);
            cursor: pointer;
            flex-shrink: 0;
        }

        .auth-submit {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 48px;
            padding: 12px 18px;
            border: 0;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 18px -8px rgba(37, 99, 235, .8);
            transition: transform .15s, box-shadow .2s, filter .2s;
        }
        .auth-submit i, .auth-submit svg { width: 17px; height: 17px; }
        .auth-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 12px 24px -10px rgba(37, 99, 235, .9); filter: saturate(1.08); }
        .auth-submit:active:not(:disabled) { transform: translateY(0); }
        .auth-submit:disabled { cursor: progress; opacity: .88; }
        .auth-submit .auth-spinner {
            display: none;
            width: 17px;
            height: 17px;
            border: 2px solid rgba(255, 255, 255, .45);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        .auth-submit.is-loading .auth-spinner { display: block; }
        .auth-submit.is-loading .auth-submit-icon { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .auth-trust {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 22px;
            font-size: 11.5px;
            color: var(--muted);
        }
        .auth-trust i, .auth-trust svg { width: 14px; height: 14px; color: #16a34a; flex-shrink: 0; }
        html.dark .auth-trust i, html.dark .auth-trust svg { color: #4ade80; }
        .auth-card-foot {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            font-size: 11px;
            color: var(--muted);
        }

        /* ===== Theme toggle ===== */
        .auth-theme-btn {
            position: fixed;
            top: calc(14px + env(safe-area-inset-top, 0px));
            right: calc(14px + env(safe-area-inset-right, 0px));
            z-index: 20;
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            box-shadow: 0 6px 16px -8px rgba(15, 23, 42, .4);
            transition: color .2s, transform .15s;
        }
        .auth-theme-btn:hover { color: var(--brand-600); transform: translateY(-1px); }
        .auth-theme-btn i, .auth-theme-btn svg { width: 18px; height: 18px; }
        .auth-theme-btn .icon-sun { display: none; }
        html.dark .auth-theme-btn .icon-moon { display: none; }
        html.dark .auth-theme-btn .icon-sun { display: block; }

        /* ===== Tablet & small laptop ===== */
        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; min-height: 0; }
            .auth-aside { padding: 26px 26px 24px; }
            .auth-aside::after { top: -60%; right: -10%; }
            .auth-aside-body { margin: 18px 0 0; max-width: none; }
            .auth-aside h1 { font-size: 23px; margin-bottom: 6px; }
            .auth-aside-p, .auth-features, .auth-aside-foot { display: none; }
            .auth-card { padding: 30px 30px 34px; }
        }

        /* ===== Phone ===== */
        @media (max-width: 560px) {
            .auth-page { padding: 0; align-items: stretch; }
            .auth-shell {
                width: 100%;
                min-height: 100dvh;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
            .auth-aside { padding: calc(20px + env(safe-area-inset-top, 0px)) 20px 20px; }
            .auth-aside-body { margin-top: 14px; }
            .auth-aside h1 { font-size: 20px; }
            .auth-card {
                flex: 1;
                padding: 26px 20px calc(30px + env(safe-area-inset-bottom, 0px));
            }
            .auth-card-brand { display: flex; }
            .auth-title { font-size: 23px; }
            .auth-sub { margin-bottom: 22px; }
            /* 16px prevents iOS Safari from zooming when a field is focused. */
            .auth-input { font-size: 16px; min-height: 52px; }
            .auth-submit { min-height: 54px; font-size: 15px; }
            .auth-pw-toggle { width: 38px; height: 38px; }
            .auth-remember { margin-bottom: 18px; }
            .auth-theme-btn {
                background: rgba(255, 255, 255, .12);
                border-color: rgba(255, 255, 255, .22);
                color: #dbe7f7;
                box-shadow: none;
            }
            html.dark .auth-theme-btn { background: rgba(15, 26, 43, .7); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; transition-duration: .001ms !important; }
        }
    </style>
</head>
<body>
    <button type="button" id="theme-toggle" class="auth-theme-btn" aria-label="Toggle dark mode" title="Toggle dark mode">
        <i data-lucide="moon" class="icon-moon"></i>
        <i data-lucide="sun" class="icon-sun"></i>
    </button>

    <div class="auth-page">
        <main class="auth-shell">
            <aside class="auth-aside" aria-label="About <?= e(APP_NAME) ?>">
                <div class="auth-brand">
                    <span class="auth-brand-mark"><img src="<?= $uBase ?>assets/img/logo.svg" alt=""></span>
                    <span>
                        <span class="auth-brand-name"><?= e(APP_NAME) ?></span>
                        <span class="auth-brand-sub">Support platform</span>
                    </span>
                </div>
                <div class="auth-aside-body">
                    <span class="auth-kicker"><i data-lucide="radio-tower"></i> Service operations</span>
                    <h1>Keep field work moving.</h1>
                    <p class="auth-aside-p">A focused workspace for field technicians, support teams, and service managers.</p>
                    <ul class="auth-features">
                        <li>
                            <span class="fi"><i data-lucide="ticket"></i></span>
                            <span><strong>Field tickets</strong><span class="ft-desc">Track every job in one place</span></span>
                        </li>
                        <li>
                            <span class="fi"><i data-lucide="clipboard-check"></i></span>
                            <span><strong>Service records</strong><span class="ft-desc">Document each visit clearly</span></span>
                        </li>
                        <li>
                            <span class="fi"><i data-lucide="book-open"></i></span>
                            <span><strong>Knowledge base</strong><span class="ft-desc">Answers at the point of need</span></span>
                        </li>
                    </ul>
                </div>
                <div class="auth-aside-foot"><i data-lucide="shield-check"></i> Secure service workspace</div>
            </aside>

            <section class="auth-card">
                <div class="auth-card-brand">
                    <span class="auth-brand-mark"><img src="<?= $uBase ?>assets/img/logo.svg" alt=""></span>
                    <span>
                        <span class="auth-brand-name"><?= e(APP_NAME) ?></span>
                        <span class="auth-brand-sub">Support platform</span>
                    </span>
                </div>

                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-sub">Sign in to access your field service workspace.</p>

                <div id="login-error" class="auth-alert" role="alert" aria-live="assertive" hidden>
                    <i data-lucide="alert-circle"></i>
                    <span id="login-error-text"></span>
                </div>
                <div id="login-note" class="auth-note" hidden>
                    <i data-lucide="info"></i>
                    <span>Password resets are handled by your administrator. Contact your IT team to regain access.</span>
                </div>

                <form id="login-form" method="post" action="<?= $uBase ?>api/auth/login">
                    <div class="auth-field">
                        <label class="auth-label" for="email">Email address</label>
                        <div class="auth-input-wrap" style="margin-top:6px;">
                            <i data-lucide="mail" class="auth-input-icon"></i>
                            <input id="email" name="email" type="email" class="auth-input" placeholder="you@company.com"
                                   autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required>
                        </div>
                    </div>

                    <div class="auth-field">
                        <div class="auth-field-top">
                            <label class="auth-label" for="password">Password</label>
                            <button type="button" class="auth-link" id="forgot-link">Forgot password?</button>
                        </div>
                        <div class="auth-input-wrap">
                            <i data-lucide="lock" class="auth-input-icon"></i>
                            <input id="password" name="password" type="password" class="auth-input" placeholder="Enter your password"
                                   autocomplete="current-password" required>
                            <button type="button" class="auth-pw-toggle" id="pw-toggle" aria-label="Show password" aria-pressed="false">
                                <i data-lucide="eye" id="pw-icon"></i>
                            </button>
                        </div>
                        <p class="auth-hint" id="caps-hint" hidden><i data-lucide="alert-triangle"></i> Caps Lock is on</p>
                    </div>

                    <label class="auth-remember" for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Keep me signed in on this device</span>
                    </label>

                    <button type="submit" id="login-btn" class="auth-submit">
                        <i data-lucide="log-in" class="auth-submit-icon"></i>
                        <span class="auth-spinner" aria-hidden="true"></span>
                        <span id="login-btn-label">Sign in</span>
                    </button>
                </form>

                <div class="auth-trust"><i data-lucide="shield-check"></i> Protected by encrypted sign-in and rate-limited access.</div>
                <div class="auth-card-foot">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &middot; v<?= e(APP_VERSION) ?></div>
            </section>
        </main>
    </div>

    <script>
    var appBase = <?= json_encode($uBase) ?>;
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var form = document.getElementById('login-form');
    var emailInput = document.getElementById('email');
    var pwInput = document.getElementById('password');
    var pwToggle = document.getElementById('pw-toggle');
    var pwIcon = document.getElementById('pw-icon');
    var capsHint = document.getElementById('caps-hint');
    var errBox = document.getElementById('login-error');
    var errText = document.getElementById('login-error-text');
    var noteBox = document.getElementById('login-note');
    var loginBtn = document.getElementById('login-btn');
    var loginBtnLabel = document.getElementById('login-btn-label');
    var busy = false;

    function clearError() {
        errBox.hidden = true;
        errBox.classList.remove('is-locked');
        emailInput.removeAttribute('aria-invalid');
        pwInput.removeAttribute('aria-invalid');
    }
    function showError(message, locked) {
        errText.textContent = message;
        errBox.hidden = false;
        errBox.classList.toggle('is-locked', !!locked);
        if (!locked) {
            emailInput.setAttribute('aria-invalid', 'true');
            pwInput.setAttribute('aria-invalid', 'true');
        }
        lucide.createIcons();
    }
    function setLoading(loading) {
        loginBtn.disabled = loading;
        loginBtn.classList.toggle('is-loading', loading);
        loginBtn.setAttribute('aria-busy', loading ? 'true' : 'false');
        loginBtnLabel.textContent = loading ? 'Signing in…' : 'Sign in';
    }
    function setCapsLock(on) {
        capsHint.hidden = !on;
        if (on) lucide.createIcons();
    }

    pwToggle.addEventListener('click', function () {
        var show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        pwToggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        pwToggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        pwIcon.setAttribute('data-lucide', show ? 'eye-off' : 'eye');
        lucide.createIcons();
        pwInput.focus();
    });

    function capsListener(e) {
        if (typeof e.getModifierState === 'function') setCapsLock(e.getModifierState('CapsLock'));
    }
    pwInput.addEventListener('keyup', capsListener);
    pwInput.addEventListener('keydown', capsListener);
    pwInput.addEventListener('blur', function () { setCapsLock(false); });

    document.getElementById('forgot-link').addEventListener('click', function () {
        noteBox.hidden = !noteBox.hidden;
        if (!noteBox.hidden) lucide.createIcons();
    });

    document.getElementById('theme-toggle').addEventListener('click', function () {
        var dark = document.documentElement.classList.toggle('dark');
        try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', dark ? '#070d18' : '#10243d');
    });

    form.addEventListener('submit', handleLogin);
    async function handleLogin(e) {
        e.preventDefault();
        if (busy) return;
        busy = true;
        clearError();
        noteBox.hidden = true;
        setLoading(true);

        var payload = {
            email: (emailInput.value || '').trim(),
            password: pwInput.value || '',
            remember: document.getElementById('remember').checked
        };

        try {
            var res = await fetch(appBase + 'api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify(payload)
            });

            // Expired/invalid CSRF token — reload to get a fresh one.
            if (res.status === 419) { location.reload(); return; }

            var data = {};
            var raw = await res.text();
            if (raw) { try { data = JSON.parse(raw); } catch (_) {} }

            if (res.ok && data.success) {
                var target = appBase + String(data.redirect || '/').replace(/^\//, '');
                window.location.href = target;
                return;
            }

            var locked = res.status === 429;
            var message = data.error || (locked
                ? 'Too many attempts. Please wait and try again.'
                : 'Invalid email or password.');
            showError(message, locked);
            if (res.status === 400 || res.status === 401) pwInput.focus();
        } catch (err) {
            showError('We could not reach the server. Check your connection and try again.', false);
        } finally {
            busy = false;
            setLoading(false);
        }
    }

    lucide.createIcons();
    // Focus the first field only on wide screens — avoids popping the
    // on-screen keyboard automatically on phones and tablets.
    if (window.matchMedia('(min-width: 901px)').matches) {
        setTimeout(function () { emailInput.focus(); }, 60);
    }
    </script>
</body>
</html>
