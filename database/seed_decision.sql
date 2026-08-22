USE fieldit_hub;
SET FOREIGN_KEY_CHECKS=0;

-- Categories
INSERT INTO troubleshooting_categories (id, name, slug, icon, description, sort_order) VALUES
(1,'Display','display','monitor','Screen issues',1),(2,'Power','power','power','Power issues',2),
(3,'Sound','sound','volume-2','Audio issues',3),(4,'Network','network','wifi','Network issues',4),
(5,'Printer','printer','printer','Printer issues',5),(6,'CCTV','cctv','camera','Camera issues',6),
(7,'Software','software','app-window','Software issues',7),(8,'Hardware','hardware','cpu','Hardware issues',8);

-- Issues
INSERT INTO troubleshooting_issues (id,category_id,title,slug,description,severity,estimated_time) VALUES
(1,1,'No Display','no-display','No display output','high','15-30 min'),
(2,2,'No Power','no-power','Computer dead','critical','20-40 min'),
(3,3,'No Sound','no-sound','No audio output','medium','10-20 min'),
(4,4,'No Internet','no-internet','Cannot access internet','high','15-30 min'),
(5,4,'WiFi Not Connecting','wifi-not-connecting','WiFi not working','medium','10-20 min'),
(6,5,'Printer Offline','printer-offline','Printer offline','medium','10-25 min'),
(7,6,'Camera Offline','camera-offline','Camera not showing','medium','15-30 min'),
(8,7,'Blue Screen','bsod','Windows BSOD','critical','20-60 min'),
(9,7,'Slow Performance','slow-performance','Computer slow','medium','15-30 min'),
(10,4,'Network Slow','network-slow','Slow network','medium','10-20 min'),
(11,8,'Random Shutdowns','random-shutdowns','Unexpected shutdowns','high','20-40 min'),
(12,8,'Overheating','overheating','Computer running hot','high','15-30 min'),
(13,7,'Application Crash','application-crash','App stops working','low','5-15 min'),
(14,5,'Paper Jam','paper-jam','Printer paper jam','low','5-10 min'),
(15,7,'Windows Update Fails','windows-update-fails','Update not completing','medium','15-30 min'),
(16,6,'No Recording','no-recording','CCTV not recording','medium','15-30 min'),
(17,4,'DNS Issues','dns-issues','DNS failing','medium','10-20 min'),
(18,1,'Flickering Display','flickering-display','Display flickering','medium','10-20 min'),
(19,8,'BIOS Issues','bios-issues','BIOS not loading','high','15-30 min'),
(20,2,'No Display and Power','no-display-and-no-power','No power and display','critical','30-60 min');

