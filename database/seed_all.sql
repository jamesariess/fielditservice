USE fieldit_hub;
-- ===== ROLES =====
INSERT INTO roles (id, name, description, is_system) VALUES
(1, 'Super Admin', 'Full system access', 1),
(2, 'Admin', 'Content and user management', 1),
(3, 'Supervisor', 'Department management', 1),
(4, 'Field IT', 'Troubleshoot and document', 1),
(5, 'Standard User', 'View and request support', 1);

-- ===== PERMISSIONS =====
INSERT INTO permissions (id, permission_key, module, description) VALUES
(1,'dashboard.view','dashboard','View dashboard'),
(2,'troubleshooting.view','troubleshooting','View guides'),
(3,'troubleshooting.create','troubleshooting','Create sessions'),
(4,'knowledge.view','knowledge','View KB'),
(5,'knowledge.create','knowledge','Create articles'),
(6,'knowledge.edit','knowledge','Edit articles'),
(7,'knowledge.delete','knowledge','Delete articles'),
(8,'knowledge.approve','knowledge','Approve articles'),
(9,'equipment.view','equipment','View equipment'),
(10,'equipment.manage','equipment','Manage equipment'),
(11,'commands.view','commands','View commands'),
(12,'tools.view','tools','View tools'),
(13,'tickets.view','tickets','View tickets'),
(14,'tickets.create','tickets','Create tickets'),
(15,'tickets.escalate','tickets','Escalate tickets'),
(16,'documentation.create','documentation','Create docs'),
(17,'documentation.view','documentation','View docs'),
(18,'chat.use','chat','Use chat'),
(19,'users.manage','users','Manage users'),
(20,'roles.manage','roles','Manage roles'),
(21,'departments.manage','departments','Manage departments'),
(22,'ai.use','ai','Use AI'),
(23,'ai.manage','ai','Manage AI'),
(24,'audit.view','audit','View audit logs'),
(25,'system.settings','system','System settings'),
(26,'statistics.view','statistics','View stats'),
(27,'favorites.manage','favorites','Manage favorites');

-- Role permissions
INSERT INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions;
INSERT INTO role_permissions (role_id, permission_id) SELECT 4, id FROM permissions WHERE permission_key IN ('dashboard.view','troubleshooting.view','troubleshooting.create','knowledge.view','knowledge.create','equipment.view','commands.view','tools.view','tickets.view','tickets.create','documentation.create','documentation.view','chat.use','ai.use','favorites.manage');
INSERT INTO role_permissions (role_id, permission_id) SELECT 3, id FROM permissions WHERE permission_key IN ('dashboard.view','troubleshooting.view','troubleshooting.create','knowledge.view','equipment.view','commands.view','tools.view','tickets.view','tickets.create','tickets.escalate','documentation.create','documentation.view','chat.use','ai.use','statistics.view');
INSERT INTO role_permissions (role_id, permission_id) SELECT 5, id FROM permissions WHERE permission_key IN ('dashboard.view','knowledge.view','equipment.view','commands.view','tools.view','tickets.view','tickets.create','chat.use');

-- ===== DEPARTMENTS =====
INSERT INTO departments (id, name, description) VALUES
(1,'IT Department','Information Technology'),
(2,'Finance','Finance and Accounting'),
(3,'HR','Human Resources'),
(4,'Operations','Operations'),
(5,'Marketing','Marketing and Sales'),
(6,'Security','Security and Facilities');

-- ===== CATEGORIES =====
INSERT INTO troubleshooting_categories (id,name,slug,icon,description,sort_order) VALUES
(1,'Display','display','monitor','Screen issues',1),(2,'Power','power','power','Power issues',2),
(3,'Sound','sound','volume-2','Audio issues',3),(4,'Network','network','wifi','Network issues',4),
(5,'Printer','printer','printer','Printer issues',5),(6,'CCTV','cctv','camera','Camera issues',6),
(7,'Software','software','app-window','Software issues',7),(8,'Hardware','hardware','cpu','Hardware issues',8);

