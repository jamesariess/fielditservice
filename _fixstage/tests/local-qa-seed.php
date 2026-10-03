<?php
/**
 * LOCAL VERIFICATION ONLY - not part of the app.
 *
 * Creates/refreshes a throwaway admin account in the local dev database so the
 * pages can be opened in a browser and checked at phone widths. The live site
 * is never touched by this script.
 *
 * Run:
 *   DB_HOST=localhost DB_NAME=fieldit_hub DB_USER=fieldit_dev DB_PASS=fieldit_dev \
 *     php tests/local-qa-seed.php
 */

$root = dirname(__DIR__);
require_once $root . '/config/app.php';
require_once $root . '/includes/Database.php';

$email = 'qa.mobile@fieldit.local';
$password = 'MobileQA#2026';

$hash = password_hash($password, PASSWORD_DEFAULT);

$existing = Database::fetch('SELECT id FROM users WHERE email = ?', [$email]);
if ($existing) {
    Database::update('users', [
        'password_hash' => $hash,
        'full_name' => 'Mobile QA Admin',
        'role_id' => 1,
        'status' => 'active',
        'deleted_at' => null,
    ], 'id = ?', [$existing['id']]);
    $id = (int)$existing['id'];
    echo "Updated QA admin #$id\n";
} else {
    $id = Database::insert('users', [
        'email' => $email,
        'password_hash' => $hash,
        'full_name' => 'Mobile QA Admin',
        'role_id' => 1,
        'status' => 'active',
    ]);
    echo "Created QA admin #$id\n";
}

$perms = Database::fetch('SELECT COUNT(*) AS n FROM role_permissions WHERE role_id = 1');
echo 'Super Admin permission rows: ' . ($perms['n'] ?? 0) . "\n";

foreach ([
    'tickets_page_table' => 'troubleshooting_sessions',
    'sessions' => 'troubleshooting_sessions',
] as $label => $table) {
    $row = Database::fetch("SELECT COUNT(*) AS n FROM $table");
    echo sprintf("%-20s %s rows\n", $label, $row['n'] ?? '?');
}

echo "Login: $email / $password\n";
