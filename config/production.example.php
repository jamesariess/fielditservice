<?php
/**
 * Production configuration template.
 * Copy this file to config/production.php on the server and replace every
 * value with the credentials created in your host's control panel.
 */
return [
    'APP_ENV' => 'production',
    'APP_URL' => 'https://support.example.com',
    'APP_TIMEZONE' => 'Asia/Manila',

    // One MySQL database contains both the Field IT Hub and AI data.
    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_NAME' => 'hostingaccount_fieldit',
    'DB_USER' => 'hostingaccount_fieldit',
    'DB_PASS' => 'replace-with-a-strong-database-password',
    'DB_CHARSET' => 'utf8mb4',
];
