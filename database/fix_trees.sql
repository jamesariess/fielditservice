-- ============================================================
-- FIX ALL INCOMPLETE DECISION TREES
-- Correct schema: id, issue_id, parent_id, yes_next, no_next,
-- question, description, risk, is_terminal, result_type, result_solution
-- ============================================================

-- Delete incomplete trees for issues that need fixing
DELETE FROM decision_nodes WHERE issue_id IN (5,7,10,11,12,13,15,16,17,18,19,20);

-- ============================================================
-- Issue 5: WiFi Not Connecting
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(5, NULL, 2001, 2002, 'Is WiFi enabled on the device?', 'Check the WiFi toggle in system tray or Settings > Network & Internet > WiFi. Most laptops also have a physical WiFi switch or function key (F12 with antenna icon).', 'safe', 0, NULL, NULL),
-- Node 2001: WiFi is ON
(5, 2000, 2003, 2004, 'Can you see your WiFi network in the available networks list?', 'Click the WiFi icon in the system tray. Look for your network name (SSID) in the list of available networks.', 'safe', 0, NULL, NULL),
-- Node 2002: WiFi is OFF → enable it
(5, 2000, 2001, NULL, 'WiFi was turned off. Enable it and check again.', 'Press the WiFi toggle key (usually F12 or a key with antenna icon) or enable WiFi in Settings > Network & Internet > WiFi.', 'safe', 1, 'solved', 'WiFi adapter was disabled. Enabled WiFi and the network is now visible in the available networks list.'),
-- Node 2003: Network visible → check password
(5, 2001, 2005, 2006, 'Are you entering the correct WiFi password?', 'Double-check the password. WiFi passwords are case-sensitive. Try typing it in a text editor first to verify before entering it in the WiFi dialog.', 'safe', 0, NULL, NULL),
-- Node 2004: Network NOT visible → check router
(5, 2001, 2007, NULL, 'Your network is not visible. Check if the router is broadcasting.', 'Verify the router is powered on. Check if SSID broadcast is enabled in router settings (192.168.1.1). The network name may have been changed or hidden.', 'caution', 1, 'solved', 'WiFi network was not broadcasting. Router was restarted or SSID broadcast was re-enabled. Network is now visible.'),
-- Node 2005: Password correct → test connection
(5, 2003, NULL, NULL, 'Connection successful. Test internet access.', 'Open a browser and try loading a website. Check if the WiFi icon shows connected with internet access.', 'safe', 1, 'solved', 'WiFi connected successfully with correct password. Internet access confirmed working.'),
-- Node 2006: Password wrong → reset
(5, 2003, 2005, NULL, 'Password is incorrect. Reset or confirm the correct password.', 'Check the router label for default password, or access router admin page (192.168.1.1) to view or reset WiFi password. Note: changing the password requires reconnecting all devices.', 'caution', 1, 'solved', 'WiFi password was incorrect. Corrected the password and connection succeeded.'),
-- Node 2007: Router issue → restart
(5, 2004, 2003, 2008, 'Restart the router. Unplug power for 30 seconds, then reconnect.', 'Unplug the router power cable. Wait 30 seconds. Plug back in. Wait 2 minutes for full boot and all LEDs to stabilize.', 'safe', 0, NULL, NULL),
-- Node 2008: Still not visible → forget & reconnect
(5, 2007, 2005, 2009, 'Try forgetting the network and reconnecting.', 'Go to Settings > WiFi > Manage known networks. Select your network and click Forget. Then reconnect by selecting it from the list and entering the password.', 'safe', 0, NULL, NULL),
-- Node 2009: Still failing → reset adapter
(5, 2008, 2005, 2010, 'Reset the network adapter.', 'Open Device Manager > Network adapters. Right-click your WiFi adapter and select Disable, wait 5 seconds, then Enable. If still failing, select Uninstall device and restart the computer.', 'caution', 0, NULL, NULL),
-- Node 2010: Still failing → reset TCP/IP
(5, 2009, 2005, NULL, 'Reset TCP/IP stack via command prompt.', 'Run Command Prompt as Administrator. Type: netsh winsock reset && netsh int ip reset. Restart the computer. This resets all network configurations to defaults.', 'high', 1, 'solved', 'TCP/IP stack was corrupted. Reset via netsh commands and restarted. WiFi now connects and internet works.');