-- ===== ISSUES =====
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
(12,8,'Overheating','overheating','Computer hot','high','15-30 min'),
(13,7,'Application Crash','application-crash','App stops','low','5-15 min'),
(14,5,'Paper Jam','paper-jam','Printer jam','low','5-10 min'),
(15,7,'Windows Update Fails','windows-update-fails','Update fails','medium','15-30 min'),
(16,6,'No Recording','no-recording','CCTV no record','medium','15-30 min'),
(17,4,'DNS Issues','dns-issues','DNS failing','medium','10-20 min'),
(18,1,'Flickering Display','flickering-display','Display flickering','medium','10-20 min'),
(19,8,'BIOS Issues','bios-issues','BIOS not loading','high','15-30 min'),
(20,2,'No Display and Power','no-display-and-no-power','No power+display','critical','30-60 min');

-- ===== DECISION NODES =====
INSERT INTO decision_nodes (id,issue_id,parent_id,question,description,risk,is_terminal,result_type,result_solution) VALUES
-- No Display (issue 1)
(1,1,NULL,'Is the computer powered on?','Check power LED and fans','safe',0,NULL,NULL),
(2,1,1,'Is the monitor powered on?','Check monitor power LED','safe',0,NULL,NULL),
(3,1,2,'Is the display cable secure?','Check HDMI/DP/VGA at both ends','safe',0,NULL,NULL),
(4,1,3,'Try a different display cable','Use known-good cable','safe',0,NULL,NULL),
(5,1,4,'Try a different monitor','Use known-good monitor','safe',1,'solved','Resolved by replacing cable/monitor'),
(6,1,2,'Monitor powers on no signal','Check input source','safe',1,'solved','Wrong input source selected'),
(7,1,1,'Computer is not on','Redirect to power','safe',1,'redirect','no-power'),
(8,1,3,'Cable OK still no display','Reseat RAM','caution',0,NULL,NULL),
(9,1,8,'Display works after RAM','RAM was loose','safe',1,'solved','RAM not seated properly'),
(10,1,8,'Still no display','Reseat GPU','caution',0,NULL,NULL),
(11,1,10,'Display works after GPU','GPU was loose','safe',1,'solved','GPU not properly seated'),
(12,1,10,'Still no display','GPU failure','danger',1,'hardware','GPU failure. Replace card.'),
-- No Power (issue 2)
(13,2,NULL,'Power cable connected?','Check both ends','safe',0,NULL,NULL),
(14,2,13,'Power outlet working?','Test with another device','safe',0,NULL,NULL),
(15,2,14,'Outlet works but dead','Check PSU switch','safe',0,NULL,NULL),
(16,2,15,'PSU switch on','Try different cable','safe',1,'solved','Power cable was faulty'),
(17,2,15,'Different cable did not help','Test PSU voltage','caution',0,NULL,NULL),
(18,2,17,'PSU bad voltage','Replace PSU','danger',1,'hardware','PSU faulty'),
(19,2,17,'PSU voltage OK','Check motherboard connections','caution',0,NULL,NULL),
(20,2,19,'Still dead','Motherboard dead','danger',1,'escalation','Motherboard failure'),
(21,2,13,'Cable not connected','Connect cable','safe',1,'solved','Cable was disconnected'),
(22,2,14,'Outlet dead','Try different outlet','safe',1,'solved','Outlet not working'),
-- No Sound (issue 3)
(23,3,NULL,'Volume muted or low?','Check system tray volume','safe',0,NULL,NULL),
(24,3,23,'Correct output device?','Check Sound settings','safe',1,'solved','Wrong output device'),
(25,3,23,'Volume up no sound','Test with headphones','safe',0,NULL,NULL),
(26,3,25,'Headphones work speakers dont','Check speaker connections','safe',1,'solved','Speaker cable loose'),
(27,3,25,'Neither work','Check audio driver','caution',0,NULL,NULL),
(28,3,27,'Driver has warning','Reinstall driver','caution',1,'solved','Driver corrupted'),
(29,3,27,'Driver OK','Run audio troubleshooter','safe',1,'solved','Run Windows Audio Troubleshooter'),
-- No Internet (issue 4)
(30,4,NULL,'Can you ping 8.8.8.8?','Open CMD ping 8.8.8.8','safe',0,NULL,NULL),
(31,4,30,'Ping successful','DNS issue','safe',0,NULL,NULL),
(32,4,31,'nslookup fails','Change DNS to 8.8.8.8','safe',1,'solved','DNS was wrong'),
(33,4,30,'Ping fails','Run ipconfig /all','safe',0,NULL,NULL),
(34,4,33,'No IP (169.254.x.x)','DHCP issue','safe',0,NULL,NULL),
(35,4,34,'ipconfig /renew works','DHCP was stuck','safe',1,'solved','DHCP issue resolved'),
(36,4,34,'ipconfig /renew fails','Check cable and switch','safe',1,'escalation','Network infrastructure issue'),
(37,4,33,'Has IP no internet','Check gateway ping','safe',0,NULL,NULL),
(38,4,37,'Gateway ping fails','Local network issue','safe',1,'escalation','Local network problem'),
(39,4,37,'Gateway ping works','ISP issue','safe',1,'escalation','Escalate to ISP'),
-- WiFi (issue 5)
(40,5,NULL,'WiFi adapter enabled?','Check settings or switch','safe',0,NULL,NULL),
(41,5,40,'Adapter enabled','Forget and reconnect','safe',1,'solved','Re-enter password'),
(42,5,40,'Adapter disabled','Enable or reinstall driver','caution',1,'solved','Enable WiFi adapter'),
-- Printer Offline (issue 6)
(43,6,NULL,'Printer powered on?','Check power and display','safe',0,NULL,NULL),
(44,6,43,'Powered on','Check connection','safe',0,NULL,NULL),
(45,6,44,'USB connected','Reinstall driver','caution',1,'solved','Driver issue'),
(46,6,44,'Network printer','Ping printer IP','safe',0,NULL,NULL),
(47,6,46,'Cannot ping','Check cable and switch','safe',1,'escalation','Network problem'),
(48,6,46,'Can ping','Restart print spooler','safe',1,'solved','net stop spooler && net start spooler'),
(49,6,43,'Not powered on','Check power cable','safe',1,'solved','Power cable disconnected'),
-- Camera Offline (issue 7)
(50,7,NULL,'Camera powered on?','Check PoE or adapter','safe',0,NULL,NULL),
(51,7,50,'Powered on','Check network cable','safe',0,NULL,NULL),
(52,7,51,'Cable connected','Ping camera IP','safe',0,NULL,NULL),
(53,7,52,'Cannot ping','Check PoE switch','safe',1,'escalation','Camera network issue'),
(54,7,52,'Can ping','Check NVR settings','caution',1,'solved','Check channel assignment'),
(55,7,50,'Not powered on','Check PoE switch','safe',1,'solved','PoE port may be off'),
-- BSOD (issue 8)
(56,8,NULL,'Note the STOP code','Write error code','safe',0,NULL,NULL),
(57,8,56,'Common codes','Boot Safe Mode','caution',0,NULL,NULL),
(58,8,57,'Boots Safe Mode','Run sfc and DISM','caution',1,'solved','System files corrupted'),
(59,8,57,'BSOD in Safe Mode','Check recent changes','caution',0,NULL,NULL),
(60,8,59,'Recent driver update','Roll back driver','safe',1,'solved','Driver caused BSOD'),
(61,8,59,'No changes','Hardware test','danger',1,'escalation','Hardware failure likely'),
-- Slow (issue 9)
(62,9,NULL,'Check Task Manager','CPU/RAM/Disk at 100%?','safe',0,NULL,NULL),
(63,9,62,'CPU at 100%','Find bad process','safe',0,NULL,NULL),
(64,9,63,'Found process','End and scan','safe',1,'solved','Malware or bad process'),
(65,9,63,'No obvious process','Disable startup apps','safe',1,'solved','Too many startup programs'),
(66,9,62,'Disk at 100%','Check disk health','caution',0,NULL,NULL),
(67,9,66,'HDD not SSD','Upgrade to SSD','safe',1,'hardware','HDD bottleneck'),
(68,9,66,'Already SSD','Disable search/superfetch','safe',1,'solved','Disable services'),
(69,9,62,'RAM at 100%','Add RAM or close apps','safe',1,'solved','Insufficient RAM'),
-- Network Slow (issue 10)
(70,10,NULL,'Run speed test','Test bandwidth','safe',0,NULL,NULL),
(71,10,70,'Speed low','Check bandwidth hogs','safe',0,NULL,NULL),
(72,10,71,'Found hogs','Limit them','safe',1,'solved','Background downloads'),
(73,10,71,'No hogs','Check switch/cable','safe',1,'escalation','Switch issue'),
(74,10,70,'Speed normal','Check DNS time','safe',1,'solved','DNS slow'),
-- Random Shutdowns (issue 11)
(75,11,NULL,'Check Event Viewer','Look for kernel-power','safe',0,NULL,NULL),
(76,11,75,'Overheating','Check CPU temp','danger',0,NULL,NULL),
(77,11,76,'Over 90C','Clean fans reapply paste','danger',1,'solved','Overheating resolved'),
(78,11,76,'Temp normal','Check PSU','caution',0,NULL,NULL),
(79,11,78,'PSU issue','Replace PSU','danger',1,'hardware','PSU failing'),
(80,11,78,'PSU OK','Update BIOS/drivers','safe',1,'solved','Update firmware'),
-- Overheating (issue 12)
(81,12,NULL,'Check CPU temp','Normal under 80C','safe',0,NULL,NULL),
(82,12,81,'Over 85C','Check fans','caution',0,NULL,NULL),
(83,12,82,'Fans not spinning','Replace fan','danger',1,'hardware','Fan failure'),
(84,12,82,'Fans spinning','Clean reapply paste','caution',1,'solved','Dried thermal paste'),
(85,12,81,'Temp normal','Check background apps','safe',1,'solved','CPU-hogging processes'),
-- App Crash (issue 13)
(86,13,NULL,'Crash on launch or during?','Timing','safe',0,NULL,NULL),
(87,13,86,'On launch','Reinstall','safe',1,'solved','Corrupted install'),
(88,13,86,'During use','Update app','safe',1,'solved','Bug in current version'),
-- Paper Jam (issue 14)
(89,14,NULL,'Where is paper stuck?','Open access panels','safe',0,NULL,NULL),
(90,14,89,'Front tray','Pull from front','safe',1,'solved','Remove from front'),
(91,14,89,'Deep inside','Open rear panel pull from back','safe',1,'solved','Remove from rear'),
-- Windows Update (issue 15)
(92,15,NULL,'What error code?','Note code','safe',0,NULL,NULL),
(93,15,92,'Common codes','Run troubleshooter','safe',0,NULL,NULL),
(94,15,93,'Fixed','Done','safe',1,'solved','Troubleshooter fixed it'),
(95,15,93,'Still failing','DISM and sfc','caution',1,'solved','System file repair'),
-- No Recording (issue 16)
(96,16,NULL,'Camera online?','Check live view','safe',0,NULL,NULL),
(97,16,96,'Online no recording','Check schedule','safe',1,'solved','Schedule was off'),
(98,16,96,'Offline','See Camera Offline','safe',1,'redirect','camera-offline'),
-- DNS Issues (issue 17)
(99,17,NULL,'nslookup works?','Test resolution','safe',0,NULL,NULL),
(100,17,99,'Some machines work','Primary DNS down','safe',0,NULL,NULL),
(101,17,100,'Primary down','Switch to 8.8.8.8','safe',1,'solved','DNS server issue'),
(102,17,99,'Fails everywhere','DNS server down','safe',1,'escalation','Escalate'),
-- Flickering (issue 18)
(103,18,NULL,'Cable secure?','Check display cable','safe',0,NULL,NULL),
(104,18,103,'Secure','Try different refresh rate','safe',1,'solved','Rate mismatch'),
(105,18,103,'Loose','Replace cable','safe',1,'solved','Faulty cable'),
-- BIOS (issue 19)
(106,19,NULL,'Beep codes?','Listen for beeps','safe',0,NULL,NULL),
(107,19,106,'No beeps','Check mobo power','caution',0,NULL,NULL),
(108,19,107,'Connections OK','Reseat RAM GPU','caution',1,'solved','Reseat components'),
(109,19,106,'Beep codes','Look up code','safe',1,'escalation','Specific HW failure'),
-- No Display+Power (issue 20)
(110,20,NULL,'Check all power connections','PSU mobo CPU','safe',0,NULL,NULL),
(111,20,110,'All connected','Test PSU','danger',0,NULL,NULL),
(112,20,111,'PSU dead','Replace PSU','danger',1,'hardware','PSU failed'),
(113,20,111,'PSU OK','Test mobo outside case','danger',1,'escalation','Motherboard failure');

