FIELD IT SUPPORT HUB — DATABASE BACKUP & RE-SETUP (InfinityFree)
================================================================
Created: 2026-10-01

YOUR LIVE DATABASE
    host      sql311.infinityfree.com
    database  if0_42963990_fieldit_hub
    user      if0_42963990
    password  InfinityFree control panel -> MySQL Databases (it is NOT in this
              repo; config/production.php holds it and is deliberately
              gitignored)

FILES IN THIS FOLDER
    sync-live-database.sql     Repair script. Import it on the host to create
                               anything the app expects but that database is
                               missing (tables, columns) and to reload the
                               reference rows (roles, permissions, device
                               types, issue lists). It only ever ADDS — it
                               cannot delete a ticket, a user or a column.
                               Verified: fresh install, re-import (idempotent)
                               and repair of a damaged table all pass.
    <db>-YYYYmmdd-HHMMSS.sql   A backup produced by the tool below. These hold
                               user rows and password hashes, which is why
                               *.sql is gitignored in this folder.
    This file                  The steps.

------------------------------------------------------------------
STEP 1 — BACK UP THE LIVE DATABASE  (always do this first)
------------------------------------------------------------------
A .sql export is the only real backup: it holds every ticket, user, equipment
record and knowledge article exactly as they are right now.

    1. InfinityFree dashboard -> MySQL Databases -> click phpMyAdmin next to
       if0_42963990_fieldit_hub.
    2. Select the database on the left.
    3. Top menu: Export  ->  Export method: "Quick"  ->  Format: SQL
       Leave "Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT" ticked.
    4. Press Export. Your browser downloads
       if0_42963990_fieldit_hub.sql (roughly 300 KB, the same size as
       database/fieldit_unified.sql in this repo).
    5. Save it in two places (PC + USB/Drive) and date the name, e.g.
       fieldit_hub-live-2026-10-01.sql.
    6. Keep it OUTSIDE htdocs. Never leave a database dump in the web folder.

If phpMyAdmin refuses the export because the file is too big, export table by
table (tick a table, Export) instead of selecting all of them at once.

------------------------------------------------------------------
STEP 2 — CHECK WHAT THE DATABASE IS MISSING
------------------------------------------------------------------
Locally, in git bash at the project root:

    php database/db-tool.php status

That prints the host/database in use, row counts for the important tables, and
any table or column the app expects but the database does not have. Against the
live database you get the same answer by comparing the table list in phpMyAdmin
with the 57 tables the app ships with.

    status    connect, summary, schema drift
    backup    write database/backups/<db>-<date>.sql (structure + data)
              add --structure for an empty-but-correct database
    repair    create missing tables/columns on the database you are connected to
    prepare   regenerate sync-live-database.sql from the current baseline

Connection overrides let you point it at another database without ever editing
config/production.php:

    php database/db-tool.php status --host=localhost --name=fieldit_hub --user=root --pass=

------------------------------------------------------------------
STEP 3 — RE-SET / REPAIR THE DATABASE ON INFINITYFREE
------------------------------------------------------------------
A) REPAIR, keeping all live data (the normal case — missing table, or
   "Unknown column" errors in the app):

    1. phpMyAdmin -> your database -> Import.
    2. Choose file: sync-live-database.sql  (from this folder; upload it with
       the app via FTP/file manager first, or import the copy on your PC).
    3. Format: SQL, Character set: utf-8, then Import.
    4. Open the app and check Tickets / Equipment / Knowledge load.

   It is safe to import twice: every statement is IF NOT EXISTS / INSERT IGNORE.

B) CLEAN INSTALL (empty database, or the schema is a mess and you accept
   starting from the reference data):

    1. Do STEP 1 first — you cannot get the tickets back otherwise.
    2. phpMyAdmin -> your database -> Import -> choose
       database/fieldit_unified.sql  (57 tables, schema + reference data +
       the demo accounts as of 2026-09-20) -> Import.
    3. Import your own export from STEP 1 only if you want the older rows back;
       it replaces what (2) just created.
    4. Change every password that came from the demo data, and check
       config/production.php still points at this database.

C) RESTORE A BACKUP (something went wrong):

    1. phpMyAdmin -> your database -> Import -> choose your dated .sql export.
    2. It drops and recreates every table, so the result is byte-for-byte the
       moment you exported. Do not import it into the wrong database.

------------------------------------------------------------------
STEP 4 — AFTER ANY IMPORT
------------------------------------------------------------------
    * Open the app: login page loads, dashboard shows tickets, the Work Order
      scanner opens, an equipment page loads.
    * phpMyAdmin -> your database -> tick all tables -> "Check tables" (or look
      for red rows on the Structure tab).
    * If the site shows "Database connection failed", the DB_* values in
      config/production.php do not match the MySQL Databases page of the
      InfinityFree control panel (host, database name, user, password).

------------------------------------------------------------------
WHY THE APP NEEDS THIS FILE AT ALL
------------------------------------------------------------------
InfinityFree gives no shell access, so the live database can only be backed up
and restored through phpMyAdmin. The tool behind this folder exists so the same
checks can be run locally: it reads config/production.php for the live
credentials but never writes to a database unless you ask it to, and the only
file it generates for the host is the additive repair script above.