-- ============================================================
-- Issue 7: Camera Offline (CCTV)
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(7, NULL, 2501, 2502, 'Is the camera powered on? (Check LED indicator)', 'Look at the camera. Most IP cameras have a power LED (red or green) near the lens or on the base. No LED usually means no power.', 'safe', 0, NULL, NULL),
-- 2501: Camera powered → check network
(7, 2500, 2503, 2504, 'Is the network cable connected? (For wired cameras)', 'Check the Ethernet cable going into the camera. Ensure it clicks firmly into the port. Check the link LED on the camera port.', 'safe', 0, NULL, NULL),
-- 2502: Camera NOT powered → check adapter
(7, 2500, 2503, 2505, 'Camera has no power. Check the power adapter.', 'Verify the power adapter is plugged into both the camera and a working outlet. Test the outlet with another device. Check adapter LED if it has one.', 'caution', 0, NULL, NULL),
-- 2503: Connected → ping test
(7, 2501, 2506, 2507, 'Can you ping the camera from the NVR or a computer?', 'Open Command Prompt on a computer on the same network. Type: ping [camera IP address]. Check if you get reply messages.', 'safe', 0, NULL, NULL),
-- 2504: Cable loose → reseat
(7, 2501, 2503, 2508, 'Reseat the Ethernet cable at both ends.', 'Unplug the cable from the camera and from the NVR/switch. Plug back in firmly until you hear a click. Check link lights.', 'safe', 0, NULL, NULL),
-- 2505: Bad adapter → replace
(7, 2502, 2503, NULL, 'Replace the power adapter with a compatible one.', 'Use a power adapter with the same voltage (V) and amperage (A) rating as the original. Check the label on the old adapter.', 'caution', 1, 'solved', 'Camera power adapter was faulty. Replaced with compatible adapter. Camera now powers on and shows online.'),
-- 2506: Ping works → check NVR config
(7, 2503, NULL, NULL, 'Camera is reachable. Check NVR/VMS software settings.', 'Open your NVR or VMS software. Verify the camera channel is configured with the correct IP address, port, and credentials. Test the live view.', 'safe', 1, 'solved', 'Camera was online but NVR configuration was incorrect. Updated IP/port/credentials. Camera now shows live view and recording.'),
-- 2507: Ping fails → check switch
(7, 2503, 2503, 2509, 'Camera not reachable on network. Check the network switch.', 'Verify the network switch port LED is on. Try connecting the camera to a different port. Check if other devices on the same switch can communicate.', 'caution', 0, NULL, NULL),
-- 2508: Try different cable
(7, 2504, 2503, 2509, 'Try a different Ethernet cable.', 'Replace with a known-good Cat5e or Cat6 cable. Damaged cables can fail internally without visible damage.', 'safe', 0, NULL, NULL),
-- 2509: Restart NVR and camera
(7, 2507, 2506, 2510, 'Restart the NVR/DVR and the camera.', 'Power cycle the NVR first (unplug for 30 seconds), then the camera. Wait 2 minutes for both to fully boot and reconnect.', 'caution', 0, NULL, NULL),
-- 2510: Factory reset camera
(7, 2509, 2506, NULL, 'Factory reset the camera (last resort).', 'Press and hold the reset button on the camera for 10-15 seconds until the LED flashes. Re-add the camera in NVR software with default settings.', 'high', 1, 'solved', 'Camera was unreachable. Performed factory reset and re-added to NVR. Camera now online and recording. All custom settings (motion zones, schedules) need to be reconfigured.');

-- ============================================================
-- Issue 10: Network Slow
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(10, NULL, 3001, NULL, 'Run a speed test at speedtest.net.', 'Open speedtest.net in your browser. Click Go and wait for results. Note the download speed, upload speed, and ping.', 'safe', 0, NULL, NULL),
-- 3001: Speed test done → compare to plan
(10, 3000, 3002, 3003, 'Is the speed significantly lower than your internet plan?', 'Compare the result with what you pay for. If you have a 100 Mbps plan but get 10 Mbps, that is a problem. If you get 90 Mbps, that is normal.', 'safe', 0, NULL, NULL),
-- 3002: Speed is low → check other devices
(10, 3001, 3004, 3005, 'Are other devices on the network also slow?', 'Test speed on another device (phone, tablet, another computer) connected to the same WiFi network.', 'safe', 0, NULL, NULL),
-- 3003: Speed is fine → app-specific
(10, 3001, NULL, NULL, 'Speed is normal for your plan. The issue may be app or website specific.', 'Some websites throttle during peak hours. Some applications use more bandwidth than others. Check if the slowness is only with one app or site.', 'safe', 1, 'solved', 'Internet speed is within normal range for your plan. Slowness may be due to specific website/application server issues, not your network.'),
-- 3004: All devices slow → restart router
(10, 3002, 3006, NULL, 'Restart the modem and router. Unplug both for 30 seconds.', 'Unplug modem power, wait 30 seconds, plug back in. Wait 2 minutes for full boot. Repeat for router. Then retest speed.', 'safe', 0, NULL, NULL),
-- 3005: Only this device → check background
(10, 3002, 3007, 3008, 'Only this device is slow. Check for background processes using bandwidth.', 'Open Task Manager (Ctrl+Shift+Esc) > Performance tab. Check for processes using high network or CPU. Common culprits: Windows Update, OneDrive sync, antivirus scans.', 'safe', 0, NULL, NULL),
-- 3006: After restart → still slow → contact ISP
(10, 3004, NULL, NULL, 'Speed is still slow after restart. Contact your ISP.', 'Call your ISP to report slow speeds. Provide your speed test results. Ask if there is an outage or maintenance in your area.', 'safe', 1, 'escalation', 'Speed remains slow after router restart. All devices affected. Issue appears to be with ISP service. Contact ISP with speed test results for further investigation.'),
-- 3007: Found background process → disable
(10, 3005, NULL, NULL, 'Disable the background process and retest.', 'Pause OneDrive sync, stop Windows Update downloads, or close the consuming application. Run speed test again.', 'safe', 1, 'solved', 'Identified bandwidth-consuming background process. Disabled/paused the process. Speed returned to normal after freeing up bandwidth.'),
-- 3008: No obvious background process → reset adapter
(10, 3005, 3003, 3009, 'Reset the network adapter.', 'Open Device Manager > Network adapters. Right-click your network adapter > Disable, wait 5 seconds, then Enable. Retest speed.', 'caution', 0, NULL, NULL),
-- 3009: Still slow → try Google DNS
(10, 3008, 3003, NULL, 'Switch to Google DNS (8.8.8.8).', 'Open Network Settings > Adapter Properties > IPv4. Set preferred DNS to 8.8.8.8 and alternate to 8.8.4.4. Retest.', 'caution', 1, 'solved', 'ISP DNS was causing slow resolution. Switched to Google DNS (8.8.8.8). Browsing speed improved significantly.');

