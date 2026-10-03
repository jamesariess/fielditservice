<?php
declare(strict_types=1);

/**
 * Field IT Support Hub — database backup, drift check and repair tool.
 *
 * The live site (InfinityFree) has no shell, so the live database is backed up
 * and restored through phpMyAdmin there — see
 * database/backups/README-BACKUP-AND-RESTORE.txt for the exact clicks.
 * This tool is the local (XAMPP) side of the same job:
 *
 *   php database/db-tool.php status              # where the app is connected, table/row summary, schema drift
 *   php database/db-tool.php backup              # write database/backups/<db>-YYYYmmdd-HHMMSS.sql (structure + data)
 *   php database/db-tool.php backup --structure  # same, without the rows (empty database, ready to import)
 *   php database/db-tool.php repair              # create tables/columns the app expects but the database is missing
 *                                                # (only ever ADDS — it never drops a table or a column)
 *
 * Every command accepts connection overrides, so a local copy can be inspected
 * without editing config/production.php:
 *   --host=localhost --port=3306 --name=fieldit_hub --user=root --pass=
 *
 * "repair" compares the live database against database/fieldit_unified.sql,
 * the known-good schema and reference data the app ships with. Uploading the
 * SQL through phpMyAdmin does the whole thing in one go; this tool is for when
 * the database is reachable from PHP but not from phpMyAdmin.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This tool runs from the command line only.');
}

define('APP_ROOT', dirname(__DIR__));

$cliOverrides = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--(host|port|name|user|pass)=(.*)$/s', $arg, $match)) {
        $cliOverrides[$match[1]] = $match[2];
    }
}

if ($cliOverrides) {
    // Aim this run at another database (a local XAMPP copy, say) without
    // touching the live credentials in config/production.php.
    date_default_timezone_set('Asia/Manila');
    define('DB_HOST', $cliOverrides['host'] ?? 'localhost');
    define('DB_PORT', (int)($cliOverrides['port'] ?? 3306));
    define('DB_NAME', $cliOverrides['name'] ?? 'fieldit_hub');
    define('DB_USER', $cliOverrides['user'] ?? 'root');
    define('DB_PASS', $cliOverrides['pass'] ?? '');
    define('DB_CHARSET', 'utf8mb4');
} else {
    require_once APP_ROOT . '/config/app.php';
}

$BASELINE   = APP_ROOT . '/database/fieldit_unified.sql';
$BACKUP_DIR = APP_ROOT . '/database/backups';

// ---------------------------------------------------------------- output ----
function dbt_line(string $line = ''): void
{
    echo $line, PHP_EOL;
}

function dbt_fail(string $message): void
{
    fwrite(STDERR, 'ERROR: ' . $message . PHP_EOL);
    exit(1);
}

// ------------------------------------------------------------- connection ----
function dbt_pdo(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        dbt_fail('cannot connect to ' . DB_USER . '@' . DB_HOST . ':' . DB_PORT . '/' . DB_NAME . ' — ' . $e->getMessage());
    }
    throw new RuntimeException('unreachable');
}

// --------------------------------------------------- expected vs. current ----
/** Tables + column definitions the app expects, read from the baseline dump. */
function dbt_expected(string $file): array
{
    if (!is_file($file)) {
        dbt_fail('baseline SQL not found: ' . $file);
    }
    $sql = (string)file_get_contents($file);
    $tables = [];
    if (preg_match_all('/^CREATE TABLE `([^`]+)` \((.*?)^\) ENGINE=([^;]*);/ms', $sql, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $block) {
            $tables[$block[1]] = [
                'body'    => rtrim($block[2]),
                'suffix'  => trim($block[3]),
                'columns' => dbt_columns($block[2]),
            ];
        }
    }
    return $tables;
}

/** Column name => definition, out of one CREATE TABLE body. Keys are skipped. */
function dbt_columns(string $body): array
{
    $columns = [];
    foreach (preg_split('/\R/', $body) ?: [] as $line) {
        if (!preg_match('/^\s*`([^`]+)`\s+(.+?),?\s*$/', $line, $match)) {
            continue;
        }
        $definition = rtrim(trim($match[2]), ',');
        if (preg_match('/^(PRIMARY KEY|UNIQUE KEY|KEY|INDEX|FULLTEXT|SPATIAL|CONSTRAINT)\b/i', $definition)) {
            continue;
        }
        $columns[$match[1]] = $definition;
    }
    return $columns;
}

