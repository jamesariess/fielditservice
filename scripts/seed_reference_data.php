<?php
/**
 * Seed ALL reference data into both main DB and AI DB
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/config/ai_db.php';
require_once APP_ROOT . '/includes/AIDatabase.php';

echo "=== Seeding All Reference Data ===\n\n";

// ===== ERROR CODES =====
echo "Seeding error codes...\n";
$errorCodes = [
    ['code' => 'CRITICAL_PROCESS_DIED', 'title' => 'Critical Process Died', 'description' => 'Windows critical process crashed. BSOD error indicating a core system process terminated unexpectedly.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Corrupted system files, failing RAM, outdated drivers, malware.', 'fix_steps' => 'Boot Safe Mode. Run sfc /scannow. Run DISM. Update drivers. Test RAM with MemTest86.'],
    ['code' => 'IRQL_NOT_LESS_OR_EQUAL', 'title' => 'IRQL Not Less Or Equal', 'description' => 'Kernel-mode process attempted to access memory without proper permissions.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Faulty RAM, incompatible drivers, corrupted system files.', 'fix_steps' => 'Boot Safe Mode. Update/remove recently installed drivers. Run sfc /scannow. Test RAM.'],
    ['code' => 'KERNEL_DATA_INPAGE_ERROR', 'title' => 'Kernel Data Inpage Error', 'description' => 'Windows failed to read/write data from the page file. Usually indicates failing hard drive.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Failing hard drive, bad sectors, corrupted NTFS, failing RAM.', 'fix_steps' => 'Run chkdsk C: /f /r. Check S.M.A.R.T. status. Backup data immediately. Test RAM.'],
    ['code' => 'SYSTEM_SERVICE_EXCEPTION', 'title' => 'System Service Exception', 'description' => 'A system service process encountered an unexpected exception.', 'category' => 'BSOD', 'severity' => 'high', 'common_causes' => 'Outdated drivers, Windows update issues, software conflicts.', 'fix_steps' => 'Note faulting driver. Update that driver. Run sfc /scannow. Uninstall recent software.'],
    ['code' => 'PAGE_FAULT_IN_NONPAGED_AREA', 'title' => 'Page Fault In Nonpaged Area', 'description' => 'Windows tried to access data in memory that was not available. Often RAM-related.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Faulty RAM, corrupted drivers, disk errors.', 'fix_steps' => 'Run sfc /scannow. Test RAM with MemTest86. Check disk with chkdsk. Update drivers.'],
    ['code' => 'WHEA_UNCORRECTABLE_ERROR', 'title' => 'WHEA Uncorrectable Error', 'description' => 'Hardware error detected. Usually indicates serious hardware problem.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Failing CPU, motherboard, unstable overclock, failing RAM.', 'fix_steps' => 'Reset BIOS to defaults. Check CPU temperature. Test RAM. Check motherboard. Escalate.'],
    ['code' => 'KERNEL_SECURITY_CHECK_FAILURE', 'title' => 'Kernel Security Check Failure', 'description' => 'Kernel security check failed. Corruption of critical kernel data structure.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Corrupted system files, incompatible drivers, failed Windows update.', 'fix_steps' => 'Boot Safe Mode. Run sfc and DISM. Uninstall recent driver updates. Test RAM.'],
    ['code' => 'MEMORY_MANAGEMENT', 'title' => 'Memory Management BSOD', 'description' => 'Windows encountered a memory management error.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Faulty RAM, driver conflicts, corrupted system files.', 'fix_steps' => 'Test RAM with MemTest86 overnight. Run sfc /scannow. Update GPU and chipset drivers.'],
    ['code' => 'DRIVER_IRQL_NOT_LESS_OR_EQUAL', 'title' => 'Driver IRQL Error', 'description' => 'A driver attempted to access improper memory address.', 'category' => 'BSOD', 'severity' => 'critical', 'common_causes' => 'Faulty driver (network/GPU), malware, corrupted drivers.', 'fix_steps' => 'Boot Safe Mode. Check Event Viewer for faulting driver. Uninstall and reinstall that driver.'],
    ['code' => '0x80070002', 'title' => 'Windows Update Error', 'description' => 'Windows Update cannot find specified file.', 'category' => 'Windows', 'severity' => 'medium', 'common_causes' => 'Corrupted update cache, incorrect date/time, disk space.', 'fix_steps' => 'Run Update Troubleshooter. Clear SoftwareDistribution folder. Reset Update components.'],
    ['code' => '0x800f0922', 'title' => 'Windows Update Error', 'description' => 'Windows Update failed. Server timed out.', 'category' => 'Windows', 'severity' => 'medium', 'common_causes' => 'VPN interference, firewall blocking, insufficient disk space.', 'fix_steps' => 'Disconnect VPN. Run DISM. Check disk space. Disable firewall temporarily.'],
    ['code' => 'DNS_PROBE_FINISHED_NXDOMAIN', 'title' => 'DNS Resolution Failed', 'description' => 'Browser cannot resolve the domain name.', 'category' => 'Network', 'severity' => 'medium', 'common_causes' => 'DNS server down, cache corrupted, DNS misconfigured.', 'fix_steps' => 'ipconfig /flushdns. Change DNS to 8.8.8.8. Test ping 8.8.8.8. Restart DNS client.'],
    ['code' => 'NO_BOOT_DEVICE', 'title' => 'No Boot Device Found', 'description' => 'BIOS cannot find a bootable device.', 'category' => 'Hardware', 'severity' => 'critical', 'common_causes' => 'HDD disconnected, boot order wrong, failing drive, corrupted bootloader.', 'fix_steps' => 'Enter BIOS, check boot order. Check SATA cable. Listen for drive sounds. Try USB recovery.'],
    ['code' => 'CPU_FAN_ERROR', 'title' => 'CPU Fan Error', 'description' => 'BIOS reports CPU fan not spinning or not detected.', 'category' => 'Hardware', 'severity' => 'high', 'common_causes' => 'Fan disconnected, failure, dust buildup, header loose.', 'fix_steps' => 'Open case, check fan connection. Clean dust. Test with known-good fan. Check BIOS settings.'],
];

foreach ($errorCodes as $ec) {
    Database::insert('error_codes', $ec);
}
echo "  ✓ " . count($errorCodes) . " error codes\n";

// ===== KNOWLEDGE ARTICLES =====
echo "Seeding knowledge articles...\n";
$articles = [
    ['title' => 'No Display Troubleshooting', 'category' => 'Display', 'issue' => 'Monitor shows no image', 'symptoms' => 'Black screen, No Signal, display blank', 'root_cause' => 'Loose cable, wrong input, failed GPU', 'solution' => 'Check cables. Try different port. Reseat RAM/GPU. Test spare monitor.', 'tools_used' => 'Spare cable, screwdriver', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'Power Issues Guide', 'category' => 'Power', 'issue' => 'Computer will not turn on', 'symptoms' => 'No lights, no fans, completely dead', 'root_cause' => 'Bad outlet, loose cable, dead PSU', 'solution' => 'Test outlet. Check PSU switch. Power drain. Check connections. Test PSU.', 'tools_used' => 'Multimeter, spare PSU', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'WiFi Troubleshooting', 'category' => 'Network', 'issue' => 'WiFi not working', 'symptoms' => 'No WiFi, drops, no internet', 'root_cause' => 'Disabled adapter, wrong password, driver issue', 'solution' => 'Toggle WiFi. Forget/reconnect. Update driver. Reset network stack.', 'tools_used' => 'Ethernet cable', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'Printer Fix Guide', 'category' => 'Printer', 'issue' => 'Printer not working', 'symptoms' => 'Offline, not printing, stuck jobs', 'root_cause' => 'Spooler stuck, wrong printer, driver issue', 'solution' => 'Restart spooler. Check offline mode. Clear queue. Reinstall driver.', 'tools_used' => 'USB cable', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'BSOD Fix Guide', 'category' => 'Software', 'issue' => 'Blue screen errors', 'symptoms' => 'Blue screen, restart, error code', 'root_cause' => 'Corrupted files, bad drivers, failing RAM', 'solution' => 'Note code. Safe Mode. sfc /scannow. DISM. Update drivers. Test RAM.', 'tools_used' => 'USB drive', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'Slow PC Fix', 'category' => 'Software', 'issue' => 'Computer running slow', 'symptoms' => 'High CPU/RAM, slow boot, freezes', 'root_cause' => 'Startup programs, malware, full disk', 'solution' => 'Restart. Check Task Manager. Disable startup. Disk cleanup. SFC. Antivirus.', 'tools_used' => 'Task Manager', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'CCTV Recording Issues', 'category' => 'CCTV', 'issue' => 'Camera not recording', 'symptoms' => 'No playback, recording stopped', 'root_cause' => 'Full disk, disconnected camera, IP conflict', 'solution' => 'Check disk space. Restart NVR. Ping camera. Check PoE. Re-add camera.', 'tools_used' => 'Monitor, Ethernet cable', 'author_id' => 1, 'status' => 'published'],
    ['title' => 'No Sound Fix', 'category' => 'Audio', 'issue' => 'No audio output', 'symptoms' => 'Muted, no sound, device not detected', 'root_cause' => 'Muted audio, wrong device, driver issue', 'solution' => 'Check volume. Select device. Test speakers. Audio Troubleshooter. Reinstall driver.', 'tools_used' => 'Working speakers', 'author_id' => 1, 'status' => 'published'],
];

foreach ($articles as $art) {
    Database::insert('knowledge_articles', $art);
}
echo "  ✓ " . count($articles) . " knowledge articles\n";

// ===== COMMANDS =====
echo "Seeding commands...\n";
$commands = [
    ['category_id' => 1, 'command' => 'ipconfig /release', 'description' => 'Releases current DHCP IP address', 'when_to_use' => 'Network issues, IP conflict', 'example' => 'ipconfig /release'],
    ['category_id' => 1, 'command' => 'ipconfig /renew', 'description' => 'Requests new IP from DHCP', 'when_to_use' => 'After releasing IP', 'example' => 'ipconfig /renew'],
    ['category_id' => 1, 'command' => 'ipconfig /flushdns', 'description' => 'Clears DNS resolver cache', 'when_to_use' => 'DNS issues, website not loading', 'example' => 'ipconfig /flushdns'],
    ['category_id' => 1, 'command' => 'netsh winsock reset', 'description' => 'Resets Winsock catalog', 'when_to_use' => 'Network not working, can connect but no internet', 'example' => 'netsh winsock reset'],
    ['category_id' => 1, 'command' => 'ping 8.8.8.8', 'description' => 'Tests internet connectivity', 'when_to_use' => 'Checking if internet works', 'example' => 'ping 8.8.8.8'],
    ['category_id' => 2, 'command' => 'sfc /scannow', 'description' => 'Scans and repairs system files', 'when_to_use' => 'BSOD, crashes, missing DLLs', 'example' => 'sfc /scannow'],
    ['category_id' => 2, 'command' => 'DISM /Online /Cleanup-Image /RestoreHealth', 'description' => 'Repairs Windows image', 'when_to_use' => 'SFC cannot fix issues', 'example' => 'DISM /Online /Cleanup-Image /RestoreHealth'],
    ['category_id' => 2, 'command' => 'shutdown /r /t 0', 'description' => 'Immediate restart', 'when_to_use' => 'Need quick restart', 'example' => 'shutdown /r /t 0'],
    ['category_id' => 2, 'command' => 'msconfig', 'description' => 'System Configuration utility', 'when_to_use' => 'Manage startup, clean boot', 'example' => 'msconfig'],
    ['category_id' => 3, 'command' => 'chkdsk C: /f /r', 'description' => 'Checks and repairs disk errors', 'when_to_use' => 'Disk errors, slow access', 'example' => 'chkdsk C: /f /r'],
    ['category_id' => 3, 'command' => 'wmic diskdrive get status', 'description' => 'Checks hard drive health', 'when_to_use' => 'Suspected disk issues', 'example' => 'wmic diskdrive get status'],
    ['category_id' => 4, 'command' => 'net stop spooler && net start spooler', 'description' => 'Restarts print spooler', 'when_to_use' => 'Printer offline, stuck jobs', 'example' => 'net stop spooler && net start spooler'],
    ['category_id' => 5, 'command' => 'net user username newpassword', 'description' => 'Resets Windows password', 'when_to_use' => 'User locked out', 'example' => 'net user john P@ss123'],
    ['category_id' => 2, 'command' => 'taskkill /F /IM process.exe', 'description' => 'Force kills a process', 'when_to_use' => 'Unresponsive app', 'example' => 'taskkill /F /IM chrome.exe'],
    ['category_id' => 2, 'command' => 'powercfg /batteryreport', 'description' => 'Generates battery health report', 'when_to_use' => 'Laptop battery issues', 'example' => 'powercfg /batteryreport'],
];

foreach ($commands as $cmd) {
    Database::insert('commands', $cmd);
}
echo "  ✓ " . count($commands) . " commands\n";

// ===== TOOLS =====
echo "Seeding tools...\n";
$tools = [
    ['name' => 'Multimeter', 'purpose' => 'Measure voltage, current, resistance', 'when_to_use' => 'Testing PSU voltages, diagnosing power issues', 'how_to_use' => 'Set to DC voltage. Touch probes to PSU pins.', 'safety' => 'Do not touch PSU internals.'],
    ['name' => 'CrystalDiskInfo', 'purpose' => 'Check drive health via S.M.A.R.T.', 'when_to_use' => 'Checking hard drive/SSD health', 'how_to_use' => 'Open to see health status.', 'safety' => 'Read-only tool.'],
    ['name' => 'MemTest86', 'purpose' => 'Test RAM for errors', 'when_to_use' => 'Diagnosing RAM-related BSOD and crashes', 'how_to_use' => 'Boot from USB. Run 1+ passes.', 'safety' => 'Requires USB boot.'],
    ['name' => 'HWMonitor', 'purpose' => 'Monitor temperatures and voltages', 'when_to_use' => 'Checking for overheating issues', 'how_to_use' => 'Open and check CPU/GPU temps.', 'safety' => 'Read-only.'],
    ['name' => 'Task Manager', 'purpose' => 'Monitor processes and resources', 'when_to_use' => 'Finding high CPU/RAM usage, managing startup', 'how_to_use' => 'Ctrl+Shift+Esc.', 'safety' => 'Built-in Windows tool.'],
    ['name' => 'Event Viewer', 'purpose' => 'View system logs', 'when_to_use' => 'Finding error details for BSOD and crashes', 'how_to_use' => 'Open eventvwr.msc.', 'safety' => 'Read-only.'],
    ['name' => 'Compressed Air', 'purpose' => 'Clean dust from components', 'when_to_use' => 'Fixing overheating, cleaning fans', 'how_to_use' => 'Short bursts on fans/heatsinks.', 'safety' => 'Hold can upright.'],
    ['name' => 'USB Bootable Drive', 'purpose' => 'Recovery and diagnostics', 'when_to_use' => 'Windows will not boot, need recovery', 'how_to_use' => 'Create with Media Creation Tool.', 'safety' => 'Backup before use.'],
];

foreach ($tools as $tool) {
    Database::insert('tools', $tool);
}
echo "  ✓ " . count($tools) . " tools\n";

// ===== SYNC TO AI DATABASE =====
echo "\nSyncing to AI database...\n";
$tables = ['error_codes', 'knowledge_articles', 'commands', 'tools'];
foreach ($tables as $table) {
    try {
        AIDatabase::execute("DELETE FROM {$table}");
        $rows = Database::fetchAll("SELECT * FROM {$table}");
        $count = 0;
        foreach ($rows as $row) { AIDatabase::insert($table, $row); $count++; }
        echo "  ✓ {$table}: {$count} records\n";
    } catch (Exception $e) {
        echo "  ⚠ {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\nDone! 🎉\n";
