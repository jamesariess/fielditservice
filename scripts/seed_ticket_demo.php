<?php
/**
 * Seed demo ticket data into troubleshooting_sessions.
 * Run: php scripts/seed_ticket_demo.php
 * Safe to re-run (upserts by ticket_number where possible, then inserts).
 */
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/demo.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/Database.php';
require __DIR__ . '/../includes/Auth.php';

// Use a dedicated seed user if possible (user id 2 = Juan Dela Cruz), else 1.
$seedUserId = 2;
try {
    $u = Database::fetch("SELECT id FROM users WHERE id = ?", [$seedUserId]);
    if (!$u) { $seedUserId = 1; }
} catch (Exception $e) { $seedUserId = 1; }

$now = date('Y-m-d H:i:s');
$yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
$twoHours = date('Y-m-d H:i:s', strtotime('-2 hours'));
$threeDaysAgo = date('Y-m-d H:i:s', strtotime('-3 days'));
$weekAgo = date('Y-m-d H:i:s', strtotime('-1 week'));

$rows = [
    [
        'ticket_number' => 'TK-1001',
        'user_id' => $seedUserId,
        'customer_name' => 'Rica Pagulayan',
        'department' => 'Operations',
        'location' => 'Floor 3, Desk 42',
        'device_type' => 'Display',
        'manufacturer' => 'Lenovo',
        'model' => 'ThinkPad T14 Gen 3',
        'serial_number' => 'PW07MWVE',
        'problem_description' => 'Camera and microphone not working',
        'priority' => 'high',
        'status' => 'solved',
        'started_at' => '2026-09-18 16:30:00',
        'ended_at' => '2026-09-18 17:10:00',
        'resolution' => 'Replaced camera and mic. Laptop camera and mic now working. Run LDT all passed. Test camera and mic working good. Boot to Windows.',
        'resolution_type' => 'completed',
        'parts_replaced' => 'Camera, Microphone',
        'tools_used' => 'Precision screwdriver, ESD strap',
        'steps_performed' => 'Upon checking camera and mic is not working. Update drivers and Lenovo Vantage still same issue. Replaced camera and mic.',
        'time_spent_minutes' => 40,
        'address' => '1 Aviation Ground Handling Services Corporation, Pasay City',
        'latitude' => '14.5547',
        'longitude' => '120.9867',
    ],
    [
        'ticket_number' => 'TK-1002',
        'user_id' => $seedUserId,
        'customer_name' => 'Glenn',
        'department' => 'Field IT',
        'location' => 'IBM Eastwood',
        'device_type' => 'Hardware',
        'manufacturer' => 'Lenovo',
        'model' => 'ThinkPad X1 Carbon Gen 9',
        'serial_number' => 'PF4BCHJT',
        'problem_description' => 'Battery life at 64%, needs replacement',
        'priority' => 'medium',
        'status' => 'in_progress',
        'started_at' => $twoHours,
        'ended_at' => null,
        'resolution' => null,
        'resolution_type' => null,
        'parts_replaced' => null,
        'tools_used' => null,
        'steps_performed' => null,
        'time_spent_minutes' => null,
        'address' => null,
        'latitude' => null,
        'longitude' => null,
    ],
    [
        'ticket_number' => 'TK-1003',
        'user_id' => $seedUserId,
        'customer_name' => 'Reception Desk',
        'department' => 'Reception',
        'location' => 'Ground Floor, Reception',
        'device_type' => 'Printer',
        'manufacturer' => 'HP',
        'model' => 'LaserJet Pro M404',
        'serial_number' => 'HPLCJ404X',
        'problem_description' => 'Printer offline, not responding to print jobs',
        'priority' => 'high',
        'status' => 'escalated',
        'started_at' => $threeDaysAgo,
        'ended_at' => null,
        'resolution' => null,
        'resolution_type' => null,
        'parts_replaced' => null,
        'tools_used' => null,
        'steps_performed' => null,
        'time_spent_minutes' => null,
        'address' => null,
        'latitude' => null,
        'longitude' => null,
    ],
    [
        'ticket_number' => 'TK-1004',
        'user_id' => $seedUserId,
        'customer_name' => 'Maria Santos',
        'department' => 'Finance',
        'location' => 'Floor 2, Room 204',
        'device_type' => 'Network',
        'manufacturer' => 'Dell',
        'model' => 'OptiPlex 7090',
        'serial_number' => 'DL-7090-FIN-204',
        'problem_description' => 'Intermittent WiFi drops every few minutes',
        'priority' => 'medium',
        'status' => 'solved',
        'started_at' => $weekAgo,
        'ended_at' => $weekAgo,
        'resolution' => 'Replaced WiFi antenna cable. Updated Intel WiFi driver to latest. Ran iPerf for 10 minutes with zero packet loss. Verified stable connection.',
        'resolution_type' => 'completed',
        'parts_replaced' => 'WiFi antenna cable',
        'tools_used' => 'Phillips PH0 screwdriver, anti-static mat',
        'steps_performed' => 'Checked Device Manager — WiFi adapter showing error code 43. Updated driver. Still dropping. Opened case, reseated antenna cable, replaced with spare. Verified signal strength -65 dBm on iwconfig equivalent.',
        'time_spent_minutes' => 35,
        'address' => 'Finance Office, 2nd Floor, East Wing',
        'latitude' => '14.5551',
        'longitude' => '120.9871',
    ],
    [
        'ticket_number' => 'TK-1005',
        'user_id' => $seedUserId,
        'customer_name' => 'Carlos Reyes',
        'department' => 'Sales',
        'location' => 'Floor 1, Sales Area',
        'device_type' => 'Display',
        'manufacturer' => 'Dell',
        'model' => 'UltraSharp U2723QE',
        'serial_number' => 'DELL-US-2723-QE',
        'problem_description' => 'Monitor shows black screen but power LED is on',
        'priority' => 'high',
        'status' => 'solved',
        'started_at' => $yesterday,
        'ended_at' => $yesterday,
        'resolution' => 'Replaced HDMI cable. Monitor now displaying correctly at 4K 60Hz. Verified with Windows display settings and ran display calibration.',
        'resolution_type' => 'completed',
        'parts_replaced' => 'HDMI cable',
        'tools_used' => 'None',
        'steps_performed' => 'Checked power LED — on. Tried different HDMI port — same issue. Replaced HDMI cable with known good cable. Monitor displayed immediately. Verified resolution and refresh rate.',
        'time_spent_minutes' => 20,
        'address' => 'Sales Area, Ground Floor',
        'latitude' => '14.5544',
        'longitude' => '120.9864',
    ],
    [
        'ticket_number' => 'TK-1006',
        'user_id' => $seedUserId,
        'customer_name' => 'System Admin',
        'department' => 'IT',
        'location' => 'Server Room B',
        'device_type' => 'Hardware',
        'manufacturer' => 'HPE',
        'model' => 'ProLiant DL380 Gen10',
        'serial_number' => 'HPE-DL380-GEN10-SRVB',
        'problem_description' => 'Server beeping continuously, amber LED on motherboard',
        'priority' => 'critical',
        'status' => 'in_progress',
        'started_at' => $now,
        'ended_at' => null,
        'resolution' => null,
        'resolution_type' => null,
        'parts_replaced' => null,
        'tools_used' => null,
        'steps_performed' => null,
        'time_spent_minutes' => null,
        'address' => 'Server Room B, Basement Level',
        'latitude' => '14.5539',
        'longitude' => '120.9859',
    ],
];

