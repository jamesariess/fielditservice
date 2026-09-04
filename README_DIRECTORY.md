# Field IT Support Hub — Project Structure

Organized so it's easy to understand what lives where, and what's secured.

```
fielditservice/
│
├── .htaccess                    ← Routes ALL requests to public/index.php (clean URLs)
├── public/                      ← Web root — the only directory with web-served files
│   ├── index.php                ← Single entry point / router (every request lands here)
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/app.css          ← Global design system (design tokens, components)
│   │   ├── js/app.js            ← Global JS (animations, helpers, API wrappers, CSRF injection)
│   │   └── img/                 ← Brand logo, favicon, empty-state illustrations
│   └── pages/
│       ├── auth/login.php       ← Guest-only (login)
│       ├── errors/403.php       ← Access denied (standalone, branded)
│       ├── errors/404.php       ← Not found (standalone, branded)
│       ├── admin/               ← ⚠ ADMIN-ONLY — every page guarded by:
│       │   │                        includes/admin_guard.php  AND  router permission map
│       │   ├── users.php        ← users.manage
│       │   ├── roles.php        ← roles.manage
│       │   ├── departments.php  ← departments.manage
│       │   ├── knowledge.php    ← knowledge.manage
│       │   ├── equipment.php    ← equipment.manage
│       │   ├── ai.php           ← ai.manage
│       │   ├── audit.php        ← audit.view
│       │   ├── statistics.php   ← audit.view
│       │   ├── settings.php     ← system.settings
│       │   └── troubleshoot.php ← system.settings
│       └── *.php                ← Authenticated app pages (dashboard, troubleshoot…)
│
├── api/                         ← JSON API endpoints (/api/* routed here)
│   ├── auth/        users/       tickets/      knowledge/
│   ├── equipment/   departments/ notifications/ favorites/
│   ├── search/      settings/    profile/      chat/
│   └── troubleshooting/ …       (also referenced by /api/troubleshooting/…)
│
├── includes/                    ← Shared PHP (no direct web access)
│   ├── layout_header.php        ← Opens <html> → sidebar, header, <div class="page-content">
│   ├── layout_footer.php        ← Closes layout + bottom nav + global modals
│   ├── layout.php               ← Legacy layout helper
│   ├── Admin guard:
│   ├── admin_guard.php          ← ⚠ Central admin permission check (defense-in-depth)
│   ├── Auth.php                 ← Login / sessions / permissions / timeouts / fingerprint
│   ├── Security.php             ← 🔒 Session hardening, CSRF, security headers, throttling
│   ├── Database.php             ← PDO wrapper
│   ├── DemoData.php             ← Demo mode users/data
│   └── helpers.php              ← e(), app_base(), json_response(), badges, time_ago()
│
├── config/                      ← Configuration
│   ├── app.php                  ← App, DB, security, AI settings
│   └── demo.php                 ← DEMO_MODE flag
│
├── src/                         ← Future domain code (Models / Services / Middleware)
│   ├── Middleware/              ← (add request middleware here)
│   ├── Models/                  ← (add ORM models here)
│   └── Services/                ← (add business services here)
│
├── database/                    ← SQL schema + seed scripts
├── scripts/                     ← Maintenance / setup scripts
└── uploads/                     ← User-uploaded files (keep outside web root)
```

## Security model

| Layer | Enforcement |
|---|---|
| Clean URLs | Root `.htaccess` routes every request through `public/index.php` — internal folders (`config/`, `includes/`, `database/`) are never directly servable. |
| Router | `public/index.php` normalizes the app base path, maps every `/admin/*` URL to a **permission key**, and rejects unauthorized users *before any admin markup renders*. |
| Page guard | Each admin page requires `includes/admin_guard.php` which re-checks the permission (defense in depth). |
| Session | `HttpOnly` + `SameSite=Strict` + hardened cookie params, **session ID rotated on login**, client-fingerprint binding (hijack detection), **server-side idle timeout (30 min)** + **absolute lifetime (8 h)** — both authoritative. |
| CSRF | Token in `<meta>` is auto-attached to every state-changing `fetch` (global JS interceptor); the router **rejects any authenticated state-changing `/api/*` call without a valid token (HTTP 419)**. |
| Login | Brute-force throttle: max `MAX_LOGIN_ATTEMPTS` failures per email+IP in `LOCKOUT_DURATION` (HTTP 429). |
| Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `X-XSS-Protection` on every response. |
| API | `/api/*` endpoints return `401`/`403`/`419` JSON when unauthenticated, unauthorized, or cross-site. |

### Session timeout tuning (`config/app.php`)

```php
define('SESSION_LIFETIME', 8 * 3600);    // absolute max session life: 8 hours
define('SESSION_IDLE_TIMEOUT', 30 * 60); // idle timeout: 30 min without activity
define('SESSION_WARNING_SECONDS', 5 * 60); // UI warns 5 min before expiry
```

The UI warns the user with an "Extend Session" dialog (extends only the idle window — the absolute cap stays fixed). The browser heartbeat only fires while the user is actively moving/typing, so walking away truly expires the session.

## Design system

The whole app shares one CSS file (`public/assets/css/app.css`) with:

- Design tokens (colors, radii, shadows) + dark mode via `.dark`
- `page-hero` — consistent page headers
- `panel-card` — consistent card shells
- `fx-reveal` — scroll-reveal animation (stagger via `--fx-delay`)
- `skeleton-*` — loading skeletons for JS-loaded panels
- `btn-spinner` / `setButtonLoading()` — loading buttons