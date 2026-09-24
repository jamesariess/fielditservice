# Deploy Field IT Support Hub

This application is a PHP + MySQL application. It uses one database: the
`database/fieldit_unified.sql` export includes both Hub and AI tables.

## What the host needs

- PHP 8.1 or newer with `pdo_mysql`, `mbstring`, `json`, `fileinfo`, and `curl`
- MySQL 8.0+ or MariaDB 10.4+
- Apache with `mod_rewrite` enabled, or a host that supports `.htaccess`
- HTTPS enabled for the production domain

## Shared hosting deployment

1. Create one MySQL database and one database user in the host control panel.
2. Import `database/fieldit_unified.sql` through phpMyAdmin. This is a full
   schema/data import, so use a fresh database or back up a populated one first.
3. Upload the entire project outside `public_html` if the host allows it.
   Point the domain document root to the project's `public/` directory.
   If the host only allows `public_html`, upload the project there because the
   root `.htaccess` routes traffic safely through `public/index.php`.
4. Copy `config/production.example.php` to `config/production.php` on the
   server. Set the live domain and the database credentials from step 1.
5. Copy `config/secrets.example.php` to `config/secrets.php` only if you want
   live AI responses, then insert your API key. Do not upload either private
   config file to GitHub.
6. Give the web-server account write access to `uploads/` if file uploads are
   enabled.
7. Open `https://your-domain/`, sign in, and test creating a ticket, updating
   a profile, and viewing the audit log.

## Notes

- Do not use the local XAMPP database account (`root` with no password) online.
- Never expose `config/production.php` or `config/secrets.php` publicly.
- The code automatically reads environment variables first. A VPS, Docker, or
  managed platform can provide `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`,
  `DB_PORT`, `APP_URL`, `APP_ENV`, and `APP_TIMEZONE` as secrets instead of
  using `config/production.php`.