-- ===== COMMAND CATEGORIES =====
INSERT INTO command_categories (id, name, slug) VALUES
(1,'Network','network'),(2,'System','system'),(3,'Disk','disk'),(4,'Process','process');

-- ===== COMMANDS =====
INSERT INTO commands (id,category_id,command,description,when_to_use,example,expected_output,common_errors,next_steps,risk_level,is_powershell,sort_order) VALUES
(1,1,'ipconfig','Display IP configuration','Network troubleshooting','ipconfig /all','IP address, subnet, gateway, DNS','Media disconnected = cable unplugged','Check DHCP, try ipconfig /renew','safe',0,1),
(2,1,'ipconfig /release','Release current IP','DHCP conflict','ipconfig /release','IP released 0.0.0.0','','Follow with /renew','safe',0,2),
(3,1,'ipconfig /renew','Request new IP','No IP assigned','ipconfig /renew','New IP from DHCP','169.254 = DHCP fail','Check cable and DHCP','safe',0,3),
(4,1,'ipconfig /flushdns','Clear DNS cache','DNS stale','ipconfig /flushdns','DNS cache flushed','','Test with nslookup','safe',0,4),
(5,1,'ping','Test connectivity','Verify network','ping 8.8.8.8','Reply with TTL/time','Timed out = unreachable','Follow ping path: local->gateway->internet->DNS','safe',0,5),
(6,1,'nslookup','Query DNS','DNS issues','nslookup google.com','DNS server and IP','Timed out = DNS issue','Try 8.8.8.8 as DNS','safe',0,6),
(7,1,'tracert','Trace route','Find path break','tracert google.com','List of hops','Timeout = path break','Identify problem hop','safe',0,7),
(8,2,'sfc /scannow','Scan system files','Corruption suspected','sfc /scannow (Admin)','Scan results','Could not perform','Run DISM first','caution',0,8),
(9,2,'DISM /Online /Cleanup-Image /RestoreHealth','Repair Windows image','sfc cannot fix','DISM (Admin)','Repair progress','','Then run sfc again','caution',1,9),
(10,2,'systeminfo','System information','Gather device details','systeminfo','OS, RAM, CPU, adapters','','Record for escalation','safe',0,10),
(11,3,'chkdsk','Check disk errors','Disk issues','chkdsk C: /f /r','Check stages','Volume in use','Schedule for next boot','caution',0,11),
(12,4,'taskmgr','Open Task Manager','Check resources','taskmgr','Task Manager window','','Check CPU/Memory/Disk columns','safe',0,12),
(13,4,'taskkill /PID 1234 /F','Kill process','Unresponsive app','taskkill /PID 1234 /F','Process terminated','Access denied','Run as Admin','caution',0,13),
(14,1,'Get-NetIPConfiguration','Show IP config (PS)','PowerShell IP check','Get-NetIPConfiguration','IP config details','','PowerShell alternative to ipconfig','safe',1,14),
(15,1,'Test-NetConnection','Test connectivity (PS)','PowerShell ping','Test-NetConnection google.com -Port 80','Connection test result','','More detailed than ping','safe',1,15);