-- ============================================================
-- Issue 11: Random Shutdowns
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(11, NULL, 3501, 3502, 'Is the computer overheating? Check CPU temperature.', 'Open BIOS setup or use HWMonitor/Core Temp software. Normal idle temperature: 30-50°C. Normal under load: 60-85°C. Above 90°C is dangerous.', 'safe', 0, NULL, NULL),
-- 3501: Temp OK → check event log
(11, 3500, 3503, NULL, 'Temperature is normal. Check Event Viewer for shutdown reason.', 'Press Win+R, type eventvwr, press Enter. Go to Windows Logs > System. Look for Event ID 41 (Kernel-Power) for unexpected shutdowns.', 'safe', 0, NULL, NULL),
-- 3502: Overheating → clean cooling
(11, 3500, 3501, NULL, 'CPU is overheating. Clean the cooling system.', 'Power off and unplug the computer. Open the case. Use compressed air to clean dust from CPU heatsink, GPU heatsink, and all case fans. Check that all fans spin freely.', 'high', 1, 'solved', 'CPU was overheating due to dust buildup on heatsinks and fans. Cleaned the cooling system with compressed air. Temperature dropped to normal range.'),
-- 3503: Check event log → during heavy use?
(11, 3501, 3504, 3505, 'Does the shutdown happen during heavy use (gaming, rendering, compiling)?', 'Note when the shutdowns occur. Are they random or during specific activities? Try running a stress test (Prime95 for CPU, FurMark for GPU) to reproduce.', 'caution', 0, NULL, NULL),
-- 3504: Under load → test PSU
(11, 3503, 3506, NULL, 'Shutdowns during heavy use. Test the power supply.', 'Use a PSU tester to check voltage rails. 12V rail should read 11.4-12.6V. 5V rail should be 4.75-5.25V. If you don\'t have a tester, try a known-good PSU.', 'high', 0, NULL, NULL),
-- 3505: Random/idle → test RAM
(11, 3503, 3507, 3508, 'Shutdowns are random. Test the RAM.', 'Run Windows Memory Diagnostic: press Win+R, type mdsched.exe, select restart now. Or boot from a USB with MemTest86 and run overnight.', 'caution', 0, NULL, NULL),
-- 3506: PSU failing → replace
(11, 3504, NULL, NULL, 'PSU is failing. Replace the power supply.', 'Replace with a quality PSU of sufficient wattage (calculate at pcpartpicker.com). Choose 80+ Bronze or better certification.', 'high', 1, 'hardware', 'Power supply unit is failing and cannot deliver stable voltage under load. Replace with a new PSU of appropriate wattage and 80+ certification.'),
-- 3507: RAM OK → check system files
(11, 3505, 3509, NULL, 'RAM is OK. Check for system file corruption.', 'Run as Administrator: sfc /scannow (wait for completion). Then run: DISM /Online /Cleanup-Image /RestoreHealth. Restart when done.', 'safe', 0, NULL, NULL),
-- 3508: RAM errors → replace
(11, 3505, NULL, NULL, 'Memory errors detected. Replace faulty RAM.', 'If MemTest86 shows errors, identify which stick is faulty by testing each individually. Replace the failing RAM module.', 'high', 1, 'hardware', 'RAM errors detected by Windows Memory Diagnostic / MemTest86. Faulty memory module identified and needs replacement. Running with bad RAM causes data corruption and random crashes.'),
-- 3509: System files OK → update BIOS
(11, 3507, 3505, NULL, 'Update BIOS and chipset drivers.', 'Download the latest BIOS from your motherboard manufacturer website. Follow their update instructions carefully. Also update chipset drivers from Device Manager.', 'caution', 1, 'solved', 'Updated BIOS and chipset drivers to latest versions. Random shutdowns resolved. Outdated BIOS firmware had a power management bug.');