function dbt_current(PDO $pdo): array
{
    $tables = [];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $name) {
        $tables[(string)$name] = [];
    }
    $rows = $pdo->query(
        'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()'
    )->fetchAll();
    foreach ($rows as $row) {
        $table = (string)$row['TABLE_NAME'];
        if (!isset($tables[$table])) {
            $tables[$table] = [];
        }
        $tables[$table][(string)$row['COLUMN_NAME']] = true;
    }
    return $tables;
}

/** @return array{missing_tables: string[], missing_columns: array<string, string[]>} */
function dbt_drift(array $expected, array $current): array
{
    $missingTables = [];
    $missingColumns = [];
    foreach ($expected as $table => $info) {
        if (!isset($current[$table])) {
            $missingTables[] = $table;
            continue;
        }
        $missing = [];
        foreach (array_keys($info['columns']) as $column) {
            if (!isset($current[$table][$column])) {
                $missing[] = $column;
            }
        }
        if ($missing) {
            $missingColumns[$table] = $missing;
        }
    }
    return ['missing_tables' => $missingTables, 'missing_columns' => $missingColumns];
}

// ----------------------------------------------------------------- backup ----
function dbt_value(PDO $pdo, $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }
    return $pdo->quote((string)$value);
}

function dbt_dump(PDO $pdo, bool $structureOnly): string
{
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $out = [];
    $out[] = '-- Field IT Support Hub — database backup';
    $out[] = '-- Database: ' . DB_NAME . '    Host: ' . DB_HOST;
    $out[] = '-- Created: ' . date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')';
    $out[] = '-- Tables: ' . count($tables) . ($structureOnly ? '   (structure only)' : '   (structure + data)');
    $out[] = '-- Import with phpMyAdmin -> your database -> Import -> choose this file (utf8mb4).';
    $out[] = '';
    $out[] = 'SET NAMES utf8mb4;';
    $out[] = 'SET FOREIGN_KEY_CHECKS = 0;';
    $out[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
    $out[] = '';

    foreach ($tables as $table) {
        $name = (string)$table;
        $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $name) . '`')->fetch();
        $definition = $create['Create Table'] ?? '';
        $out[] = '--';
        $out[] = '-- Table structure for `' . $name . '`';
        $out[] = '--';
        $out[] = 'DROP TABLE IF EXISTS `' . $name . '`;';
        $out[] = $definition . ';';
        $out[] = '';

        if ($structureOnly) {
            continue;
        }

        $rows = $pdo->query('SELECT * FROM `' . str_replace('`', '``', $name) . '`');
        $chunk = [];
        $out[] = '--';
        $out[] = '-- Data for `' . $name . '`';
        $out[] = '--';
        while (($row = $rows->fetch(PDO::FETCH_ASSOC)) !== false) {
            $values = [];
            foreach ($row as $value) {
                $values[] = dbt_value($pdo, $value);
            }
            $chunk[] = '(' . implode(',', $values) . ')';
            if (count($chunk) >= 100) {
                $out[] = 'INSERT INTO `' . $name . '` VALUES ' . implode(",\n", $chunk) . ';';
                $chunk = [];
            }
        }
        if ($chunk) {
            $out[] = 'INSERT INTO `' . $name . '` VALUES ' . implode(",\n", $chunk) . ';';
        }
        $out[] = '';
    }

    $out[] = 'SET FOREIGN_KEY_CHECKS = 1;';
    $out[] = '';
    return implode(PHP_EOL, $out);
}

function dbt_backup(PDO $pdo, bool $structureOnly): string
{
    global $BACKUP_DIR;
    if (!is_dir($BACKUP_DIR) && !@mkdir($BACKUP_DIR, 0775, true) && !is_dir($BACKUP_DIR)) {
        dbt_fail('cannot create ' . $BACKUP_DIR);
    }
    $stamp = date('Ymd-His');
    $file = $BACKUP_DIR . '/' . preg_replace('/[^A-Za-z0-9_\-]/', '_', DB_NAME) . '-' . $stamp
        . ($structureOnly ? '-structure' : '') . '.sql';
    $sql = dbt_dump($pdo, $structureOnly);
    if (@file_put_contents($file, $sql) === false) {
        dbt_fail('cannot write ' . $file);
    }
    dbt_line('Backup written: ' . $file . '  (' . number_format(strlen($sql)) . ' bytes)');
    dbt_line('Keep a copy outside the project as well — an uploaded file can be overwritten by the next upload.');
    return $file;
}