-- ===== TOOLS =====
INSERT INTO tools (id, name, icon, purpose, when_to_use, how_to_use, safety, related_issues) VALUES
(1,'Phillips Screwdriver Set','screwdriver','Opening cases securing components','Hardware installation RAM/SSD','Use correct size to avoid stripping','Use correct size','No Display,Overheating'),
(2,'Anti-Static Wrist Strap','shield','Prevent ESD damage','Touching internal components','Clip to grounded metal','Always clip to grounded metal','No Display,Overheating,Random Shutdowns'),
(3,'Network Cable Tester','cable','Test Ethernet cable continuity','Network issues','Connect both ends and test','Low voltage safe','No Internet,Network Slow'),
(4,'USB Boot Drive (8GB+)','usb','Boot recovery and OS install','OS repair fresh install','Create bootable USB with Rufus','Ensure correct images','BSOD,Slow Performance'),
(5,'Thermal Paste','thermometer','Replace CPU/GPU thermal compound','Overheating after heatsink removal','Clean old paste apply thin layer','Do not apply too much','Overheating,Random Shutdowns'),
(6,'Multeter','activity','Measure voltage current resistance','PSU testing cable testing','Use appropriate voltage range','Use appropriate range','No Power,Random Shutdowns'),
(7,'Crimping Tool','scissors','Terminate Ethernet with RJ45','Custom cables replacing connectors','Use T568B standard','Use T568B standard','No Internet,Network Slow'),
(8,'Cable Labels','tag','Label cables for identification','During installation or reorganization','Print and attach labels','None','No Internet,Network Slow');

