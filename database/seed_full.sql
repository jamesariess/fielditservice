USE fieldit_hub;
SET FOREIGN_KEY_CHECKS=0;

-- ===== MANUFACTURERS =====
REPLACE INTO manufacturers (id, name, website) VALUES
(1, 'Dell', 'https://www.dell.com'),
(2, 'Lenovo', 'https://www.lenovo.com'),
(3, 'HP', 'https://www.hp.com'),
(4, 'Hikvision', 'https://www.hikvision.com'),
(5, 'Cisco', 'https://www.cisco.com'),
(6, 'Epson', 'https://www.epson.com'),
(7, 'Samsung', 'https://www.samsung.com'),
(8, 'Canon', 'https://www.canon.com'),
(9, 'Ubiquiti', 'https://www.ui.com'),
(10, 'TP-Link', 'https://www.tp-link.com');

-- ===== DEVICE TYPES =====
REPLACE INTO device_types (id, name, icon) VALUES
(1, 'Desktop', 'desktop'),
(2, 'Laptop', 'laptop'),
(3, 'Server', 'server'),
(4, 'Monitor', 'monitor'),
(5, 'Printer', 'printer'),
(6, 'Router', 'router'),
(7, 'Switch', 'switch'),
(8, 'Access Point', 'wifi'),
(9, 'Camera', 'camera'),
(10, 'DVR/NVR', 'hard-drive'),
(11, 'UPS', 'battery-charging'),
(12, 'Scanner', 'scan');

-- ===== DEVICE MODELS =====
REPLACE INTO device_models (id, manufacturer_id, device_type_id, name, known_issues, common_failures, required_tools) VALUES
(1, 1, 1, 'OptiPlex 7090', 'RAM compatibility issues, Fan noise', 'RAM failure, PSU degradation', 'Phillips screwdriver, ESD strap'),
(2, 2, 2, 'ThinkPad T14 Gen 3', 'WiFi disconnects, Thermal throttling', 'WiFi card failure, Thermal paste dried', 'Phillips screwdriver, Thermal paste'),
(3, 3, 5, 'LaserJet Pro M404dn', 'Paper jams, Toner low errors', 'Fuser unit wear, Pickup roller worn', 'Pickup roller kit'),
(4, 1, 2, 'Latitude 5520', 'Battery drain, Screen flicker', 'Battery failure, Display cable wear', 'Screwdriver set, Spudger'),
(5, 3, 2, 'ProBook 450 G9', 'Slow performance, WiFi issues', 'HDD bottleneck, WiFi card', 'SSD, Screwdriver'),
(6, 2, 1, 'ThinkCentre M70s', 'No audio, USB port failure', 'Audio chip failure, USB controller', 'Multimeter'),
(7, 4, 9, 'DS-2CD2143G2-I', 'PoE not working, Night vision fails', 'IR LED failure, Network interface', 'Crimping tool, Cable tester'),
(8, 4, 10, 'DS-7608NI-K2', 'HDD not detected, Recording gaps', 'HDD failure, Firmware bug', 'SATA cable, Screwdriver');

-- ===== TIPS (more data) =====
INSERT IGNORE INTO tips (category, title, content, author_id, is_featured) VALUES
('hardware','Check thermal paste annually','Dried thermal paste causes overheating. Replace every 12-18 months.',1,1),
('network','Document IP allocations','Keep a record of DHCP reservations and static IPs to avoid conflicts.',1,1),
('printer','Clean print heads monthly','Prevents ink buildup and print quality degradation.',1,0),
('software','Create restore points before updates','Windows Updates can break drivers. Always create a restore point first.',1,1),
('hardware','Label all cables and ports','Proper labeling saves hours of troubleshooting in server rooms.',1,0),
('network','Test before and after changes','Always verify connectivity before and after network changes.',1,0),
('printer','Use OEM supplies when possible','Third-party toner can damage fuser units and reduce print quality.',1,0),
('software','Keep drivers updated monthly','Outdated drivers cause most BSOD errors and application crashes.',1,1),
('cctv','Check PoE budget on switches','Overloading PoE switches causes cameras to drop offline intermittently.',1,0),
('hardware','Carry spare cables and adapters','HDMI, DisplayPort, USB, and Ethernet cables are the most common failure points.',1,1),
('network','Use cable labels with QR codes','Modern labeling systems allow quick scanning for documentation.',1,0),
('software','Document all custom configurations','Keep records of BIOS settings, static IPs, and custom software configs.',1,0);