-- Decision Nodes
INSERT INTO decision_nodes (id,issue_id,parent_id,question,description,risk,is_terminal,result_type,result_solution) VALUES
-- No Display
(1,1,NULL,'Is the computer powered on?','Check power LED and fans','safe',0,NULL,NULL),
(2,1,1,'Is the monitor powered on?','Check monitor power LED','safe',0,NULL,NULL),
(3,1,2,'Is the display cable secure?','Check HDMI/DP/VGA at both ends','safe',0,NULL,NULL),
(4,1,3,'Try a different display cable','Use known-good cable','safe',0,NULL,NULL),
(5,1,4,'Try a different monitor','Use known-good monitor','safe',1,'solved','Resolved by replacing cable/monitor'),
(6,1,2,'Monitor powers on no signal','Check input source','safe',1,'solved','Wrong input source. Switch to correct input.'),
(7,1,1,'Computer is not on','Redirect to power','safe',1,'redirect','no-power'),
(8,1,3,'Cable OK still no display','Reseat RAM','caution',0,NULL,NULL),
(9,1,8,'Display works after RAM','RAM was loose','safe',1,'solved','RAM not seated. Click firmly into slot.'),
(10,1,8,'Still no display','Reseat GPU','caution',0,NULL,NULL),
(11,1,10,'Display works after GPU','GPU was loose','safe',1,'solved','GPU not properly seated in PCIe slot.'),
(12,1,10,'Still no display','GPU failure - escalate','danger',1,'hardware','GPU failure. Replace card or escalate.'),
-- No Power
(13,2,NULL,'Power cable connected?','Check both ends','safe',0,NULL,NULL),
(14,2,13,'Power outlet working?','Test with another device','safe',0,NULL,NULL),
(15,2,14,'Outlet works but dead','Check PSU switch','safe',0,NULL,NULL),
(16,2,15,'PSU switch is on','Try different cable','safe',1,'solved','Power cable was faulty. Replace it.'),
(17,2,15,'Different cable did not help','Test PSU voltage','caution',0,NULL,NULL),
(18,2,17,'PSU bad voltage','Replace PSU','danger',1,'hardware','PSU faulty. Replace power supply.'),
(19,2,17,'PSU voltage OK','Check motherboard connections','caution',0,NULL,NULL),
(20,2,19,'Still dead','Motherboard dead - escalate','danger',1,'escalation','Motherboard failure. Escalate.'),
(21,2,13,'Cable not connected','Connect cable','safe',1,'solved','Cable disconnected. Reconnect.'),
(22,2,14,'Outlet dead','Try different outlet','safe',1,'solved','Outlet not working. Switch outlet.'),
-- No Sound
(23,3,NULL,'Volume muted or low?','Check system tray volume','safe',0,NULL,NULL),
(24,3,23,'Correct output device?','Check Sound settings','safe',1,'solved','Wrong device. Switch to correct output.'),
(25,3,23,'Volume up no sound','Test with headphones','safe',0,NULL,NULL),
(26,3,25,'Headphones work speakers dont','Check speaker connections','safe',1,'solved','Speaker cable loose. Reconnect.'),
(27,3,25,'Neither work','Check audio driver','caution',0,NULL,NULL),
(28,3,27,'Driver has warning','Reinstall driver','caution',1,'solved','Driver corrupted. Reinstall.'),
(29,3,27,'Driver OK','Run audio troubleshooter','safe',1,'solved','Run Windows Audio Troubleshooter.'),
-- No Internet
(30,4,NULL,'Can you ping 8.8.8.8?','Open CMD, type ping 8.8.8.8','safe',0,NULL,NULL),
(31,4,30,'Ping successful','DNS issue - check nslookup','safe',0,NULL,NULL),
(32,4,31,'nslookup fails','Change DNS to 8.8.8.8','safe',1,'solved','DNS was wrong. Set DNS to 8.8.8.8.'),
(33,4,30,'Ping fails','Run ipconfig /all','safe',0,NULL,NULL),
(34,4,33,'No IP address (169.254.x.x)','DHCP issue - renew IP','safe',0,NULL,NULL),
(35,4,34,'ipconfig /renew works','DHCP was stuck','safe',1,'solved','DHCP issue. ipconfig /release then /renew.'),
(36,4,34,'ipconfig /renew fails','Check cable and switch port','safe',1,'escalation','Network infrastructure issue. Check switch.'),
(37,4,33,'Has valid IP but no internet','Check default gateway ping','safe',0,NULL,NULL),
(38,4,37,'Gateway ping fails','Local network issue - check cable/switch','safe',1,'escalation','Local network problem. Check cable and switch.'),
(39,4,37,'Gateway ping works','Internet issue - escalate to ISP','safe',1,'escalation','ISP or upstream issue. Escalate.'),
-- WiFi Not Connecting
(40,5,NULL,'WiFi adapter enabled?','Check Network Settings or physical switch','safe',0,NULL,NULL),
(41,5,40,'Adapter enabled','Try forgetting network and reconnect','safe',1,'solved','Forget network, re-enter password.'),
(42,5,40,'Adapter disabled or missing','Enable adapter or reinstall driver','caution',1,'solved','Enable WiFi adapter in settings or reinstall driver.'),
-- Printer Offline
(43,6,NULL,'Printer powered on?','Check power and display','safe',0,NULL,NULL),
(44,6,43,'Powered on','Check connection (USB/network)','safe',0,NULL,NULL),
(45,6,44,'USB connected','Reinstall printer driver','caution',1,'solved','Driver issue. Reinstall printer driver.'),
(46,6,44,'Network printer','Check IP address and ping it','safe',0,NULL,NULL),
(47,6,46,'Cannot ping printer','Check network cable and switch port','safe',1,'escalation','Network connection problem.'),
(48,6,46,'Can ping but offline','Restart print spooler service','safe',1,'solved','Restart spooler: net stop spooler && net start spooler.'),
(49,6,43,'Not powered on','Check power cable','safe',1,'solved','Printer power cable disconnected.'),
-- Camera Offline
(50,7,NULL,'Camera powered on?','Check PoE or power adapter','safe',0,NULL,NULL),
(51,7,50,'Powered on','Check network cable to camera','safe',0,NULL,NULL),
(52,7,51,'Cable connected','Ping camera IP','safe',0,NULL,NULL),
(53,7,52,'Cannot ping','Check PoE switch or cable','safe',1,'escalation','Camera network issue.'),
(54,7,52,'Can ping','Check NVR/DVR settings','caution',1,'solved','Check NVR channel assignment and resolution.'),
(55,7,50,'Not powered on','Check PoE switch status','safe',1,'solved','PoE switch port may be off. Check switch.'),
-- BSOD
(56,8,NULL,'Note the STOP code','Write down the error code','safe',0,NULL,NULL),
(57,8,56,'Common codes (IRQL, KERNEL_DATA)','Boot into Safe Mode','caution',0,NULL,NULL),
(58,8,57,'Boots in Safe Mode','Run sfc /scannow and DISM','caution',1,'solved','System files corrupted. Run sfc /scannow then DISM.'),
(59,8,57,'BSOD in Safe Mode too','Check recent driver/hardware changes','caution',0,NULL,NULL),
(60,8,59,'Recent driver update','Roll back driver','safe',1,'solved','Recent driver caused BSOD. Roll back or uninstall.'),
(61,8,59,'No recent changes','Hardware test - RAM and disk','danger',1,'escalation','Hardware failure likely. Run memtest and chkdsk.'),
-- Slow Performance
(62,9,NULL,'Check Task Manager CPU/RAM/Disk','Which is at 100%?','safe',0,NULL,NULL),
(63,9,62,'CPU at 100%','Find and close runaway process','safe',0,NULL,NULL),
(64,9,63,'Found bad process','End process and scan for malware','safe',1,'solved','Malware or bad process. End it and run antivirus scan.'),
(65,9,63,'No obvious process','Check startup programs and disable','safe',1,'solved','Too many startup programs. Disable unnecessary ones.'),
(66,9,62,'Disk at 100%','Check disk health and fragmentation','caution',0,NULL,NULL),
(67,9,66,'HDD not SSD','Upgrade to SSD','safe',1,'hardware','HDD is bottleneck. Upgrade to SSD.'),
(68,9,66,'Already SSD','Check for Windows Search and Superfetch','safe',1,'solved','Disable Windows Search and SysMain services.'),
(69,9,62,'RAM at 100%','Add more RAM or close apps','safe',1,'solved','Insufficient RAM. Upgrade to 8GB minimum.'),
-- Network Slow
(70,10,NULL,'Run speed test','Test actual bandwidth','safe',0,NULL,NULL),
(71,10,70,'Speed much lower than expected','Check for bandwidth hogs','safe',0,NULL,NULL),
(72,10,71,'Found bandwidth hogs','Limit or block them','safe',1,'solved','Background downloads/uploads consuming bandwidth.'),
(73,10,71,'No bandwidth hogs','Check switch port and cable','safe',1,'escalation','Switch or cable issue. Replace cable or check switch.'),
(74,10,70,'Speed is normal but feels slow','Check DNS resolution time','safe',1,'solved','DNS slow. Change DNS to 8.8.8.8.'),
-- Random Shutdowns
(75,11,NULL,'Check Event Viewer for errors','Look for kernel-power events','safe',0,NULL,NULL),
(76,11,75,'Overheating shutdowns','Check CPU temperature','danger',0,NULL,NULL),
(77,11,76,'CPU over 90C','Clean fans and reapply thermal paste','danger',1,'solved','Overheating. Clean dust, reapply thermal paste.'),
(78,11,76,'Temperature normal','Check power settings and PSU','caution',0,NULL,NULL),
(79,11,78,'PSU issue suspected','Test or replace PSU','danger',1,'hardware','PSU failing. Replace.'),
(80,11,78,'PSU OK','Update BIOS and drivers','safe',1,'solved','Update BIOS and all drivers.'),
-- Overheating
(81,12,NULL,'Check CPU temp with HWMonitor','Normal is under 80C','safe',0,NULL,NULL),
(82,12,81,'Over 85C','Open case and check fans','caution',0,NULL,NULL),
(83,12,82,'Fans not spinning','Replace fan','danger',1,'hardware','Fan failure. Replace.'),
(84,12,82,'Fans spinning but hot','Clean heatsink and reapply thermal paste','caution',1,'solved','Dried thermal paste. Clean and reapply.'),
(85,12,81,'Temperature is normal','Check for background processes','safe',1,'solved','Check Task Manager for CPU-hogging processes.'),
-- Application Crash
(86,13,NULL,'Does it crash on launch or during use?','Timing of crash','safe',0,NULL,NULL),
(87,13,86,'Crashes on launch','Reinstall application','safe',1,'solved','Corrupted install. Uninstall and reinstall.'),
(88,13,86,'Crashes during use','Check for updates and patches','safe',1,'solved','Update application to latest version.'),
-- Paper Jam
(89,14,NULL,'Where is the paper stuck?','Open access panels','safe',0,NULL,NULL),
(90,14,89,'Paper in front tray','Pull gently from front','safe',1,'solved','Remove paper from front path.'),
(91,14,89,'Paper deep inside','Open rear panel and pull from back','safe',1,'solved','Remove paper from rear path. Pull firmly but gently.'),
-- Windows Update Fails
(92,15,NULL,'What is the error code?','Note the error code','safe',0,NULL,NULL),
(93,15,92,'Common codes (0x80070002)','Run Windows Update Troubleshooter','safe',0,NULL,NULL),
(94,15,93,'Troubleshooter fixed it','Issue resolved','safe',1,'solved','Troubleshooter repaired the update service.'),
(95,15,93,'Still failing','Run DISM and sfc /scannow','caution',1,'solved','System files corrupted. Repair with DISM + sfc.'),
-- No Recording
(96,16,NULL,'Is the camera online?','Check live view','safe',0,NULL,NULL),
(97,16,96,'Camera is online but no recording','Check recording schedule','safe',1,'solved','Recording schedule was off. Enable continuous recording.'),
(98,16,96,'Camera is offline','See Camera Offline troubleshooting','safe',1,'redirect','camera-offline'),
-- DNS Issues
(99,17,NULL,'nslookup google.com works?','Test DNS resolution','safe',0,NULL,NULL),
(100,17,99,'nslookup works from some machines','DNS server issue','safe',0,NULL,NULL),
(101,17,100,'Primary DNS down','Switch to secondary DNS 8.8.8.8','safe',1,'solved','Primary DNS server issue. Switch to 8.8.8.8.'),
(102,17,99,'nslookup fails everywhere','Check DNS server status','safe',1,'escalation','DNS server down. Escalate.'),
-- Flickering Display
(103,18,NULL,'Is cable securely connected?','Check display cable','safe',0,NULL,NULL),
(104,18,103,'Cable secure','Try different refresh rate','safe',1,'solved','Refresh rate mismatch. Set to 60Hz in display settings.'),
(105,18,103,'Cable loose','Replace cable','safe',1,'solved','Faulty display cable. Replace.'),
-- BIOS Issues
(106,19,NULL,'Any beep codes on boot?','Listen for beep patterns','safe',0,NULL,NULL),
(107,19,106,'No beeps at all','Check motherboard power connections','caution',0,NULL,NULL),
(108,19,107,'Connections OK','Reseat RAM and GPU','caution',1,'solved','Reseat all components. BIOS should POST.'),
(109,19,106,'Beep codes present','Look up beep code for motherboard','safe',1,'escalation','Beep code indicates specific hardware failure.'),
-- No Display and Power
(110,20,NULL,'Check all power connections','PSU, motherboard, CPU power','safe',0,NULL,NULL),
(111,20,110,'All connected','Test PSU with tester','danger',0,NULL,NULL),
(112,20,111,'PSU dead','Replace PSU','danger',1,'hardware','PSU failed. Replace.'),
(113,20,111,'PSU OK','Test motherboard outside case','danger',1,'escalation','Motherboard failure. Escalate.');

SET FOREIGN_KEY_CHECKS=1;