-- ===== KNOWLEDGE ARTICLES =====
INSERT INTO knowledge_articles (id,title,category,issue,symptoms,root_cause,solution,tools_used,commands_used,author_id,status,quality_score,success_count,use_count) VALUES
(1,'No Display — Desktop Troubleshooting','Hardware','no-display','Black screen, No signal, Monitor LED on, Flickering','Loose display cables, improperly seated RAM, monitor input misconfiguration','Check Power, Reseat Cable, Test Cable, Test Monitor, Reseat RAM, Reseat GPU','Known-good monitor, Cable, Screwdriver, ESD strap','systeminfo,msinfo32',1,'published',92.00,126,137),
(2,'Printer Offline — HP LaserJet','Printer','printer-offline','Printer offline, Not responding, Error LED','Network connectivity issue, driver problem, print spooler','Check power, Check connection, Restart spooler, Reinstall driver','Ethernet cable, USB cable','net stop spooler, ping',1,'published',88.00,45,52),
(3,'WiFi Connectivity Troubleshooting','Network','wifi-not-connecting','Cannot connect, Limited connectivity, Authentication failed','Adapter disabled, driver issue, signal strength','Enable adapter, Forget network, Update driver, Check signal','WiFi analyzer','netsh wlan show profile',1,'published',85.00,67,78),
(4,'RAM Reseat Procedure','Hardware','no-display','No display after transport, Random BSOD, Beep codes','RAM not properly seated in slot','Power off, Open case, Remove RAM, Reinsert firmly','Screwdriver, ESD strap','','1','published',95.00,89,95),
(5,'VPN Connection Issues','Network','no-internet','VPN timeout, Cannot connect to VPN, Slow VPN','VPN client config, network interference','Check credentials, Restart client, Check firewall','VPN client','ipconfig, ping',1,'published',82.00,34,41),
(6,'New WiFi Issue Guide','Network','wifi-not-connecting','Intermittent WiFi, Slow WiFi, Dropouts','Channel interference, distance, driver','Change channel, Move closer, Update driver','WiFi analyzer','netsh wlan',2,'submitted',0,0,0),
(7,'POS Printer Blinking Red','Printer','printer-offline','Red light, Not printing, Paper error','Paper jam, low ink, driver error','Clear jam, Replace ink, Reinstall driver','','',4,'submitted',0,0,0),
(8,'Server Room Temperature Guide','Hardware','overheating','Server overheating, Thermal shutdown','HVAC failure, dust buildup, fan failure','Check HVAC, Clean filters, Check fans','','',3,'submitted',0,0,0),
(9,'BSOD Kernel Data Inpage Error','Software','bsod','BSOD KERNEL_DATA_INPAGE_ERROR','Disk corruption, failing drive','Run chkdsk, Test drive health','SATA cable','chkdsk, sfc',1,'published',90.00,23,28),
(10,'CCTV Camera Setup Guide','CCTV','camera-offline','Camera not showing, No live view','Network config, IP conflict, PoE issue','Check PoE, Verify IP, Configure NVR','','',1,'published',87.00,15,19);