-- ============================================================
-- Issue 12: Overheating
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(12, NULL, 4001, NULL, 'What is the current CPU/GPU temperature?', 'Use HWMonitor, Core Temp, or check BIOS hardware monitor. Idle: 30-50°C. Load: 60-85°C. Above 90°C is dangerous and requires immediate action.', 'safe', 0, NULL, NULL),
-- 4001: Check temp → above 90?
(12, 4000, 4002, 4003, 'Is the temperature above 90°C under load?', 'Run a stress test (Prime95 for CPU, FurMark for GPU) for 10 minutes while monitoring temperature. Note the peak temperature reached.', 'caution', 0, NULL, NULL),
-- 4002: Under 90 → safe
(12, 4001, NULL, NULL, 'Temperature is within safe range. Monitor periodically.', 'Your system is operating within normal thermal limits. Continue monitoring during heavy use. Consider improving airflow if temperatures approach 85°C.', 'safe', 1, 'solved', 'CPU/GPU temperature is within normal operating range (below 85°C under load). No action needed. Continue periodic monitoring.'),
-- 4003: Above 90 → check fans
(12, 4001, 4004, 4005, 'Are the fans spinning? Open the case and check.', 'Power off, unplug, and open the side panel. Power on briefly and check: CPU cooler fan, GPU fans, case fans (front and rear), PSU fan. All should be spinning.', 'caution', 0, NULL, NULL),
-- 4004: Fans OK but still hot → thermal paste
(12, 4003, 4002, 4006, 'Fans are spinning but still overheating. Check thermal paste.', 'Remove the CPU cooler. Clean old thermal paste from CPU and cooler with isopropyl alcohol (90%+). Apply a pea-sized dot of new thermal paste. Reattach cooler firmly.', 'high', 0, NULL, NULL),
-- 4005: Fan dead → replace
(12, 4003, 4004, NULL, 'A fan is not spinning. Replace the failed fan.', 'Identify which fan has failed. Order a replacement of the same size (80mm, 120mm, etc.) and connector type. Install and verify it spins.', 'caution', 1, 'solved', 'Case/CPU fan was not spinning, causing overheating. Replaced the failed fan. Temperature now within normal range.'),
-- 4006: New paste applied → retest
(12, 4004, 4002, 4007, 'New thermal paste applied. Retest temperature.', 'Run the stress test again for 10 minutes. Monitor peak temperature. New thermal paste should significantly reduce temperatures.', 'high', 0, NULL, NULL),
-- 4007: Still hot → improve airflow
(12, 4006, 4002, NULL, 'Still overheating. Improve case airflow.', 'Add more case fans, ensure cables are not blocking airflow paths, remove obstructions from intake/exhaust vents, ensure front-to-back airflow direction.', 'caution', 1, 'solved', 'Improved case airflow by adding fans and managing cables. CPU temperature now within safe range under full load.');

