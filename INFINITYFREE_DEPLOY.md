# InfinityFree Deployment

Your InfinityFree MySQL connection is already created:

| Setting | Value |
| --- | --- |
| Host | `sql311.infinityfree.com` |
| Port | `3306` |
| Database | `if0_42963990_fieldit_hub` |
| Username | `if0_42963990` |

## 1. Import the database

In InfinityFree, open **MySQL Databases** and use the **phpMyAdmin** button for
`if0_42963990_fieldit_hub`. Select **Import**, choose
`database/fieldit_unified.sql`, and run the import. The SQL file contains both
the Field IT Hub and AI tables in this one database.

## 2. Upload the application

Open **File Manager** then open `htdocs`. Upload the project files directly
into `htdocs`, including the root `index.php`, root `.htaccess`, `public`,
`api`, `config`, `includes`, `database`, and `uploads` folders. Delete any
custom `index.html` redirect you created before uploading. Do not upload `.git`,
local XAMPP logs, or `config/secrets.php`.

The root `index.php` starts the application and the root `.htaccess` sends
clean URLs to `public/index.php`, so do not put only the `public` folder in
`htdocs`.

## 3. Add the production database settings

1. In `config/`, rename `production.infinityfree.example.php` to
   `production.php`.
2. Replace `APP_URL` with your real InfinityFree URL or custom domain.
3. Click the eye icon in the InfinityFree MySQL page to reveal the password and
   paste it as `DB_PASS`. Keep this password private.

## 4. Test

Visit the live URL. Test sign-in, create a ticket, update the profile, and open
the audit log. If a `500` error appears, verify the database password and that
the root `.htaccess` was uploaded.
