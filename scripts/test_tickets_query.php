<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/demo.php';
require __DIR__ . '/../includes/Auth.php';
require __DIR__ . '/../includes/Database.php';

// Simulate a logged-in session
$_SESSION['user_id'] = 2;
$_SESSION['user_email'] = 'fieldit@fieldit.local';
$_SESSION['user_name'] = 'Juan Dela Cruz';
$_SESSION['csrf_token'] = 'test';

Auth::start();
echo "userId=" . Auth::userId() . "\n";
echo "userName=" . Auth::userName() . "\n";

$tickets = Database::fetchAll(
    "SELECT id, ticket_number, status, model, serial_number, started_at, ended_at, time_spent_minutes, address, customer_name FROM troubleshooting_sessions WHERE user_id = ? ORDER BY started_at DESC",
    [Auth::userId()]
);
echo "Rows: " . count($tickets) . "\n";
foreach ($tickets as $t) {
    echo $t["ticket_number"] . " | " . $t["status"] . " | " . ($t["model"] ?? "NULL") . " | " . ($t["customer_name"] ?? "NULL") . "\n";
}