$existing = [];
try {
    $existingRows = Database::fetchAll("SELECT ticket_number FROM troubleshooting_sessions WHERE ticket_number IS NOT NULL");
    foreach ($existingRows as $e) { $existing[$e['ticket_number']] = true; }
} catch (Exception $e) { $existing = []; }

$inserted = 0;
$skipped = 0;
foreach ($rows as $r) {
    if (isset($existing[$r['ticket_number']])) {
        // Update status to match demo (so the page reflects the intended state)
        try {
            Database::execute(
                "UPDATE troubleshooting_sessions SET status = ?, started_at = ?, ended_at = ?, resolution = ?, parts_replaced = ?, tools_used = ?, steps_performed = ?, time_spent_minutes = ?, model = ?, serial_number = ?, customer_name = ?, department = ?, location = ?, device_type = ?, manufacturer = ?, problem_description = ?, address = ?, latitude = ?, longitude = ? WHERE ticket_number = ?",
                [$r['status'], $r['started_at'], $r['ended_at'], $r['resolution'], $r['parts_replaced'], $r['tools_used'], $r['steps_performed'], $r['time_spent_minutes'], $r['model'], $r['serial_number'], $r['customer_name'], $r['department'], $r['location'], $r['device_type'], $r['manufacturer'], $r['problem_description'], $r['address'], $r['latitude'], $r['longitude'], $r['ticket_number']]
            );
            $skipped++;
        } catch (Exception $e) { $skipped++; }
        continue;
    }
    try {
        Database::insert('troubleshooting_sessions', $r);
        $inserted++;
    } catch (Exception $e) {
        echo "FAIL: " . $r['ticket_number'] . " — " . $e->getMessage() . "\n";
    }
}

echo "Seed complete: $inserted inserted, $skipped updated/skipped.\n";
echo "Seeded user id: $seedUserId\n";