-- ===== TIPS =====
INSERT INTO tips (category, title, content, author_id, is_featured) VALUES
('hardware','Always use ESD protection','ESD straps prevent static damage to components',1,1),
('network','Document IP allocations','Keep a record of DHCP reservations and static IPs',1,1),
('printer','Clean print heads monthly','Prevents ink buildup and print quality issues',1,1),
('software','Keep drivers updated','Outdated drivers cause most BSOD and crashes',1,1),
('hardware','Label everything','Proper cable labels save hours of troubleshooting',1,0),
('network','Test before and after','Always test connectivity before and after changes',1,0),
('printer','Use OEM supplies','Third party supplies can damage printers',1,0),
('software','Create restore points','Before major changes always create a restore point',1,0);

-- ===== TROUBLESHOOTING SESSIONS (Tickets) =====
INSERT INTO troubleshooting_sessions (id,ticket_number,user_id,issue_id,customer_name,department,location,device_type,manufacturer,model,priority,status,problem_description,created_at) VALUES
(1,'TK-1001',2,1,'Finance Department','Finance','Floor 3, Desk 42','Desktop','Dell','OptiPlex 7090','high','solved','Computer not showing display after moving desk',DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2,'TK-1002',2,10,'HR Department','HR','Floor 3, Room 301','Laptop','HP','ProBook 450','medium','in_progress','Very slow network speed on floor 3',DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3,'TK-1003',2,6,'Reception','Reception','Ground Floor','Printer','HP','LaserJet Pro M404','high','escalated','Printer offline not responding to jobs',DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4,'TK-1004',2,3,'Meeting Room A','Operations','Floor 2','Desktop','Lenovo','ThinkCentre M70s','medium','solved','No sound from speakers',DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5,'TK-1005',2,5,'Sales Area','Sales','Floor 1','Laptop','Dell','Latitude 5520','low','in_progress','WiFi not connecting to office network',NOW());

-- ===== CHAT =====
INSERT INTO chat_conversations (id, type, name, created_by) VALUES
(1,'group','Field IT Team',1),(2,'direct','Maria S.',2),(3,'group','Support Channel',1);

INSERT INTO chat_messages (conversation_id, user_id, content, created_at) VALUES
(1,1,'Good morning team! Check your tickets today.',DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(1,2,'Starting with the printer issue at reception.',DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1,2,'Working on no display issue in finance. Reseating RAM.',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1,1,'Thanks for the update. Let me know if you need help.',DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1,3,'Has anyone dealt with the new Epson printers? Paper jam that wont clear.',DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1,2,'Yes, open the rear panel and pull paper from the back.',NOW()),
(2,3,'Thanks for the help!',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2,2,'No problem!',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3,1,'New ticket assigned to you.',DATE_SUB(NOW(), INTERVAL 1 HOUR));