-- ============================================================
-- Issue 13: Application Crash
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(13, NULL, 4501, 4502, 'Does the application crash immediately on launch or during use?', 'Try opening the application. Note whether it crashes right away (before you can use it) or after using it for a while. This determines the troubleshooting path.', 'safe', 0, NULL, NULL),
-- 4501: Crashes on launch → check error
(13, 4500, 4503, 4504, 'Application crashes on launch. Check for any error message.', 'Note any error message, pop-up window, or error code displayed when the application crashes. Write down the exact text.', 'safe', 0, NULL, NULL),
-- 4502: Crashes during use → note steps
(13, 4500, 4505, 4506, 'Application crashes during use. Note what you were doing.', 'Reproduce the crash by following the same steps. Note: which menu, which button, how long after opening, what data was being processed.', 'safe', 0, NULL, NULL),
-- 4503: Has error → try admin
(13, 4501, 4507, 4508, 'Try running the application as Administrator.', 'Right-click the application shortcut > Run as administrator. If it works, right-click > Properties > Compatibility > check "Run this program as an administrator".', 'caution', 0, NULL, NULL),
-- 4504: No error message → Event Viewer
(13, 4501, 4503, 4509, 'No error message. Check Windows Event Viewer.', 'Press Win+R, type eventvwr. Go to Windows Logs > Application. Look for Error events with the application name around the time it crashed.', 'safe', 0, NULL, NULL),
-- 4505: During use → memory check
(13, 4502, 4510, 4511, 'Check available memory when crash occurs.', 'Open Task Manager (Ctrl+Shift+Esc) > Performance > Memory. Watch memory usage while using the application. Note if it spikes to near 100% before crash.', 'safe', 0, NULL, NULL),
-- 4506: During use → check updates
(13, 4502, 4507, NULL, 'Check for Windows and application updates.', 'Open the application and check Help > Check for Updates. Also run Windows Update from Settings. Install any available updates.', 'safe', 1, 'solved', 'Application was crashing due to a known bug that was fixed in a recent update. Installed the latest update and the application now runs without crashing.'),
-- 4507: Works now → verify stability
(13, 4503, NULL, NULL, 'Application works. Test stability for 30 minutes.', 'Use the application normally for at least 30 minutes. Perform the tasks that previously caused the crash. Confirm it runs without issues.', 'safe', 1, 'solved', 'Application crash resolved. Ran as administrator (or applied compatibility fix). Application now runs stably for extended periods.'),
-- 4508: Still crashes → reinstall
(13, 4503, 4507, NULL, 'Repair or reinstall the application.', 'Go to Settings > Apps > [App name] > Modify/Repair. If no repair option, uninstall completely and download a fresh installer from the official website.', 'caution', 1, 'solved', 'Application was corrupted. Performed repair/reinstall. Application now launches and runs without crashing.'),
-- 4509: Event log shows issue → conflict check
(13, 4504, 4508, NULL, 'Check for conflicting software.', 'Temporarily disable antivirus real-time protection, firewall rules, or other monitoring software. Test if the application launches. If it does, add an exception.', 'caution', 1, 'solved', 'Security software was blocking application components. Added exception/exclusion for the application. It now launches and runs normally.'),
-- 4510: Memory spike → free memory
(13, 4505, 4507, NULL, 'Close other applications to free memory.', 'Close unnecessary browser tabs (each uses 100-500MB), background apps, and services. Retest the application with more memory available.', 'safe', 1, 'solved', 'Application was crashing due to insufficient available memory. Closed background applications to free RAM. Application now runs without memory-related crashes.'),
-- 4511: Memory OK → compatibility
(13, 4505, 4507, NULL, 'Try running in compatibility mode.', 'Right-click shortcut > Properties > Compatibility tab. Check "Run this program in compatibility mode for:" and select an older Windows version (e.g., Windows 8 or 7).', 'caution', 1, 'solved', 'Application was not fully compatible with current Windows version. Enabled compatibility mode for Windows 8. Application now runs without crashing.');

-- ============================================================
-- Issue 15: Windows Update Fails
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(15, NULL, 5001, NULL, 'What is the error code for the failed update?', 'Open Settings > Windows Update > View update history. Note the error code (e.g., 0x80070002, 0x800f0922). Write it down — specific codes have specific fixes.', 'safe', 0, NULL, NULL),
-- 5001: Error code noted → run troubleshooter
(15, 5000, 5002, 5003, 'Run the Windows Update Troubleshooter.', 'Go to Settings > System > Troubleshoot > Other troubleshooters. Find Windows Update and click Run. Let it scan and apply fixes.', 'safe', 0, NULL, NULL),
-- 5002: Troubleshooter done → retry update
(15, 5001, NULL, 5003, 'Did the troubleshooter fix it? Retry the update.', 'Go back to Settings > Windows Update and click Check for updates. Try downloading and installing the failed update again.', 'safe', 1, 'solved', 'Windows Update Troubleshooter identified and fixed the issue (cleared SoftwareDistribution folder or reset BITS). Update installed successfully.'),
-- 5003: Troubleshooter failed → check disk space
(15, 5002, 5004, 5005, 'Check disk space on C: drive. Need at least 20GB free.', 'Open File Explorer > This PC. Check free space on C: drive. If low, run Disk Clean-up: press Win+R, type cleanmgr, select C: drive.', 'safe', 0, NULL, NULL),
-- 5004: Has space → reset components
(15, 5003, 5002, 5006, 'Reset Windows Update components manually.', 'Run Command Prompt as Administrator:\nnet stop wuauserv\nnet stop bits\nren C:\\Windows\\SoftwareDistribution SoftwareDistribution.old\nnet start wuauserv\nnet start bits\nThen retry the update.', 'caution', 0, NULL, NULL),
-- 5005: Low disk space → free space
(15, 5003, 5004, NULL, 'Free up disk space.', 'Run Disk Cleanup (cleanmgr) as Administrator. Check all boxes including "Windows Update Cleanup". Also delete temp files (Win+R, type %temp%). Uninstall unused programs.', 'safe', 0, NULL, NULL),
-- 5006: Still failing → manual install
(15, 5004, 5007, NULL, 'Download and install the update manually from Microsoft Update Catalog.', 'Go to catalog.update.microsoft.com. Search for the KB number from the error. Download the correct version (x64/x86). Run the downloaded installer.', 'caution', 0, NULL, NULL),
-- 5007: Manual installed → verify
(15, 5006, NULL, NULL, 'Run SFC and DISM to verify system file integrity.', 'Run as Administrator: sfc /scannow (wait for completion). Then: DISM /Online /Cleanup-Image /RestoreHealth. Restart when done.', 'safe', 1, 'solved', 'Update was installed manually from Microsoft Update Catalog. System files verified with SFC and DISM. Windows Update now functioning normally.');

