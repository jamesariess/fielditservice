<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';

// Seed manufacturers with proper brand logos if they don't exist
$brands = [
    ['name' => 'Dell', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/48/Dell_Logo.svg/200px-Dell_Logo.svg.png', 'device_types' => 'desktop,laptop,server,monitor', 'description' => 'Leading manufacturer of desktops, laptops, servers, and monitors'],
    ['name' => 'HP', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/HP_logo_2012.svg/200px-HP_logo_2012.svg.png', 'device_types' => 'laptop,printer,desktop', 'description' => 'Hewlett-Packard - Printers, laptops, and desktops'],
    ['name' => 'Lenovo', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/Lenovo_logo_%282015%29.svg/200px-Lenovo_logo_%282015%29.svg.png', 'device_types' => 'laptop,desktop', 'description' => 'ThinkPads, IdeaPads, and enterprise solutions'],
    ['name' => 'Hikvision', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Hikvision_logo.svg/200px-Hikvision_logo.svg.png', 'device_types' => 'cctv', 'description' => 'CCTV cameras and surveillance systems'],
    ['name' => 'Brother', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Brother_logo.svg/200px-Brother_logo.svg.png', 'device_types' => 'printer', 'description' => 'Printers and scanning solutions'],
    ['name' => 'Cisco', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Cisco_logo_blue_2016.svg/200px-Cisco_logo_blue_2016.svg.png', 'device_types' => 'switch,router', 'description' => 'Enterprise networking equipment'],
    ['name' => 'Dahua', 'image_url' => 'https://www.dahuasecurity.com/images/logo.png', 'device_types' => 'cctv', 'description' => 'Video surveillance and CCTV solutions'],
    ['name' => 'HPE', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Hewlett_Packard_Enterprise_logo.svg/200px-Hewlett_Packard_Enterprise_logo.svg.png', 'device_types' => 'server', 'description' => 'Enterprise servers and infrastructure'],
    ['name' => 'TP-Link', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/TP-Link_Logo.svg/200px-TP-Link_Logo.svg.png', 'device_types' => 'switch,router', 'description' => 'Networking routers, switches, and access points'],
    ['name' => 'Ubiquiti', 'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/Ubiquiti_Logo.svg/200px-Ubiquiti_Logo.svg.png', 'device_types' => 'access point', 'description' => 'Enterprise WiFi and networking'],
];

foreach ($brands as $b) {
    // Check if brand already exists in manufacturers table
    $existing = Database::fetch("SELECT id FROM manufacturers WHERE name = ?", [$b['name']]);
    if ($existing) {
        Database::update('manufacturers', ['image_url' => $b['image_url']], 'name = ?', [$b['name']]);
        echo "Updated: {$b['name']}\n";
    } else {
        Database::insert('manufacturers', $b);
        echo "Inserted: {$b['name']}\n";
    }
}

echo "Done!\n";