-- ===== KNOWLEDGE ARTICLES (add more) =====
REPLACE INTO knowledge_articles (id, title, category, issue, symptoms, root_cause, solution, tools_used, commands_used, author_id, status, quality_score, success_count, use_count) VALUES
(11, 'BSOD IRQL NOT LESS OR EQUAL','Software','bsod','Blue screen IRQL error, System crash during boot','Faulty RAM or incompatible driver','Run Windows Memory Diagnostic, Update or rollback drivers','RAM diagnostic USB','mdsched.exe, sfc /scannow',1,'published',88.00,18,22),
(12, 'Network Printer Queue Stuck','Printer','printer-offline','Print jobs stuck in queue, Cannot delete jobs','Print spooler corrupted','Restart spooler service, Clear queue manually','','net stop spooler, net start spooler',1,'published',91.00,32,38),
(13, 'VPN Disconnects Every 10 Minutes','Network','no-internet','VPN drops repeatedly, Cannot maintain connection','NAT timeout, DNS issue, Firewall blocking','Increase NAT timeout, Check firewall rules','','tracert, nslookup',2,'published',78.00,12,15),
(14, 'Monitor Flickering at 144Hz','Hardware','flickering-display','Display flickers at high refresh rate, Works fine at 60Hz','Cable bandwidth limitation, Driver issue','Use DisplayPort cable, Update GPU driver','','',1,'published',85.00,8,10),
(15, 'Laptop Battery Not Charging','Hardware','no-power','Battery stuck at 0%, Shows plugged in not charging','Battery calibration issue, Driver fault','Calibrate battery, Update BIOS, Check power adapter','','powercfg /batteryreport',1,'published',82.00,25,30);

-- ===== CHAT CONVERSATIONS & MESSAGES (more realistic) =====
REPLACE INTO chat_conversations (id, type, name, created_by) VALUES
(4, 'group', 'Escalations', 1),
(5, 'direct', 'Juan D.', 3);

INSERT INTO chat_messages (conversation_id, user_id, content, created_at) VALUES
(1, 1, 'Good morning team! Check your assigned tickets today.', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 2, 'Starting with the printer issue at reception. Will update soon.', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(1, 3, 'Maria here. Floor 3 network is slow again. Anyone else seeing this?', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(1, 2, 'Yes Maria, I had the same report from Finance. Looking into it now.', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(1, 1, 'Good catch. Let me know if you need access to the switch room.', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1, 2, 'Working on the no display issue in finance. Reseating the RAM now.', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 1, 'Thanks for the update Juan. Let me know if you need help.', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 3, 'Has anyone dealt with the new Epson printers? Paper jam issue that won t clear from the front.', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 2, 'Yes! Open the rear panel and pull paper from the back. The front path is a red herring on those models.', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(1, 3, 'That worked! Thanks a lot Juan.', DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(1, 4, 'Hey team, I m new here. Carlo from Finance. Nice to meet everyone!', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(1, 1, 'Welcome aboard Carlo! Don t hesitate to ask if you need help.', NOW()),
(2, 3, 'Hi Juan, can you check the server room temperature? The AC seems off.', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 2, 'Sure thing Maria. I ll check it after lunch.', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 3, 'Thanks! The monitoring dashboard shows 32C which is way too high.', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(4, 1, 'New escalation: Multiple WiFi disconnections on Floor 2. Priority high.', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 3, 'I can take this one. It might be the access point near the conference room.', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(4, 1, 'Good. Let me know if you need the spare AP from storage.', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(5, 3, 'Hey Juan, thanks for helping with the server room temp issue earlier.', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 2, 'No problem Maria! The AC filter was clogged. Cleaned it and temp dropped to 22C.', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- ===== TICKET NOTES (add resolution details) =====
-- ticket_notes skipped - FK references tickets table which may have different IDs

-- ===== FAVORITES =====
INSERT IGNORE INTO favorites (user_id, item_type, item_id, created_at) VALUES
(1, 'knowledge', 1, NOW()),
(1, 'knowledge', 2, NOW()),
(2, 'knowledge', 3, NOW()),
(2, 'command', 1, NOW()),
(3, 'knowledge', 1, NOW()),
(3, 'tool', 2, NOW());

-- ===== NOTIFICATIONS =====
INSERT IGNORE INTO notifications (user_id, type, title, message, is_read, created_at) VALUES
(1, 'ticket', 'New Ticket Created', 'TK-1006 has been created by Carlo Reyes', 0, NOW()),
(1, 'ticket', 'Ticket Escalated', 'TK-1003 has been escalated to supervisor', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'ticket', 'Ticket Assigned', 'You have been assigned to TK-1002', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'ticket', 'Ticket Resolved', 'TK-1001 has been marked as resolved', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'escalation', 'Escalation Received', 'TK-1003 has been escalated for your review', 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ===== AUDIT LOGS =====
INSERT IGNORE INTO audit_logs (user_id, action, resource_type, resource_id, details, ip_address, created_at) VALUES
(1, 'LOGIN', 'auth', NULL, '{"method":"password"}', '127.0.0.1', NOW()),
(2, 'LOGIN', 'auth', NULL, '{"method":"password"}', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'CREATE', 'ticket', 6, '{"ticket_number":"TK-1006"}', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'UPDATE', 'ticket', 1, '{"status":"solved"}', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'APPROVE', 'knowledge', 9, '{"article":"WiFi Connectivity Guide"}', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'UPDATE', 'settings', NULL, '{"ai_enabled":true}', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 'CREATE', 'ticket', 2, '{"ticket_number":"TK-1002","priority":"medium"}', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 3 DAY));

SET FOREIGN_KEY_CHECKS=1;
