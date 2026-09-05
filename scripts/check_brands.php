<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';

$rows = Database::fetchAll('SELECT id, name, image_url, device_types FROM fieldit_hub.manufacturers ORDER BY name');
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['name'] . ' | ' . ($r['image_url'] ?: 'NO_IMG') . ' | ' . ($r['device_types'] ?: 'N/A') . PHP_EOL;
}

echo "\n--- Equipment count per brand ---\n";
$eq = Database::fetchAll("SELECT LOWER(manufacturer) as m, COUNT(*) as c FROM equipment WHERE deleted_at IS NULL GROUP BY LOWER(manufacturer) ORDER BY c DESC");
foreach ($eq as $r) {
    echo $r['m'] . ': ' . $r['c'] . PHP_EOL;
}
