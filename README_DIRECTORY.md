# Field IT Support Hub — Project Structure

Organized so it's easy to understand what lives where, and what's secured.

```
fielditservice/
│
├── public/                      ← Web root (only folder Apache should serve)
│   ├── index.php                ← Single entry point / router (all requests)
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/app.css          ← Global design system (design tokens, components)
│   │   ├── js/app.js            ← Global JS (animations, helpers, API wrappers)
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
│   ├── Auth.php                 ← Login / sessions / permissions
│   ├── Database.php             ← PDO wrapper
│   ├── DemoData.php             ← Demo mode users/data
│   └── helpers.php              ← e(), json_response(), badges, time_ago()
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
| Router | `public/index.php` maps every `/admin/*` URL to a **permission key** and rejects unauthorized users *before any admin markup renders*. |
| Page guard | Each admin page requires `includes/admin_guard.php` which re-checks the permission (defense in depth). |
| Session | `HttpOnly` + `SameSite=Strict` cookies, CSRF token in `<meta>` and API `X-CSRF-Token` header. |
| API | `/api/*` endpoints return `401`/`403` JSON when unauthenticated/unauthorized. |

## Design system

The whole app shares one CSS file (`public/assets/css/app.css`) with:

- Design tokens (colors, radii, shadows) + dark mode via `.dark`
- `page-hero` — consistent page headers
- `panel-card` — consistent card shells
- `fx-reveal` — scroll-reveal animation (stagger via `--fx-delay`)
- `skeleton-*` — loading skeletons for JS-loaded panels
- `btn-spinner` / `setButtonLoading()` — loading buttons