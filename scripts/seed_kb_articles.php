<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';

$articles = [
    [
        'id' => 26, 'title' => 'No Display Troubleshooting', 'category' => 'Display',
        'device_type' => 'Desktop',
        'issue' => 'Computer turns on but monitor shows no image or completely black screen.',
        'symptoms' => 'Black screen, No signal message, Monitor LED on but no image, Flickering display, Monitor shows No Input',
        'root_cause' => 'Most commonly caused by loose display cables, improperly seated RAM, failed GPU, wrong monitor input source, or monitor hardware failure.',
        'solution' => "Check Power:Verify both computer and monitor have power cables connected and powered on.\nCheck Monitor Input Source:Press the input/source button on the monitor to select the correct input (HDMI, DisplayPort, VGA).\nReseat Display Cable:Power off, disconnect and firmly reconnect HDMI/DisplayPort/VGA cable at both ends.\nTest Different Cable:Try a known-good display cable to rule out cable failure.\nTest Different Monitor:Connect a known-good monitor to isolate whether it is the monitor or PC.\nReseat RAM:Power off, open case, remove and reseat RAM modules firmly.\nReseat GPU:Power off, remove and reinsert the graphics card, check PCIe power connectors.\nTest with Integrated Graphics:Remove GPU and connect monitor to motherboard video output.",
        'tools_used' => 'Known-good monitor, Known-good display cable, Phillips screwdriver, ESD wrist strap',
        'commands_used' => 'systeminfo,msinfo32',
    ],
    [
        'id' => 27, 'title' => 'Power Issues Guide', 'category' => 'Power',
        'device_type' => 'Desktop',
        'issue' => 'Computer does not respond at all when power button is pressed - completely dead.',
        'symptoms' => 'No power at all, No LEDs, No fan spin, No beep codes, Completely dead when pressing power button',
        'root_cause' => 'Caused by failed PSU, loose power cable, tripped surge protector, failed power button, or motherboard failure.',
        'solution' => "Check Power Outlet:Plug another device into the outlet to verify it works.\nCheck PSU Switch:Make sure the PSU switch on the back of the PC is in the ON position (I).\nReseat Power Cable:Disconnect and reconnect the power cable at both PSU and wall outlet.\nTest Different Cable:Try a different power cable if available.\nCheck Surge Protector:Make sure surge protector is on and working.\nTest PSU with Multimeter:Use a multimeter to test PSU voltages on the 24-pin connector (should show 12V on yellow, 5V on red).\nCheck Power Button:Open case and short the power button pins on the motherboard header to test.\nReseat 24-pin and CPU Power:Disconnect and firmly reconnect the 24-pin ATX and 4/8-pin CPU power connectors.\nTry Spare PSU:If possible, test with a known-good power supply.\nCheck Motherboard:Look for bulging capacitors or burnt components.",
        'tools_used' => 'Multimeter, Spare PSU, Phillips screwdriver, ESD wrist strap',
        'commands_used' => '',
    ],
    [
        'id' => 28, 'title' => 'WiFi Troubleshooting', 'category' => 'Network',
        'device_type' => 'Desktop',
        'issue' => 'Device cannot connect to WiFi or WiFi keeps disconnecting.',
        'symptoms' => 'Cannot connect to WiFi, WiFi keeps disconnecting, Connected but no internet, Slow WiFi speeds, Limited connectivity',
        'root_cause' => 'Caused by incorrect WiFi password, router issues, driver problems, DNS configuration, or signal interference.',
        'solution' => "Check WiFi is ON:Make sure WiFi switch on laptop is on or airplane mode is off.\nForget and Reconnect:Forget the WiFi network and reconnect with the correct password.\nRestart Router:Unplug router for 30 seconds, plug back in, wait for full boot.\nCheck Other Devices:See if other devices can connect to rule out device-specific issue.\nUpdate WiFi Driver:Open Device Manager, expand Network adapters, right-click WiFi adapter, Update driver.\nRun Network Troubleshooter:Settings > Network & Internet > Status > Network troubleshooter.\nReset Network Settings:Settings > Network & Internet > Advanced network settings > Network reset.\nFlush DNS:Open CMD as admin, run: ipconfig /flushdns then ipconfig /registerdns.\nRelease and Renew IP:Run: ipconfig /release then ipconfig /renew in CMD as admin.\nCheck DNS Settings:Set DNS to 8.8.8.8 and 8.8.4.4 in adapter IPv4 settings.",
        'tools_used' => 'None for basic steps, USB WiFi adapter for testing',
        'commands_used' => 'ipconfig /flushdns,ipconfig /release,ipconfig /renew,netsh winsock reset',
    ],
    [
        'id' => 29, 'title' => 'Printer Fix Guide', 'category' => 'Printer',
        'device_type' => 'Printer',
        'issue' => 'Printer does not respond to print jobs or shows as offline.',
        'symptoms' => 'Printer shows offline, Print jobs stuck in queue, Documents not printing, Printer not responding, Error lights on printer',
        'root_cause' => 'Caused by loose USB cable, wrong default printer, spooler service stopped, driver issues, or network connectivity problems for network printers.',
        'solution' => "Check Physical Connection:Ensure USB cable is firmly connected or network cable is plugged in.\nPower Cycle Printer:Turn printer off, wait 30 seconds, turn back on.\nClear Print Queue:Open Settings > Devices > Printers, open queue, cancel all documents.\nRestart Print Spooler:Open CMD as admin, run: net stop spooler then net start spooler.\nSet as Default Printer:Go to Settings > Devices > Printers, right-click the printer, select Set as default.\nUpdate Printer Driver:Download latest driver from manufacturer website.\nCheck Network:For network printers, ping the printer IP address from CMD.\nRemove and Re-add Printer:Remove the printer from Settings > Devices, then add it back.\nCheck Printer Display:Look at printer LCD for any error messages or warning lights.\nRun Printer Troubleshooter:Settings > Update & Security > Troubleshoot > Printer.",
        'tools_used' => 'USB cable, Ethernet cable (for network printers), Phillips screwdriver',
        'commands_used' => 'net stop spooler,net start spooler,ping,ipconfig',
    ],
    [
        'id' => 30, 'title' => 'BSOD Fix Guide', 'category' => 'Software',
        'device_type' => 'Desktop',
        'issue' => 'Windows shows blue screen with error code and automatically restarts.',
        'symptoms' => 'Blue Screen of Death, BSOD, System crash with error code, Automatic restart, Stop error',
        'root_cause' => 'Caused by faulty RAM, failing hard drive, incompatible drivers, overheating, or hardware failure.',
        'solution' => "Note the Stop Code:Write down the BSOD error code (e.g., IRQL_NOT_LESS_OR_EQUAL).\nBoot into Safe Mode:Restart, hold Shift, select Troubleshoot > Advanced > Startup Settings > Safe Mode.\nUninstall Recent Driver:If BSOD started after driver install, boot Safe Mode and uninstall it.\nRun SFC Scan:Open CMD as admin, run: sfc /scannow to repair system files.\nRun DISM:Run: DISM /Online /Cleanup-Image /RestoreHealth in CMD as admin.\nCheck RAM:Run Windows Memory Diagnostic (mdsched.exe) or use MemTest86 bootable USB.\nCheck Disk:Run: chkdsk C: /f /r in CMD as admin (may require restart).\nCheck Event Viewer:Open eventvwr.msc, check System and Application logs for critical errors.\nUpdate Windows:Settings > Update & Security > Windows Update > Check for updates.\nCheck Temps:Use HWMonitor to check CPU/GPU temperatures for overheating.",
        'tools_used' => 'MemTest86 bootable USB, Event Viewer, HWMonitor',
        'commands_used' => 'sfc /scannow,DISM /Online /Cleanup-Image /RestoreHealth,chkdsk C: /f /r,mdsched.exe',
    ],
    [
        'id' => 31, 'title' => 'Slow PC Fix', 'category' => 'Software',
        'device_type' => 'Desktop',
        'issue' => 'PC is noticeably slow, takes long to respond and load applications.',
        'symptoms' => 'Slow boot time, Applications take long to open, System freezes, High disk usage, High CPU usage, Laggy mouse',
        'root_cause' => 'Caused by too many startup programs, full hard drive, fragmented disk, malware, insufficient RAM, or failing hardware.',
        'solution' => "Check Task Manager:Press Ctrl+Shift+Esc, check CPU, Memory, and Disk usage percentages.\nDisable Startup Apps:Task Manager > Startup tab, disable unnecessary programs.\nClean Disk Space:Run Disk Cleanup (cleanmgr) or delete temp files.\nCheck for Malware:Run a full scan with Windows Defender or Malwarebytes.\nUpgrade to SSD:If using HDD, upgrading to SSD dramatically improves performance.\nAdd More RAM:If RAM usage is consistently above 80%, consider adding more RAM.\nRun Disk Defrag:For HDD only (not SSD): Optimize Drives from Start menu.\nCheck for Windows Updates:Install all pending Windows updates.\nUninstall Unused Programs:Control Panel > Programs > Uninstall unused software.\nCheck for Background Processes:Task Manager > Details tab, sort by CPU or Memory.",
        'tools_used' => 'Malwarebytes, CrystalDiskInfo (check disk health)',
        'commands_used' => 'cleanmgr,defrag C:',
    ],
    [
        'id' => 32, 'title' => 'CCTV Recording Issues', 'category' => 'CCTV',
        'device_type' => 'CCTV',
        'issue' => 'NVR/DVR shows camera but is not recording or camera shows offline.',
        'symptoms' => 'Camera shows no recording, No live feed, Playback empty, Camera offline in NVR, Motion detection not working',
        'root_cause' => 'Caused by network connectivity issues, full hard drive, power supply failure, or configuration problems.',
        'solution' => "Check Camera Power:Verify PoE injector/switch is powered and cable is connected.\nCheck Network Cable:Ensure Ethernet cable is securely connected at both ends.\nPing Camera IP:Open CMD and ping the camera IP to verify network connectivity.\nCheck NVR Storage:NVR/DVR interface > check if hard drive is full or failed.\nFormat Recording Disk:If disk is corrupted, format it through NVR settings (WARNING: erases footage).\nCheck Recording Schedule:NVR settings > make sure recording schedule is set to continuous or motion-triggered.\nRestart NVR and Camera:Power cycle both the NVR and camera.\nUpdate Camera Firmware:Download latest firmware from manufacturer website.\nCheck Camera Settings:Log into camera web interface, verify RTSP stream and recording settings.\nCheck Power Supply:Test PoE switch/injector output voltage.",
        'tools_used' => 'Network cable tester, PoE tester, PC for camera web interface access',
        'commands_used' => 'ping,ping -t',
    ],
    [
        'id' => 33, 'title' => 'No Sound Fix', 'category' => 'Audio',
        'device_type' => 'Desktop',
        'issue' => 'No audio output from speakers or headphones.',
        'symptoms' => 'No audio output, Crackling sound, Audio cuts out, Speakers not detected, Headphone jack not working',
        'root_cause' => 'Caused by muted audio, wrong output device, disabled audio service, driver issues, or hardware failure.',
        'solution' => "Check Volume:Click speaker icon in taskbar, make sure not muted and volume is up.\nCheck Output Device:Right-click speaker icon > Sound settings > select correct output device.\nTest Different Audio:Try playing different audio (YouTube, local file) to rule out app-specific issue.\nRestart Audio Service:Open CMD as admin, run: net stop Audiosrv then net start Audiosrv.\nUpdate Audio Driver:Device Manager > Sound controllers > right-click > Update driver.\nReinstall Audio Driver:Device Manager > Sound controllers > right-click > Uninstall, restart PC to reinstall.\nCheck Audio Connections:Make sure speakers/headphones are plugged into the correct jack (usually green).\nTest Different Port:Try front and back audio jacks on the PC.\nDisable Audio Enhancements:Right-click speaker icon > Sound settings > select device > Properties > Disable enhancements.\nCheck BIOS:Enter BIOS setup and verify onboard audio is enabled.",
        'tools_used' => 'Known-good speakers or headphones, USB audio adapter',
        'commands_used' => 'net stop Audiosrv,net start Audiosrv,sndvol',
    ],
];

$count = 0;
foreach ($articles as $a) {
    Database::update('knowledge_articles', [
        'issue' => $a['issue'],
        'symptoms' => $a['symptoms'],
        'root_cause' => $a['root_cause'],
        'solution' => $a['solution'],
        'tools_used' => $a['tools_used'],
        'commands_used' => $a['commands_used'],
        'device_type' => $a['device_type'],
    ], 'id = ?', [$a['id']]);
    $count++;
    echo "Updated: {$a['title']} (id={$a['id']})\n";
}
echo "\nDone! Updated {$count} knowledge base articles.\n";