-- ============================================================
-- Issue 16: No Recording (CCTV)
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(16, NULL, 5501, NULL, 'Is the camera online in the NVR/VMS software?', 'Open your NVR or VMS software. Check the camera list — does the camera show as online/connected or offline/disconnected?', 'safe', 0, NULL, NULL),
-- 5501: Camera online → check recording setting
(16, 5500, 5502, 5503, 'Is recording enabled for this camera channel?', 'In NVR/VMS, select the camera. Go to Recording or Schedule settings. Check if recording is enabled (continuous or motion-triggered). Verify the schedule is set correctly.', 'safe', 0, NULL, NULL),
-- 5502: Recording enabled → check storage
(16, 5501, 5504, 5505, 'Is there available storage on the NVR hard drive?', 'Check the NVR storage status in Settings > Storage or Disk Management. Look for available space. Check if the HDD status shows healthy.', 'safe', 0, NULL, NULL),
-- 5503: Recording not enabled → enable it
(16, 5501, 5502, NULL, 'Enable recording for this camera channel.', 'Go to Recording settings for the camera. Enable continuous recording or set up a motion-triggered schedule. Save changes and verify recording indicator appears.', 'safe', 1, 'solved', 'Recording was disabled for this camera channel. Enabled continuous recording and set up schedule. Camera is now recording and footage is accessible in playback.'),
-- 5504: Has storage → check playback
(16, 5502, NULL, NULL, 'Storage is available. Check playback to verify recordings exist.', 'Go to Playback/Export in NVR software. Select the camera and a recent time range. Check if video footage exists and plays back correctly.', 'safe', 1, 'solved', 'Storage has space and camera is configured for recording. Verified footage exists in playback. Recording pipeline is working end-to-end.'),
-- 5505: Storage full → fix
(16, 5502, 5504, NULL, 'Storage is full or drive is failing. Fix the storage issue.', 'If storage is full: enable auto-overwrite (oldest recordings deleted first). If drive shows errors: replace the NVR hard drive with a surveillance-grade HDD.', 'caution', 1, 'solved', 'NVR storage was full and stopped recording. Enabled auto-overwrite mode. Old recordings are now being replaced with new footage.');

-- ============================================================
-- Issue 17: DNS Issues
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(17, NULL, 6001, 6002, 'Can you ping an IP address directly? Type: ping 8.8.8.8', 'Open Command Prompt (Win+R, type cmd). Type: ping 8.8.8.8 and press Enter. Do you get reply messages or "Request timed out"?', 'safe', 0, NULL, NULL),
-- 6001: IP ping works → test domain
(17, 6000, 6003, 6004, 'Can you ping a domain name? Type: ping google.com', 'In the same Command Prompt, type: ping google.com and press Enter. Does it resolve to an IP and get replies?', 'safe', 0, NULL, NULL),
-- 6002: IP ping fails → network issue
(17, 6000, NULL, NULL, 'Cannot ping IP addresses. This is a network connectivity issue, not DNS.', 'Check your network connection: run ipconfig /all to verify IP address, subnet mask, and default gateway. Fix network connectivity first before addressing DNS.', 'safe', 1, 'escalation', 'Cannot reach any IP address on the network. This indicates a network connectivity problem (not DNS). Check network adapter, cables, and IP configuration. Escalate if hardware issue suspected.'),
-- 6003: Domain ping works → browser issue
(17, 6001, NULL, NULL, 'DNS is working correctly. Issue is browser-specific.', 'Try a different browser. Clear browser cache and DNS cache. Check browser proxy settings (should be disabled unless you use a proxy).', 'safe', 1, 'solved', 'DNS resolution is working correctly (IP and domain pings succeed). Browser-specific issue resolved by clearing cache or switching browsers.'),
-- 6004: Domain ping fails → DNS issue confirmed
(17, 6001, 6005, 6006, 'DNS resolution is failing. Flush the DNS cache.', 'Run as Administrator: ipconfig /flushdns. This clears any cached DNS entries. Then try: ping google.com again.', 'safe', 0, NULL, NULL),
-- 6005: Flush done → test
(17, 6004, 6003, 6006, 'Did flushing DNS cache fix it? Test again.', 'Try: ping google.com again. If it works, the DNS cache was corrupted. If still failing, change DNS servers.', 'safe', 0, NULL, NULL),
-- 6006: Still failing → change DNS
(17, 6005, 6003, NULL, 'Change DNS server to Google DNS (8.8.8.8).', 'Open Network Settings > Change adapter options > right-click adapter > Properties > IPv4 > Properties. Set DNS to 8.8.8.8 (preferred) and 8.8.4.4 (alternate).', 'caution', 1, 'solved', 'ISP DNS server was unresponsive. Switched to Google Public DNS (8.8.8.8). Domain resolution now works correctly.');