// ----------------------------------------------------------------- repair ----
function dbt_repair(PDO $pdo, array $expected, array $drift, string $baselineFile): void
{
    $sql = (string)file_get_contents($baselineFile);
    $created = [];

    foreach ($drift['missing_tables'] as $table) {
        $info = $expected[$table];
        $statement = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (' . PHP_EOL
            . trim($info['body']) . PHP_EOL . ') ENGINE=' . $info['suffix'];
        $pdo->exec($statement);
        $created[] = $table;
        dbt_line('created table   ' . $table);
    }

    // Reference rows for the tables this run created (device types, roles,
    // permissions, issue lists…). Existing tables are never touched.
    foreach ($created as $table) {
        if (!preg_match_all('/^INSERT INTO `' . preg_quote($table, '/') . '` VALUES (.*?);$/ms', $sql, $matches)) {
            continue;
        }
        $rows = 0;
        foreach ($matches[1] as $values) {
            try {
                $rows += $pdo->exec('INSERT IGNORE INTO `' . $table . '` VALUES ' . $values . ';');
            } catch (PDOException $e) {
                dbt_line('  skipped baseline rows for ' . $table . ': ' . $e->getMessage());
                break;
            }
        }
        if ($rows > 0) {
            dbt_line('  baseline rows   ' . $table . ' (' . $rows . ')');
        }
    }

    foreach ($drift['missing_columns'] as $table => $columns) {
        foreach ($columns as $column) {
            $definition = $expected[$table]['columns'][$column];
            $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
            dbt_line('added column    ' . $table . '.' . $column);
        }
    }
}

// ------------------------------------------------- prepare a repair script ----
/**
 * Writes one SQL file that can be imported on a database this machine cannot
 * reach (the live InfinityFree database): it creates any table the app expects
 * but the import target is missing, adds any missing column, and re-inserts the
 * structural reference rows. It never drops a table, a column or a row, so it is
 * safe to run twice and safe to run on the live database.
 */
function dbt_prepare(array $expected, string $baselineFile, string $outFile): void
{
    global $BACKUP_DIR;
    if (!is_dir($BACKUP_DIR) && !@mkdir($BACKUP_DIR, 0775, true) && !is_dir($BACKUP_DIR)) {
        dbt_fail('cannot create ' . $BACKUP_DIR);
    }
    $baseline = (string)file_get_contents($baselineFile);
    $referenceTables = [
        'roles', 'permissions', 'role_permissions', 'user_permissions', 'departments',
        'locations', 'device_types', 'manufacturers', 'troubleshooting_categories',
        'troubleshooting_issues', 'system_settings', 'ai_personality',
    ];

    $sql = [];
    $sql[] = '-- Field IT Support Hub — database repair script';
    $sql[] = '-- Created: ' . date('Y-m-d H:i:s') . '  from ' . basename($baselineFile);
    $sql[] = '--';
    $sql[] = '-- Import this in phpMyAdmin -> your database -> Import (format: SQL).';
    $sql[] = '-- It only ever ADDS: missing tables, missing columns and the reference';
    $sql[] = '-- rows the app needs (roles, permissions, device types, issue lists).';
    $sql[] = '-- It never drops a table, a column or a row, so it cannot delete tickets.';
    $sql[] = '';
    $sql[] = 'SET NAMES utf8mb4;';
    $sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
    $sql[] = '';

    foreach ($expected as $table => $info) {
        $sql[] = '-- table: ' . $table;
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (';
        $sql[] = trim($info['body']);
        $sql[] = ') ENGINE=' . $info['suffix'] . ';';
        foreach ($info['columns'] as $column => $definition) {
            $sql[] = 'ALTER TABLE `' . $table . '` ADD COLUMN IF NOT EXISTS `' . $column . '` ' . $definition . ';';
        }
        $sql[] = '';
    }

    $sql[] = '-- reference rows (existing rows are left untouched)';
    foreach ($referenceTables as $table) {
        if (!preg_match_all('/^INSERT INTO `' . preg_quote($table, '/') . '` VALUES (.*?);$/ms', $baseline, $matches)) {
            continue;
        }
        foreach ($matches[1] as $values) {
            $sql[] = 'INSERT IGNORE INTO `' . $table . '` VALUES ' . $values . ';';
        }
    }
    $sql[] = '';
    $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
    $sql[] = '';

    if (@file_put_contents($outFile, implode(PHP_EOL, $sql)) === false) {
        dbt_fail('cannot write ' . $outFile);
    }
    dbt_line('Repair script written: ' . $outFile);
    dbt_line('Upload it with the app, then import it in phpMyAdmin on the host.');
}

// ----------------------------------------------------------------- status ----
function dbt_status(PDO $pdo, array $expected): void
{
    $version = (string)$pdo->query('SELECT VERSION()')->fetchColumn();
    dbt_line('Database   : ' . DB_NAME . '  @  ' . DB_HOST . ':' . DB_PORT . '  (user ' . DB_USER . ')');
    dbt_line('Server     : ' . $version . '  ·  charset ' . DB_CHARSET);
    dbt_line('Baseline   : ' . count($expected) . ' tables expected by the app');
    dbt_line();

    $current = dbt_current($pdo);
    $important = [
        'users', 'roles', 'permissions', 'troubleshooting_sessions', 'ticket_notes', 'equipment',
        'device_types', 'manufacturers', 'device_models', 'troubleshooting_issues', 'troubleshooting_steps',
        'knowledge_articles', 'ticket_suggestions', 'ticket_field_memory', 'activity_logs',
    ];
    dbt_line('Row counts');
    foreach (array_keys($current) as $table) {
        if (!in_array($table, $important, true)) {
            continue;
        }
        $count = (int)$pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        dbt_line(sprintf('  %-28s %s', $table, number_format($count)));
    }
    $total = 0;
    foreach (array_keys($current) as $table) {
        $total += (int)$pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    }
    dbt_line(sprintf('  %-28s %s', 'ALL ' . count($current) . ' tables', number_format($total)));
    dbt_line();

    dbt_line('Schema check');
    $drift = dbt_drift($expected, $current);
    $extra = array_diff(array_keys($current), array_keys($expected));
    if (!$drift['missing_tables'] && !$drift['missing_columns']) {
        dbt_line('  OK — every table and column the app expects is present.');
    }
    foreach ($drift['missing_tables'] as $table) {
        dbt_line('  MISSING TABLE   ' . $table);
    }
    foreach ($drift['missing_columns'] as $table => $columns) {
        dbt_line('  MISSING COLUMNS ' . $table . ': ' . implode(', ', $columns));
    }
    foreach ($extra as $table) {
        if ($table === '') {
            continue;
        }
        dbt_line('  extra table     ' . $table . ' (not used by this app version — harmless)');
    }
    if ($drift['missing_tables'] || $drift['missing_columns']) {
        dbt_line();
        dbt_line('Run  php database/db-tool.php repair  to create what is missing (nothing is dropped).');
    }
}

// ------------------------------------------------------------------- main ----
$command = $argv[1] ?? 'status';
$flags = array_slice($argv, 2);

if (!in_array($command, ['status', 'backup', 'repair', 'prepare'], true)) {
    dbt_line('Usage: php database/db-tool.php [status|backup|repair|prepare] [--structure]');
    dbt_line('       [--host= --port= --name= --user= --pass=]  (defaults to config/production.php)');
    exit($command === '--help' || $command === '-h' ? 0 : 1);
}

if ($command === 'prepare') {
    // No connection needed: this writes a file for the database you cannot
    // reach from here (the live host).
    dbt_prepare(dbt_expected($BASELINE), $BASELINE, $BACKUP_DIR . '/sync-live-database.sql');
    exit(0);
}

$pdo = dbt_pdo();
$expected = dbt_expected($BASELINE);

if ($command === 'backup') {
    dbt_backup($pdo, in_array('--structure', $flags, true));
    exit(0);
}

if ($command === 'repair') {
    $drift = dbt_drift($expected, dbt_current($pdo));
    if (!$drift['missing_tables'] && !$drift['missing_columns']) {
        dbt_line('Nothing to repair — the database already matches the app schema.');
        exit(0);
    }
    dbt_repair($pdo, $expected, $drift, $BASELINE);
    dbt_line();
    dbt_line('Repair finished — re-checking…');
    dbt_line();
}

dbt_status($pdo, $expected);