-- ============================================================
-- Issue 18: Flickering Display
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(18, NULL, 6501, 6502, 'Is the display cable securely connected?', 'Check the video cable (HDMI, DisplayPort, VGA, or DVI) at both ends — computer and monitor. Ensure it is firmly seated. Reseat if loose.', 'safe', 0, NULL, NULL),
-- 6501: Cable OK → test resolutions
(18, 6500, 6503, 6504, 'Does the flickering happen at all resolutions and refresh rates?', 'Right-click desktop > Display settings > Advanced display. Try different refresh rates (60Hz, 75Hz, 144Hz) and resolutions. Note which ones flicker.', 'safe', 0, NULL, NULL),
-- 6502: Cable loose → reseat/replace
(18, 6500, 6503, NULL, 'Cable was loose. Reseat or replace the display cable.', 'Unplug and firmly reconnect the cable at both ends. If flickering persists, try a different cable of the same type (e.g., another HDMI cable).', 'safe', 1, 'solved', 'Display cable was not properly seated. Reseated the cable at both ends. Display flickering resolved. If flickering returns, replace the cable.'),
-- 6503: Flickers at all resolutions → update driver
(18, 6501, 6505, 6506, 'Update the graphics driver.', 'Open Device Manager > Display adapters. Right-click your GPU > Update driver > Search automatically. Or download the latest driver from NVIDIA/AMD/Intel website.', 'caution', 0, NULL, NULL),
-- 6504: Only at certain resolutions → set optimal
(18, 6501, 6505, NULL, 'Flickering only at specific resolutions. Set the optimal resolution and refresh rate.', 'Use the recommended resolution and refresh rate in Display settings. Check the monitor specifications for supported resolutions. Do not overclock the refresh rate.', 'safe', 1, 'solved', 'Display was set to an unsupported resolution/refresh rate. Changed to the monitor\'s native resolution and supported refresh rate. Flickering stopped.'),
-- 6505: Driver updated → test with external monitor
(18, 6503, 6507, 6508, 'Test with a different monitor or connect your monitor to another computer.', 'Connect an external monitor to rule out monitor failure. Or connect your monitor to a different computer to test.', 'caution', 0, NULL, NULL),
-- 6506: Driver update didn't help → clean install
(18, 6503, 6505, NULL, 'Perform a clean GPU driver install with DDU.', 'Download DDU (Display Driver Uninstaller). Boot into Safe Mode. Run DDU to completely remove old drivers. Restart and install the latest fresh driver.', 'high', 0, NULL, NULL),
-- 6507: External monitor works → original monitor failing
(18, 6505, NULL, NULL, 'External monitor works fine. Original monitor may be failing.', 'Test the original monitor on another computer. If it flickers there too, the monitor needs repair or replacement. Check warranty status.', 'caution', 1, 'hardware', 'Original monitor flickers on multiple computers. The monitor hardware is failing (possibly bad capacitors or loose internal ribbon cable). Needs repair or replacement.'),
-- 6508: Both monitors flicker → GPU issue
(18, 6505, NULL, NULL, 'Both monitors flicker. Graphics card may be failing.', 'Check GPU temperature and fan operation under load. Run a GPU stress test. Try installing a spare GPU if available. If GPU is integrated, the motherboard may need replacement.', 'high', 1, 'hardware', 'Flickering occurs on all connected displays. Graphics card or GPU chip is failing. Needs replacement. If using integrated graphics, motherboard replacement may be required.');

-- ============================================================
-- Issue 19: BIOS Issues
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(19, NULL, 7001, 7002, 'Can you access the BIOS setup? (Press DEL, F2, or F12 during boot)', 'Restart the computer. As soon as it starts, repeatedly press the BIOS key (DEL, F2, F12, or ESC — varies by manufacturer). The key is shown briefly on the splash screen.', 'caution', 0, NULL, NULL),
-- 7001: BIOS accessible → what's the issue
(19, 7000, 7003, NULL, 'BIOS is accessible. What issue are you experiencing?', 'Common BIOS issues: wrong boot order, system time resets, settings not saving, need to update BIOS, or BIOS password lockout.', 'safe', 0, NULL, NULL),
-- 7002: Cannot access → clear CMOS
(19, 7000, 7000, 7003, 'Cannot access BIOS. Clear CMOS to reset settings.', 'Power off and unplug. Locate the CMOS clear jumper on the motherboard (consult manual) and move it to clear position for 10 seconds. Or remove the CR2032 battery for 5 minutes. Reconnect and restart.', 'high', 0, NULL, NULL),
-- 7003: Boot order wrong → fix
(19, 7001, NULL, NULL, 'Boot order is incorrect. Change the boot priority.', 'In BIOS, navigate to Boot tab. Set your primary drive (SSD/HDD with Windows) as first priority. Remove USB from boot list if not needed. Save and Exit (usually F10).', 'safe', 1, 'solved', 'BIOS boot order was incorrect, causing boot failures or slow startup. Corrected boot priority to boot from the primary SSD/HDD first. System now boots normally.'),
-- 7004: Need BIOS update
(19, 7001, 7003, NULL, 'BIOS needs updating. Update to the latest version.', 'Download the latest BIOS from your motherboard manufacturer website. Copy to USB drive. In BIOS, use the built-in update utility (EZ Flash, Q-Flash, etc.). Follow on-screen instructions exactly.', 'high', 1, 'solved', 'BIOS was outdated causing hardware compatibility or stability issues. Updated to latest version from manufacturer. System now recognizes all hardware correctly and is more stable.');

-- ============================================================
-- Issue 20: No Display AND No Power
-- ============================================================
INSERT INTO decision_nodes (issue_id, parent_id, yes_next, no_next, question, description, risk, is_terminal, result_type, result_solution) VALUES
-- Root
(20, NULL, 7501, 7502, 'Is the power cable connected to both the wall outlet and the computer?', 'Check both ends of the power cable. Ensure it is firmly plugged into the wall outlet and into the back of the computer PSU (power supply unit).', 'safe', 0, NULL, NULL),
-- 7501: Cable connected → check for signs of life
(20, 7500, 7503, 7504, 'Press the power button. Do any LEDs light up or fans spin?', 'Press and hold the power button for 3 seconds. Look for: front panel LEDs, fan noise, hard drive spin sounds, or any activity at all.', 'safe', 0, NULL, NULL),
-- 7502: Cable not connected → connect it
(20, 7500, 7501, NULL, 'Power cable was not connected. Connect it firmly.', 'Plug the power cable into a known-working wall outlet. Plug the other end firmly into the PSU on the back of the computer.', 'safe', 0, NULL, NULL),
-- 7503: No signs of life → check PSU switch
(20, 7501, 7505, 7506, 'Is the PSU power switch turned ON?', 'Look at the back of the computer. The PSU has a small switch. The "|" position means ON. The "O" position means OFF. Ensure it is ON.', 'safe', 0, NULL, NULL),
-- 7504: Some signs of life but no display → minimal hardware
(20, 7501, 7507, NULL, 'Fans spin but still no display. Test with minimal hardware.', 'Disconnect all non-essential devices: extra drives, USB devices, expansion cards. Leave only: CPU, 1 RAM stick, and GPU (if separate). Try to POST.', 'caution', 0, NULL, NULL),
-- 7505: PSU switch was OFF → turn ON
(20, 7503, 7501, 7506, 'PSU switch was OFF. Turn it ON and try again.', 'Flip the PSU switch to the "|" (ON) position. Then press the power button again.', 'safe', 0, NULL, NULL),
-- 7506: PSU switch ON but still dead → test PSU
(20, 7503, 7508, 7509, 'Test the PSU with a PSU tester or multimeter.', 'Use a PSU tester to verify all voltage rails (12V: 11.4-12.6V, 5V: 4.75-5.25V, 3.3V: 3.14-3.47V). Or do a paperclip test: bridge green wire to any black wire on the 24-pin connector.', 'high', 0, NULL, NULL),
-- 7507: Minimal config → reseat RAM/CPU
(20, 7504, 7504, 7509, 'Reseat the RAM and check the CPU.', 'Remove RAM stick(s) and firmly reinsert until the clips click. If comfortable, remove and recheck CPU alignment in the socket.', 'high', 0, NULL, NULL),
-- 7508: PSU works → motherboard issue
(20, 7506, NULL, NULL, 'PSU is working but system is dead. Motherboard may be faulty.', 'Inspect the motherboard for visible damage: burnt components, bulging capacitors, corroded traces. Check that all standoff screws are in the correct positions.', 'high', 1, 'hardware', 'PSU tested and confirmed working. Motherboard shows signs of failure (no POST, no power delivery to components). Motherboard needs professional diagnosis or replacement.'),
-- 7509: Need professional help → escalate
(20, 7506, NULL, NULL, 'System requires professional repair.', 'Document all troubleshooting steps taken. Report findings to supervisor. Send the system to the repair facility with a full diagnostic report.', 'high', 1, 'escalation', 'Complete system failure: no power and no display after all basic troubleshooting. PSU and motherboard suspected. System requires professional hardware diagnosis. Escalated to repair facility.');
