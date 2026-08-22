USE fieldit_hub;

-- ===== ROLES =====
INSERT INTO roles (id, name, description, is_system) VALUES
(1, 'Super Admin', 'Full system access', TRUE),
(2, 'Admin', 'Content and user management', TRUE),
(3, 'Supervisor', 'Department management and escalation handling', TRUE),
(4, 'Field IT', 'Troubleshoot, document, and use AI', TRUE),
(5, 'Standard User', 'Search knowledge and create support requests', TRUE);

-- ===== PERMISSIONS =====
INSERT INTO permissions (permission_key, module, description) VALUES
('dashboard.view', 'dashboard', 'View dashboard'),
('troubleshooting.view', 'troubleshooting', 'View troubleshooting guides'),
('troubleshooting.create', 'troubleshooting', 'Create troubleshooting sessions'),
('knowledge.view', 'knowledge', 'View knowledge base'),
('knowledge.create', 'knowledge', 'Create knowledge articles'),
('knowledge.edit', 'knowledge', 'Edit knowledge articles'),
('knowledge.delete', 'knowledge', 'Delete knowledge articles'),
('knowledge.approve', 'knowledge', 'Approve knowledge articles'),
('knowledge.publish', 'knowledge', 'Publish knowledge articles'),
('knowledge.manage', 'knowledge', 'Manage knowledge base'),
('equipment.view', 'equipment', 'View equipment database'),
('equipment.create', 'equipment', 'Add equipment'),
('equipment.edit', 'equipment', 'Edit equipment'),
('equipment.manage', 'equipment', 'Manage equipment'),
('commands.view', 'commands', 'View commands reference'),
('tools.view', 'tools', 'View tools reference'),
('ai.use', 'ai', 'Use AI assistant'),
('ai.manage', 'ai', 'Configure AI settings'),
('tickets.view', 'tickets', 'View tickets'),
('tickets.create', 'tickets', 'Create tickets'),
('tickets.manage', 'tickets', 'Manage tickets'),
('chat.use', 'chat', 'Use team chat'),
('users.view', 'users', 'View users'),
('users.manage', 'users', 'Manage users'),
('roles.manage', 'roles', 'Manage roles and permissions'),
('departments.manage', 'departments', 'Manage departments'),
('contacts.view', 'contacts', 'View contacts'),
('contacts.manage', 'contacts', 'Manage contacts'),
('audit.view', 'audit', 'View audit logs'),
('system.settings', 'system', 'System settings'),
('documentation.create', 'documentation', 'Create documentation'),
('documentation.approve', 'documentation', 'Approve documentation'),
('escalations.view', 'escalations', 'View escalations'),
('escalations.manage', 'escalations', 'Manage escalations'),
('favorites.manage', 'favorites', 'Manage favorites');

-- ===== ROLE PERMISSIONS =====
-- Super Admin gets everything
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Admin permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_key IN (
    'dashboard.view','troubleshooting.view','troubleshooting.create',
    'knowledge.view','knowledge.create','knowledge.edit','knowledge.delete','knowledge.approve','knowledge.publish','knowledge.manage',
    'equipment.view','equipment.create','equipment.edit','equipment.manage',
    'commands.view','tools.view',
    'ai.use','ai.manage',
    'tickets.view','tickets.create','tickets.manage',
    'chat.use','users.view','users.manage','roles.manage','departments.manage',
    'contacts.view','contacts.manage','audit.view','system.settings',
    'documentation.create','documentation.approve','escalations.view','escalations.manage'
);

-- Supervisor permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_key IN (
    'dashboard.view','troubleshooting.view','troubleshooting.create',
    'knowledge.view','knowledge.create','knowledge.edit','knowledge.approve',
    'equipment.view','commands.view','tools.view',
    'ai.use','tickets.view','tickets.create','tickets.manage',
    'chat.use','users.view','contacts.view','contacts.manage',
    'documentation.create','documentation.approve','escalations.view','escalations.manage'
);

-- Field IT permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE permission_key IN (
    'dashboard.view','troubleshooting.view','troubleshooting.create',
    'knowledge.view','equipment.view','commands.view','tools.view',
    'ai.use','tickets.view','tickets.create',
    'chat.use','documentation.create','favorites.manage'
);

-- Standard User permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE permission_key IN (
    'dashboard.view','knowledge.view','equipment.view','commands.view','tools.view','chat.use'
);

-- ===== DEPARTMENTS =====
INSERT INTO departments (name, head_name, head_email, location, user_count) VALUES
('IT', 'System Admin', 'it@company.local', 'Floor 3, IT Room', 8),
('Finance', 'Maria Santos', 'finance@company.local', 'Floor 3', 24),
('HR', 'Ana Torres', 'hr@company.local', 'Floor 2', 15),
('Operations', 'Carlos Reyes', 'ops@company.local', 'Floor 1 & 2', 45),
('Sales', 'Rica Villanueva', 'sales@company.local', 'Floor 1', 32),
('Reception', 'Joy Mendoza', 'reception@company.local', 'Ground Floor', 5);

-- ===== USERS (password = 'password' for admin, bcrypt for others) =====
INSERT INTO users (full_name, email, password_hash, role_id, department_id, status) VALUES
('System Admin', 'admin@fieldit.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 'active'),
('Juan Dela Cruz', 'fieldit@fieldit.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 1, 'active'),
('Maria Santos', 'supervisor@fieldit.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2, 'active'),
('Ana Torres', 'user@fieldit.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 3, 'active');

-- ===== TROUBLESHOOTING CATEGORIES =====
INSERT INTO troubleshooting_categories (id, name, icon, color, description, sort_order) VALUES
(1, 'Display', 'monitor', 'blue', 'Monitor and display issues', 1),
(2, 'Power', 'power', 'red', 'Power and startup issues', 2),
(3, 'Sound', 'volume-2', 'purple', 'Audio and sound issues', 3),
(4, 'Network', 'wifi', 'green', 'Network connectivity issues', 4),
(5, 'Printer', 'printer', 'orange', 'Printer and print issues', 5),
(6, 'CCTV', 'camera', 'yellow', 'Camera and surveillance issues', 6),
(7, 'Software', 'settings', 'indigo', 'Software and OS issues', 7),
(8, 'Hardware', 'cpu', 'pink', 'Hardware component issues', 8);

-- ===== TROUBLESHOOTING ISSUES =====
INSERT INTO troubleshooting_issues (id, category_id, title, severity, icon, has_wizard, symptoms) VALUES
(1, 1, 'No Display', 'high', 'monitor-x', TRUE, '["Monitor shows no signal","Black screen after boot","Display cable connected but nothing shown"]'),
(2, 1, 'Flickering Display', 'medium', 'zap', TRUE, '["Screen flashes intermittently","Horizontal lines appear","Display cuts in and out"]'),
(3, 2, 'No Power', 'critical', 'power', TRUE, '["Device does not turn on","No LED lights","No fan spin"]'),
(4, 2, 'Random Shutdowns', 'high', 'power-off', TRUE, '["Computer shuts down unexpectedly","Blue screen then shutdown","Random power loss"]'),
(5, 3, 'No Sound', 'medium', 'volume-x', TRUE, '["No audio output","Speakers not working","Headphone jack no sound"]'),
(6, 4, 'No Internet', 'high', 'wifi-off', TRUE, '["Cannot browse websites","No network connection","Limited connectivity"]'),
(7, 4, 'WiFi Not Connecting', 'medium', 'wifi', TRUE, '["Cannot see WiFi networks","Authentication fails","Connected but no internet"]'),
(8, 5, 'Printer Offline', 'high', 'printer', TRUE, '["Printer shows offline","Cannot send print jobs","Queue stuck"]'),
(9, 5, 'Paper Jam', 'medium', 'file-warning', TRUE, '["Paper stuck in printer","Cannot feed paper","Multiple sheets feeding"]'),
(10, 6, 'Camera Offline', 'high', 'camera-off', TRUE, '["No video feed","Camera not responding","NVR shows camera offline"]'),
(11, 7, 'Blue Screen (BSOD)', 'critical', 'alert-triangle', TRUE, '["Blue screen with error code","System crashes","Automatic restart"]'),
(12, 7, 'Slow Performance', 'medium', 'gauge', TRUE, '["Computer runs slowly","Applications freeze","High CPU usage"]'),
(13, 7, 'Application Crash', 'medium', 'app-window-x', FALSE, '["Application closes unexpectedly","Error message appears","Program not responding"]'),
(14, 7, 'Windows Update Failed', 'medium', 'refresh-cw', FALSE, '["Update stuck at percentage","Update error code","System loops restarting"]'),
(15, 8, 'Overheating', 'high', 'thermometer', TRUE, '["Fan running at max speed","Hot to touch","Thermal shutdown"]'),
(16, 1, 'Wrong Resolution', 'low', 'scaling', FALSE, '["Display stretched or blurry","Cannot change resolution","Resolution locked"]'),
(17, 4, 'Slow Network', 'medium', 'gauge', FALSE, '["Pages load slowly","Download speed low","Lag during video calls"]'),
(18, 5, 'Poor Print Quality', 'low', 'image', FALSE, '["Faded text","Streaks on prints","Colors look wrong"]'),
(19, 8, 'Hard Drive Failing', 'critical', 'hard-drive', FALSE, '["Clicking noise from drive","Very slow file access","Blue screen on boot"]'),
(20, 6, 'No Recording', 'high', 'video-off', FALSE, '["NVR not recording","Storage full","Playback shows gaps"]');

-- ===== DECISION TREE NODES =====

-- === NO DISPLAY (Issue 1) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(1, 1, 'Is the computer powered on?', 'Check if the power LED is on and fans are spinning.', 'safe', 'A powered-off computer obviously has no display output.', 'Computer is ON, fans spinning, LEDs on', 'Computer is OFF — this is a power issue, not display', 2, 'redirect_power', FALSE, NULL, NULL, NULL, NULL),
(1, 2, 'Is the monitor powered on?', 'Check the monitor power LED and power cable.', 'safe', 'The monitor needs power to display anything.', 'Monitor LED is ON (usually green/blue)', 'Monitor LED is OFF — check power cable and outlet', 3, 'solved_monitor_power', FALSE, NULL, NULL, NULL, NULL),
(1, 3, 'Is the correct input source selected on the monitor?', 'Press the input/source button on the monitor. Try HDMI, DisplayPort, VGA.', 'safe', 'Wrong input source means the monitor ignores the actual video signal.', 'Tried all inputs, still no display', 'Display appeared after selecting correct input', 4, 'solved_input_source', FALSE, NULL, NULL, NULL, NULL),
(1, 4, 'Is the display cable properly connected and not damaged?', 'Unplug and re-plug the display cable on BOTH ends. Check for bent pins or damage.', 'safe', 'Loose or damaged cables are the #1 cause of no display.', 'Cable looks fine, reseated both ends — still no display', 'Reseating cable restored the display', 5, 'solved_cable', FALSE, NULL, NULL, NULL, NULL),
(1, 5, 'Does an external monitor show display?', 'Connect a different monitor or TV to the same video port.', 'safe', 'This isolates whether the problem is the original monitor or the computer.', 'External monitor shows display — original monitor is faulty', 'External monitor also shows no display — computer issue', 6, 'solved_monitor_fault', FALSE, NULL, NULL, NULL, NULL),
(1, 6, 'Do you hear beep codes or see diagnostic LEDs?', 'Listen carefully for beeps when powering on. Check for blinking LED patterns.', 'safe', 'Beep codes and LED patterns indicate specific hardware failures.', 'Beeps or LED patterns heard/seen', 'No beeps, no LED patterns', 7, 8, FALSE, NULL, NULL, NULL, NULL),
(1, 7, 'Have you tried reseating the RAM modules?', 'Power off, unplug, open case, remove RAM, firmly reseat it. Try one stick at a time.', 'caution', 'Loose RAM is a very common cause of no display.', 'RAM reseated — still no display', 'Display returned after reseating RAM', 9, 'solved_ram', FALSE, NULL, NULL, NULL, NULL),
(1, 8, 'Have you tried a different display cable?', 'Use a known-good cable from another workstation.', 'safe', 'Faulty cables can fail silently without visible damage.', 'Different cable used — still no display', 'New cable fixed the display', 9, 'solved_cable_replace', FALSE, NULL, NULL, NULL, NULL),
(1, 9, 'Have you tried connecting to the integrated graphics port?', 'Move the display cable to the motherboard video output (not GPU).', 'safe', 'This tests if the dedicated GPU is the problem.', 'Integrated graphics also has no display', 'Integrated graphics works — GPU may be faulty', NULL, 'solved_gpu', FALSE, NULL, NULL, NULL, NULL),
-- Terminal nodes
(1, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Monitor Power Issue', 'The monitor was not powered on.', 'Ensure the monitor power cable is firmly connected.'),
(1, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Incorrect Input Source', 'The monitor input was not set correctly.', 'Set the monitor input source to match the connected cable.'),
(1, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Display Cable Issue', 'The display cable was loose or disconnected.', 'Ensure all display cables are firmly connected at both ends.'),
(1, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Monitor Faulty', 'The original monitor does not display, but an external works.', 'Replace the monitor or send for manufacturer repair.'),
(1, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: RAM Issue', 'Reseating RAM resolved the issue.', 'Ensure RAM is properly seated. Run memtest86 to check.'),
(1, 105, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Faulty Cable', 'The display cable was faulty.', 'Use the new cable permanently.'),
(1, 106, '', '', 'caution', '', '', '', NULL, NULL, TRUE, 'escalated', 'Escalation Required', 'Neither GPU nor integrated graphics produce display.', 'Escalate for advanced motherboard hardware diagnosis.'),
(1, 107, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'redirect', 'Redirecting: This is a Power issue', 'The computer is not powered on.', 'Switch to No Power troubleshooting.');

-- === NO POWER (Issue 3) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(3, 1, 'Is the wall outlet working?', 'Test with a phone charger or another device.', 'safe', 'If the outlet has no power, nothing downstream will work.', 'Outlet has power', 'Outlet has no power', 2, 'solved_outlet', FALSE, NULL, NULL, NULL, NULL),
(3, 2, 'Is the power strip/UPS turned on?', 'Check the power strip switch or UPS power button.', 'safe', 'A switched-off power strip is surprisingly common.', 'Power strip is ON', 'Power strip was OFF', 3, 'solved_strip', FALSE, NULL, NULL, NULL, NULL),
(3, 3, 'Is the power cable securely connected on both ends?', 'Check the cable from the computer to the outlet/strip.', 'safe', 'A loose power cable is easy to fix.', 'Cable firmly connected — still no power', 'Cable was loose', 4, 'solved_cable', FALSE, NULL, NULL, NULL, NULL),
(3, 4, 'Is the PSU switch in the ON (I) position?', 'Check the back of the desktop PSU for a rocker switch.', 'safe', 'PSU switches can be accidentally flipped.', 'PSU switch is ON — still no power', 'PSU switch was OFF', 5, 'solved_psu_switch', FALSE, NULL, NULL, NULL, NULL),
(3, 5, 'For laptops: Does the charger LED work? For desktops: Do any LEDs light up?', 'Laptop: Check charger indicator. Desktop: Press power button, watch for LEDs.', 'safe', 'LED activity tells us if power reaches the motherboard.', 'Some LEDs are on', 'No LEDs at all', 6, 7, FALSE, NULL, NULL, NULL, NULL),
(3, 6, 'Have you tried a power drain (static discharge)?', 'Power off, unplug, hold power button 30 seconds, release, plug back in.', 'safe', 'Static charge can prevent booting.', 'Power drain done — still not working', 'Power drain fixed it', 7, 'solved_drain', FALSE, NULL, NULL, NULL, NULL),
(3, 7, 'Have you removed all external USB devices?', 'Disconnect everything except power, monitor, and keyboard. Try to boot.', 'safe', 'A faulty USB device can prevent booting.', 'All devices removed — still no boot', 'Computer boots after removing devices', 8, 'solved_external', FALSE, NULL, NULL, NULL, NULL),
(3, 8, 'For desktop: Do fans spin briefly? For laptop: Does it work with known-good charger?', 'Watch CPU and case fans for any movement.', 'safe', 'Any fan spin means PSU is delivering some power.', 'Fans spin briefly then stop', 'No fan spin at all', NULL, NULL, FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(3, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Outlet Has No Power', 'The wall outlet was not providing power.', 'Reset the circuit breaker. If still dead, call electrician.'),
(3, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Power Strip Was Off', 'The power strip was turned off.', 'Mark the power strip switch position clearly.'),
(3, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Loose Power Cable', 'The power cable was loose.', 'Check all power cable connections periodically.'),
(3, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: PSU Switch Was Off', 'The PSU rocker switch was OFF.', 'Label PSU switches clearly.'),
(3, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Static Discharge', 'Power drain resolved the issue.', 'The system had accumulated static charge.'),
(3, 105, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Faulty External Device', 'An external USB device was preventing boot.', 'Test each device individually to find the faulty one.');

-- === NO SOUND (Issue 5) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(5, 1, 'Is the volume turned up and not muted?', 'Check the speaker icon in the system tray. Ensure volume is above 0%.', 'safe', 'Muted or zero volume is the most common cause.', 'Volume is up, not muted', 'Volume was muted', 2, 'solved_muted', FALSE, NULL, NULL, NULL, NULL),
(5, 2, 'Is the correct playback device selected?', 'Right-click speaker icon > Sound settings. Check output device.', 'safe', 'Windows may default to a wrong device.', 'Correct device selected', 'Wrong device was selected', 3, 'solved_wrong_device', FALSE, NULL, NULL, NULL, NULL),
(5, 3, 'Are speakers/headphones physically connected properly?', 'Check cable connections. Try different audio port. Test on another device.', 'safe', 'Loose connections prevent signal transmission.', 'Connections verified', 'Reconnecting fixed it', 4, 'solved_connection', FALSE, NULL, NULL, NULL, NULL),
(5, 4, 'Is the Windows Audio service running?', 'Run services.msc, find Windows Audio. Check status is Running.', 'caution', 'If this service stops, all audio stops.', 'Service is running', 'Service was stopped', 5, 'solved_service', FALSE, NULL, NULL, NULL, NULL),
(5, 5, 'Have you tried updating the audio driver?', 'Device Manager > Sound controllers > right-click > Update driver.', 'caution', 'Corrupted drivers are a frequent cause.', 'Driver updated — still no sound', 'Driver update restored audio', 6, 'solved_driver', FALSE, NULL, NULL, NULL, NULL),
(5, 6, 'Have you tested with different speakers or headphones?', 'Connect known-good headphones or external speakers.', 'safe', 'This isolates the problem.', 'Different speakers also no sound', 'New speakers work', NULL, 'solved_speakers', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(5, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Volume Was Muted', 'System volume was muted or zero.', 'Adjust volume. Check application-specific volume too.'),
(5, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Wrong Playback Device', 'Windows was routing audio to wrong device.', 'Set correct default playback device.'),
(5, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Connection Issue', 'Audio devices were not properly connected.', 'Ensure cables are fully inserted into correct ports.'),
(5, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Windows Audio Service Stopped', 'The Windows Audio service had stopped.', 'Set Windows Audio service to Automatic startup.'),
(5, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Audio Driver Issue', 'Updating audio driver resolved the problem.', 'Keep audio drivers updated from manufacturer website.'),
(5, 105, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Speakers/Headphones Faulty', 'Original speakers do not work, known-good ones work.', 'Replace the faulty speakers or headphones.');

-- === NO INTERNET (Issue 6) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(6, 1, 'Is the network cable connected? Is WiFi connected?', 'Check cable at both ends. For WiFi, check the WiFi icon.', 'safe', 'Physical connectivity must be established first.', 'Connected — still no internet', 'Not connected — reconnected', 2, 'solved_cable', FALSE, NULL, NULL, NULL, NULL),
(6, 2, 'Can you ping the default gateway?', 'Run ipconfig, find Default Gateway, then ping it.', 'safe', 'Pinging gateway tests local network connectivity.', 'Gateway ping succeeds', 'Gateway ping fails', 3, 4, FALSE, NULL, NULL, NULL, NULL),
(6, 3, 'Can you ping 8.8.8.8?', 'Run ping 8.8.8.8 in CMD.', 'safe', 'If IP ping works but sites dont load, its DNS.', '8.8.8.8 ping works', '8.8.8.8 ping fails', 5, 6, FALSE, NULL, NULL, NULL, NULL),
(6, 4, 'Have you tried ipconfig /release then /renew?', 'Run ipconfig /release, wait, then ipconfig /renew.', 'caution', 'Forces DHCP to assign new IP, resolving conflicts.', 'Tried renew — still no gateway', 'New IP assigned, gateway works', 7, 'solved_dhcp', FALSE, NULL, NULL, NULL, NULL),
(6, 5, 'Have you flushed DNS? Run ipconfig /flushdns', 'Run ipconfig /flushdns in CMD, then browse.', 'safe', 'Corrupted DNS cache prevents name resolution.', 'DNS flush done — still cant browse', 'Browsing works after DNS flush', 8, 'solved_dns_cache', FALSE, NULL, NULL, NULL, NULL),
(6, 6, 'Try switching DNS to Google DNS (8.8.8.8)', 'In Network Settings > adapter > IPv4 > set DNS manually.', 'caution', 'ISP DNS servers can fail. Google DNS is reliable.', 'DNS changed — still no internet', 'Changing DNS fixed internet', 7, 'solved_dns_server', FALSE, NULL, NULL, NULL, NULL),
(6, 7, 'Have you restarted the network adapter?', 'Run ncpa.cpl, right-click adapter, Disable, wait 10s, Enable.', 'safe', 'Restarting adapter resets network stack.', 'Adapter restarted — still no connection', 'Adapter restart fixed connection', 8, 'solved_adapter', FALSE, NULL, NULL, NULL, NULL),
(6, 8, 'Is this affecting other computers on the same network?', 'Check with colleagues or test on another device.', 'safe', 'If multiple PCs affected, its the network infrastructure.', 'Multiple PCs affected', 'Only this PC affected', 'escalate_network', 'escalate_pc', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(6, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Network Not Connected', 'Cable was disconnected or WiFi not connected.', 'Ensure cables are secure. Check WiFi credentials.'),
(6, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: DHCP Issue', 'Releasing/renewing IP resolved DHCP issue.', 'Consider static IP if DHCP issues recur.'),
(6, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Corrupted DNS Cache', 'Flushing DNS cache resolved browsing.', 'Flush DNS cache as first step for browsing issues.'),
(6, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: DNS Server Issue', 'Switching to Google DNS resolved the issue.', 'Keep Google DNS configured.'),
(6, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Network Adapter Issue', 'Disabling/enabling adapter reset network stack.', 'Update NIC driver if this recurs.'),
(6, 105, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'escalated', 'Escalation Required: Network Infrastructure', 'Multiple PCs affected — network/switch/router issue.', 'Escalate to network team.');

-- === PRINTER OFFLINE (Issue 8) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(8, 1, 'Is the printer powered on and showing Ready?', 'Check the printer display panel and power LED.', 'safe', 'An offline printer wont accept jobs.', 'Printer is ON and shows Ready', 'Printer is OFF or showing error', 2, 'solved_power', FALSE, NULL, NULL, NULL, NULL),
(8, 2, 'Can you print a test page from the printer itself?', 'Use printer menu: Settings > Print Test Page.', 'safe', 'Self-test confirms printer hardware works.', 'Test page prints OK', 'Test page fails', 3, 'solved_printer_hw', FALSE, NULL, NULL, NULL, NULL),
(8, 3, 'Is the printer connected via USB or Network?', 'Check the connection type.', 'safe', 'Connection type determines next steps.', 'Connected', 'Connection type determined', 4, 4, FALSE, NULL, NULL, NULL, NULL),
(8, 4, 'USB: Try different port. Network: Ping printer IP.', 'USB: unplug and try different port. Network: ping printer IP.', 'safe', 'USB ports can fail, network connectivity must be verified.', 'Connection verified — still offline', 'Connection issue found', 5, 'solved_connection', FALSE, NULL, NULL, NULL, NULL),
(8, 5, 'Have you restarted the Print Spooler service?', 'Run services.msc, find Print Spooler, right-click > Restart.', 'caution', 'If spooler hangs, printers appear offline.', 'Spooler restarted — still offline', 'Printer came back online', 6, 'solved_spooler', FALSE, NULL, NULL, NULL, NULL),
(8, 6, 'Have you removed and re-added the printer?', 'Settings > Printers, remove printer, then Add printer.', 'safe', 'Re-adding refreshes drivers and settings.', 'Printer re-added — still offline', 'Printer works after re-adding', NULL, 'solved_readd', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(8, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Printer Power Issue', 'The printer was powered off.', 'Ensure printer is powered on and showing Ready.'),
(8, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Printer Hardware Failure', 'Self-test failed — hardware problem.', 'Contact manufacturer for repair or replacement.'),
(8, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Connection Issue', 'Printer connection was faulty.', 'For USB: use working port. For network: verify IP.'),
(8, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Print Spooler Issue', 'Restarting spooler restored functionality.', 'Set Print Spooler to Automatic restart.'),
(8, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Re-added Printer', 'Re-adding refreshed all settings.', 'Download latest driver from manufacturer website.');

-- === SLOW PERFORMANCE (Issue 12) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(12, 1, 'Is Task Manager showing high CPU usage?', 'Press Ctrl+Shift+Esc. Check CPU column. Above 80% sustained is high.', 'safe', 'High CPU causes slowness.', 'High CPU detected', 'CPU usage normal', 2, 3, FALSE, NULL, NULL, NULL, NULL),
(12, 2, 'Can you identify the process using high CPU?', 'In Task Manager, click CPU column to sort.', 'safe', 'Identifying the process is key.', 'Process identified', 'Cannot identify', 'solved_cpu_process', 4, FALSE, NULL, NULL, NULL, NULL),
(12, 3, 'Is disk usage at 100% in Task Manager?', 'Check the Disk column. 100% disk causes extreme slowness.', 'safe', '100% disk makes system unusable.', 'Disk at 100%', 'Disk usage normal', 4, 5, FALSE, NULL, NULL, NULL, NULL),
(12, 4, 'Run chkdsk C: /f (run as admin)', 'Open CMD as admin, run chkdsk C: /f. Agree to schedule on reboot.', 'caution', 'CHKDSK fixes disk errors causing 100% disk usage.', 'CHKDSK ran', 'CHKDSK fixed errors', 5, 'solved_disk', FALSE, NULL, NULL, NULL, NULL),
(12, 5, 'How much free RAM? Check Task Manager > Performance > Memory', 'If consistently under 10% free, system needs more RAM.', 'safe', 'Low RAM causes slowness.', 'Low RAM — under 10%', 'RAM is sufficient', 6, 7, FALSE, NULL, NULL, NULL, NULL),
(12, 6, 'Disable unnecessary startup programs', 'Task Manager > Startup tab. Disable unnecessary programs.', 'caution', 'Too many startup programs consume RAM and CPU.', 'Startup programs cleaned', 'System is faster', 7, 'solved_startup', FALSE, NULL, NULL, NULL, NULL),
(12, 7, 'Run sfc /scannow (run as admin)', 'Open CMD as admin, run sfc /scannow.', 'caution', 'Corrupted system files cause performance issues.', 'SFC ran', 'SFC restored files', NULL, 'solved_sfc', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(12, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Resource-Consuming Process', 'A process was consuming excessive CPU.', 'Monitor the process. Update or reinstall if it recurs.'),
(12, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Disk Errors Fixed', 'CHKDSK repaired disk errors.', 'Monitor disk health. Consider SSD upgrade.'),
(12, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Too Many Startup Programs', 'Disabling startup apps freed resources.', 'Only keep essential programs in startup.'),
(12, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Corrupted System Files', 'SFC repaired corrupted files.', 'Run DISM if SFC finds unfixable issues.');

-- === BSOD (Issue 11) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(11, 1, 'Does the BSOD show a specific error code?', 'Note down the exact error code on the blue screen.', 'safe', 'The error code identifies the specific cause.', 'Error code recorded', 'System restarts too fast', 2, 3, FALSE, NULL, NULL, NULL, NULL),
(11, 2, 'Does it mention a specific driver file (.sys)?', 'Look for file like nvlddmkm.sys or ntoskrnl.exe.', 'safe', 'Driver files indicate which driver is crashing.', 'Driver file identified', 'No specific file shown', 'solved_driver_bsod', 4, FALSE, NULL, NULL, NULL, NULL),
(11, 3, 'Can you boot into Safe Mode?', 'Press F8 or Shift+Restart. Try Safe Mode.', 'safe', 'Safe Mode loads only essential drivers.', 'Can boot into Safe Mode', 'Cannot reach Safe Mode', 4, 'escalate_bsod', FALSE, NULL, NULL, NULL, NULL),
(11, 4, 'Check Event Viewer for crash details', 'eventvwr.msc > Windows Logs > System. Look for Critical/Error.', 'safe', 'Event Viewer logs detailed crash info.', 'Event details found', 'No useful events', 5, 6, FALSE, NULL, NULL, NULL, NULL),
(11, 5, 'Run sfc /scannow and DISM', 'Run both as admin. SFC first, then DISM.', 'caution', 'Corrupted system files cause BSOD.', 'Both commands ran', 'Repairs successful', 6, 'solved_repair', FALSE, NULL, NULL, NULL, NULL),
(11, 6, 'Check for Windows updates and driver updates', 'Windows Update + update GPU and chipset drivers.', 'caution', 'Outdated drivers are #1 BSOD cause.', 'Updates installed — still crashing', 'Updates fixed stability', NULL, 'solved_updates', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(11, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Driver BSOD', 'Updating/rolling back driver resolved it.', 'Keep all drivers updated.'),
(11, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Corrupted System Files', 'SFC and DISM repaired files.', 'Monitor stability. Consider Windows refresh if recurring.'),
(11, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Outdated Software', 'Windows and driver updates resolved BSOD.', 'Enable automatic Windows updates.'),
(11, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'escalated', 'Escalation Required: Persistent BSOD', 'Too severe for standard troubleshooting.', 'Escalate to senior technician. May need memory/OS testing.');

-- === WIFI NOT CONNECTING (Issue 7) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(7, 1, 'Can you see WiFi networks in the list?', 'Click WiFi icon in system tray. Can you see available networks?', 'safe', 'If no networks visible, WiFi adapter may be disabled or faulty.', 'Can see networks', 'Cannot see any networks', 2, 'solved_adapter_issue', FALSE, NULL, NULL, NULL, NULL),
(7, 2, 'Does it connect but show "No Internet"?', 'Connect to the network. What does it say?', 'safe', 'Connected but no internet means different issue.', 'Connected, no internet', 'Cannot connect at all', 'solved_dns_or_gw', 3, FALSE, NULL, NULL, NULL, NULL),
(7, 3, 'Is the WiFi password correct?', 'Re-enter the WiFi password. Caps Lock may be on.', 'safe', 'Wrong password is the most common WiFi failure.', 'Password correct — still fails', 'Password was wrong', 4, 'solved_password', FALSE, NULL, NULL, NULL, NULL),
(7, 4, 'Have you forgotten the network and reconnected?', 'Settings > WiFi > Manage known networks > Forget > Reconnect.', 'safe', 'This resets the WiFi profile.', 'Still cannot connect', 'Reconnected successfully', NULL, 'solved_reconnect', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(7, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: WiFi Adapter Issue', 'WiFi adapter was disabled or faulty.', 'Enable WiFi adapter in Settings or Device Manager.'),
(7, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: DNS/Gateway Issue', 'Connected but no internet — DNS or gateway problem.', 'Flush DNS or switch to Google DNS.'),
(7, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Wrong Password', 'WiFi password was incorrect.', 'Store correct password in a secure note.'),
(7, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Reconnected Successfully', 'Forgetting and reconnecting reset the profile.', 'Check WiFi signal strength.');

-- === RANDOM SHUTOwnS (Issue 4) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(4, 1, 'Is the computer overheating?', 'Touch the vents. Check for thermal warnings.', 'safe', 'Overheating causes automatic shutdowns.', 'Computer is very hot', 'Temperature seems normal', 2, 3, FALSE, NULL, NULL, NULL, NULL),
(4, 2, 'Are fans spinning freely? Dust buildup?', 'Listen for fan noise. Check if fans are blocked.', 'safe', 'Dust-clogged fans cant cool effectively.', 'Fans clogged or not spinning', 'Fans seem fine', 'solved_cleaning', 4, FALSE, NULL, NULL, NULL, NULL),
(4, 3, 'Shutdown during specific tasks or random?', 'Note when it happens — idle, gaming, heavy work.', 'safe', 'Pattern helps identify cause.', 'During heavy load', 'Randomly', 4, 5, FALSE, NULL, NULL, NULL, NULL),
(4, 4, 'Check Event Viewer for shutdown cause', 'eventvwr.msc > System. Look for Event ID 41 (kernel-power).', 'safe', 'Event Viewer logs why system shut down.', 'Event found — power/hardware failure', 'No useful events', 5, 6, FALSE, NULL, NULL, NULL, NULL),
(4, 5, 'Have you checked the PSU? (Desktop)', 'Test with PSU tester or swap PSU.', 'caution', 'PSU failure causes random shutdowns under load.', 'PSU seems OK', 'PSU faulty', 6, 'solved_psu', FALSE, NULL, NULL, NULL, NULL),
(4, 6, 'Run Windows Memory Diagnostic', 'Search Start for Windows Memory Diagnostic. Run and restart.', 'caution', 'Faulty RAM causes random shutdowns.', 'Memory test complete', 'Memory errors found', NULL, 'solved_ram', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(4, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Overheating Due to Dust', 'Cleaning dust restored cooling.', 'Clean computer every 3-6 months.'),
(4, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Failing Power Supply', 'PSU failing causing random shutdowns.', 'Replace PSU with appropriate wattage and 80+ certification.'),
(4, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Faulty RAM', 'Memory test detected errors.', 'Replace faulty RAM module(s). Re-test to confirm.');

-- === CAMERA OFFLINE (Issue 10) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(10, 1, 'Is the camera receiving power?', 'Check camera LED. For PoE, check switch port LED.', 'safe', 'Camera without power wont work.', 'Camera has power (LED on)', 'No power', 2, 'solved_power', FALSE, NULL, NULL, NULL, NULL),
(10, 2, 'Is the network cable connected? Switch port active?', 'Check Ethernet cable and switch port LED.', 'safe', 'IP cameras need network connectivity.', 'Network connected, port active', 'Network cable or port issue', 3, 'solved_network', FALSE, NULL, NULL, NULL, NULL),
(10, 3, 'Can you ping the cameras IP?', 'Use CMD: ping <camera-IP>.', 'safe', 'Pinging confirms camera is reachable.', 'Ping succeeds', 'Ping fails', 4, 'solved_config', FALSE, NULL, NULL, NULL, NULL),
(10, 4, 'Is the camera visible in NVR software?', 'Open NVR interface. Check camera list.', 'safe', 'NVR must be configured to receive stream.', 'Camera in NVR but no video', 'Camera not in NVR', 'solved_nvr_config', 5, FALSE, NULL, NULL, NULL, NULL),
(10, 5, 'Have you rebooted the camera?', 'Disconnect power, wait 10s, reconnect. Or reboot NVR.', 'safe', 'Reboot fixes many camera issues.', 'Camera rebooted — still offline', 'Camera came back online', NULL, 'solved_reboot', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(10, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Camera Power Issue', 'Camera not receiving power.', 'Verify PoE switch budget, check power cable.'),
(10, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Network Cable Issue', 'Network cable or switch port was faulty.', 'Replace cable, try different switch port.'),
(10, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Camera Network Config', 'Camera had network configuration issue.', 'Ensure camera IP is in correct subnet.'),
(10, 103, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: NVR Configuration', 'NVR not configured correctly.', 'Re-add camera with correct protocol and credentials.'),
(10, 104, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Camera Needed Reboot', 'Power cycling resolved the issue.', 'Check firmware if this recurs.');

-- === OVERHEATING (Issue 15) ===
INSERT INTO decision_nodes (issue_id, node_number, question, instruction, risk_level, why, expected_yes, expected_no, yes_next_node, no_next_node, is_terminal, terminal_result, terminal_message, terminal_detail, terminal_solution) VALUES
(15, 1, 'Are the fans spinning at all?', 'Listen and look at fans when computer is on.', 'safe', 'No fan spin means cooling system failure.', 'Fans spinning', 'Fans not spinning', 2, 'solved_fan_replace', FALSE, NULL, NULL, NULL, NULL),
(15, 2, 'Is there excessive dust buildup inside?', 'Open case and inspect for dust on fans and heatsinks.', 'safe', 'Dust blocks airflow and insulates heat.', 'Dust buildup found', 'Fans clean', 3, 4, FALSE, NULL, NULL, NULL, NULL),
(15, 3, 'Clean dust from fans and heatsinks', 'Use compressed air to blow out dust. Hold fans while blowing.', 'caution', 'Cleaning restores proper airflow.', 'Cleaned — check temperature', 'Still overheating after cleaning', 4, 5, FALSE, NULL, NULL, NULL, NULL),
(15, 4, 'Is the thermal paste old or dried?', 'If heatsink was recently removed, thermal paste may need replacing.', 'caution', 'Old thermal paste loses effectiveness.', 'Thermal paste needs replacing', 'Thermal paste looks OK', 'solved_thermal_paste', 'escalate_hw', FALSE, NULL, NULL, NULL, NULL),
(15, 5, 'Is the computer in a well-ventilated area?', 'Check for blocked vents, carpet, enclosed space.', 'safe', 'Poor environment causes overheating.', 'Environment is OK', 'Poor ventilation found', NULL, 'solved_ventilation', FALSE, NULL, NULL, NULL, NULL),
-- Terminal
(15, 100, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'hardware', 'Diagnosis: Fan Failure', 'Fan not spinning — needs replacement.', 'Replace the faulty fan.'),
(15, 101, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Thermal Paste', 'Replacing thermal paste resolved overheating.', 'Use quality thermal paste. Apply thin, even layer.'),
(15, 102, '', '', 'safe', '', '', '', NULL, NULL, TRUE, 'solved', 'Problem Solved: Poor Ventilation', 'Moving computer to ventilated area helped.', 'Keep computer away from heat sources. Ensure 6 inches clearance.'),
(15, 103, '', '', 'caution', '', '', '', NULL, NULL, TRUE, 'escalated', 'Escalation Required: Persistent Overheating', 'Standard fixes did not resolve overheating.', 'Escalate for heatsink reseating or hardware replacement.');

-- ===== MANUFACTURERS =====
INSERT INTO manufacturers (name, icon, model_count) VALUES
('Dell', 'laptop', 8), ('Lenovo', 'laptop', 6), ('HP', 'printer', 7),
('Cisco', 'wifi', 5), ('Hikvision', 'camera', 4), ('APC', 'battery-charging', 3),
('Samsung', 'monitor', 4), ('Ubiquiti', 'wifi', 3);

-- ===== DEVICE MODELS =====
INSERT INTO device_models (manufacturer_id, manufacturer_name, model, device_type, generation, specs, known_issues, tools_needed) VALUES
(1, 'Dell', 'OptiPlex 7090', 'Desktop', '11th Gen Intel', '["Intel Core i7-11700","16GB DDR4","512GB NVMe SSD"]', '["BIOS firmware updates needed"]', '["Phillips screwdriver","Anti-static wrist strap"]'),
(2, 'Lenovo', 'ThinkPad T14 Gen 3', 'Laptop', '12th Gen Intel', '["Intel Core i5-1240P","16GB LPDDR5","512GB NVMe SSD","14 inch FHD IPS"]', '["Trackpad sensitivity","Battery drain in sleep"]', '["Phillips screwdriver","Plastic spudger","Anti-static wrist strap"]'),
(3, 'HP', 'LaserJet Pro M404dn', 'Printer', 'Mono Laser', '["40 ppm print speed","Duplex printing","Ethernet/USB"]', '["Firmware updates required","Toner chip recognition"]', '["Standard tools","Toner cartridge"]'),
(4, 'Dell', 'Latitude 5520', 'Laptop', '11th Gen Intel', '["Intel Core i5-1145G7","8GB DDR4","256GB SSD"]', '["Thunderbolt dock issues","WiFi disconnects"]', '["Phillips screwdriver","Anti-static wrist strap"]'),
(5, 'Hikvision', 'DS-2CD2143G2-I', 'CCTV Camera', 'AcuSense Gen 2', '["4MP resolution","PoE powered","IP67 weatherproof"]', '["Firmware update for ONVIF","PoE budget"]', '["PoE switch","Cable tester","Ladder"]'),
(3, 'HP', 'ProBook 450 G9', 'Laptop', '12th Gen Intel', '["Intel Core i5-1235U","8GB DDR4","256GB SSD"]', '["Fan noise under load","BIOS update recommended"]', '["Phillips screwdriver","Anti-static strap"]'),
(1, 'Dell', 'Latitude 7420', 'Laptop', '11th Gen Intel', '["Intel Core i7-1185G7","16GB LPDDR4x","512GB SSD"]', '["Thunderbolt compatibility","Battery swelling"]', '["Phillips screwdriver","Anti-static wrist strap"]'),
(2, 'Lenovo', 'ThinkCentre M70s', 'Desktop', '11th Gen Intel', '["Intel Core i5-11500","8GB DDR4","256GB SSD"]', '["Front USB port issues","BIOS updates"]', '["Phillips screwdriver","Anti-static wrist strap"]');

-- ===== COMMANDS =====
INSERT INTO commands (command, category, risk_level, description, when_to_use, example, expected_output, next_step, sort_order) VALUES
('ipconfig', 'Network', 'safe', 'Display IP configuration', 'Check IP address, subnet, gateway', 'ipconfig', 'IPv4, Subnet, Gateway, DNS', 'If no IP: check DHCP', 1),
('ipconfig /all', 'Network', 'safe', 'Detailed IP configuration', 'Verify DHCP server, DNS, MAC', 'ipconfig /all', 'Full adapter details', 'Check DHCP Enabled', 2),
('ipconfig /release', 'Network', 'caution', 'Release current IP', 'IP conflicts or DHCP issues', 'ipconfig /release', 'IP released', 'Follow with /renew', 3),
('ipconfig /renew', 'Network', 'caution', 'Request new IP from DHCP', 'After release or IP conflict', 'ipconfig /renew', 'New IP assigned', 'If fails: check DHCP server', 4),
('ipconfig /flushdns', 'Network', 'safe', 'Clear DNS resolver cache', 'DNS resolution incorrect', 'ipconfig /flushdns', 'DNS cache flushed', 'Test browsing', 5),
('ping', 'Network', 'safe', 'Test connectivity to host', 'Verify host reachability', 'ping 8.8.8.8', 'Reply from target', 'If timeout: check firewall/cable', 6),
('tracert', 'Network', 'safe', 'Trace route to destination', 'Find where connectivity fails', 'tracert google.com', 'List of hops with latency', 'Timeout at hop = issue', 7),
('nslookup', 'Network', 'safe', 'Query DNS servers', 'Websites dont load but IPs work', 'nslookup google.com', 'DNS resolution result', 'Try nslookup google.com 8.8.8.8', 8),
('sfc /scannow', 'System', 'caution', 'Scan and repair system files', 'System instability, BSOD, crashes', 'sfc /scannow', 'Files repaired or errors found', 'If errors: run DISM next', 9),
('DISM /Online /Cleanup-Image /RestoreHealth', 'System', 'caution', 'Repair Windows system image', 'sfc cannot fix all errors', 'DISM /Online /Cleanup-Image /RestoreHealth', 'Operation completed successfully', 'Run sfc /scannow again after', 10),
('chkdsk C: /f', 'Disk', 'caution', 'Check and fix disk errors', 'Slow file access, 100% disk', 'chkdsk C: /f', 'Scheduled on next restart', 'Restart computer', 11),
('taskmgr', 'Process', 'safe', 'Open Task Manager', 'Check CPU, memory, disk usage', 'taskmgr', 'Task Manager window', 'Check Processes and Startup tabs', 12),
('eventvwr', 'System', 'safe', 'Open Event Viewer', 'Investigate crashes, BSODs', 'eventvwr', 'Event Viewer', 'Check System > Critical/Error', 13),
('netstat -an', 'Network', 'safe', 'Display active connections', 'Check ports, suspicious connections', 'netstat -an', 'TCP/UDP connections list', 'ESTABLISHED = active, LISTENING = waiting', 14),
('systeminfo', 'System', 'safe', 'System information', 'Gather OS, hardware, hotfixes', 'systeminfo', 'OS, processor, RAM, hotfixes', 'Useful for documentation', 15);

-- ===== TOOLS =====
INSERT INTO tools (name, icon, category, purpose, when_to_use, safety) VALUES
('Phillips Screwdriver Set', 'screwdriver', 'Hand Tools', 'Opening cases, securing components', 'Hardware installation, RAM/SSD upgrade', 'Use correct size to avoid stripping'),
('Anti-Static Wrist Strap', 'shield', 'Safety', 'Prevent ESD damage to components', 'Any time touching internal components', 'Always clip to grounded metal'),
('Network Cable Tester', 'zap', 'Network', 'Test Ethernet cable continuity', 'Network connectivity issues', 'Low voltage tool, safe'),
('USB Boot Drive (8GB+)', 'usb', 'Software', 'Boot recovery, install OS, diagnostics', 'OS repair, fresh install', 'Ensure correct images on drive'),
('Thermal Paste', 'thermometer', 'Hardware', 'Replace CPU/GPU thermal compound', 'Overheating after heatsink removal', 'Do not apply too much'),
('Multimeter', 'activity', 'Diagnostics', 'Measure voltage, current, resistance', 'PSU testing, cable testing', 'Use appropriate voltage range'),
('Crimping Tool', 'scissors', 'Network', 'Terminate Ethernet with RJ45', 'Custom cables, replacing connectors', 'Use T568B wiring standard'),
('Cable Labels', 'tag', 'Organization', 'Label cables for identification', 'During installation or reorganization', 'None');

-- ===== TIPS =====
INSERT INTO tips (category, content) VALUES
('Hardware', 'Before replacing a component, test against a known-good component whenever practical.'),
('Network', 'Always ping the gateway first. If gateway fails, focus on local network before DNS.'),
('Software', 'Run sfc /scannow as first step for many Windows issues. Safe and can fix corruption.'),
('Safety', 'Always wear anti-static wrist strap when touching internal components.'),
('Printer', 'When printer shows offline, restart Print Spooler first. Resolves 60% of cases.'),
('Customer', 'Explain in simple terms. "Checking the cable" is better than "verifying physical layer".'),
('Troubleshooting', 'Start with simplest cause. Check cables before hardware. Check software before replacing parts.'),
('CCTV', 'Single camera offline? Check PoE switch port first. Move cable to known-good port.');

-- ===== KNOWLEDGE ARTICLES =====
INSERT INTO knowledge_articles (title, category, content, status, author_id, rating, use_count, verified) VALUES
('How to Fix No Display on Desktop PC', 'Display', 'Complete guide to diagnosing no display issues.', 'published', 1, 4.7, 137, TRUE),
('Network Troubleshooting Complete Guide', 'Network', 'Comprehensive network troubleshooting from physical layer to DNS.', 'published', 1, 4.8, 203, TRUE),
('Printer Offline Fix - HP LaserJet Series', 'Printer', 'Step-by-step guide for HP LaserJet offline issues.', 'published', 2, 4.5, 89, TRUE),
('CCTV Camera Offline - IP Camera Guide', 'CCTV', 'Diagnosing and fixing IP camera offline issues.', 'published', 1, 4.6, 67, TRUE),
('BSOD Troubleshooting Guide', 'Software', 'Systematic approach to resolving Blue Screen errors.', 'published', 2, 4.9, 156, TRUE),
('Power Issues: No Power Troubleshooting', 'Power', 'Complete power troubleshooting for desktops and laptops.', 'published', 1, 4.4, 112, TRUE),
('Slow Computer: Speed Up Windows 10/11', 'Software', 'Practical steps to improve slow computer performance.', 'published', 1, 4.3, 198, TRUE),
('ESD Safety Best Practices for Field IT', 'Safety', 'Electrostatic discharge prevention guide.', 'published', 1, 4.6, 78, TRUE),
('Windows Recovery Tools Guide', 'Software', 'Using sfc, DISM, and chkdsk to fix system issues.', 'draft', 2, 0, 0, FALSE),
('WiFi Troubleshooting Quick Reference', 'Network', 'Quick reference for common WiFi issues.', 'published', 1, 4.5, 65, TRUE);

-- ===== SAMPLE TICKETS =====
INSERT INTO troubleshooting_sessions (ticket_number, user_id, issue_id, title, device, department, location, priority, status, assigned_to) VALUES
('TK-1001', 2, 1, 'No Display — Desktop PC', 'Dell OptiPlex 7090', 'Finance', 'Floor 3, Desk 42', 'high', 'solved', 2),
('TK-1002', 3, 6, 'Network Slow — Floor 3', 'HP ProBook 450', 'HR', 'Floor 3, Room 301', 'medium', 'in_progress', 2),
('TK-1003', 2, 8, 'Printer Offline — Reception', 'HP LaserJet Pro M404', 'Reception', 'Ground Floor, Reception', 'high', 'escalated', 2),
('TK-1004', 2, 5, 'No Sound — Meeting Room', 'Lenovo ThinkCentre M70s', 'Operations', 'Floor 2, Meeting Room A', 'medium', 'solved', 2),
('TK-1005', 4, 7, 'WiFi Not Connecting', 'Dell Latitude 5520', 'Sales', 'Floor 1, Sales Area', 'low', 'in_progress', 2);

-- ===== SAMPLE AUDIT LOGS =====
INSERT INTO audit_logs (user_id, action, resource_type, ip_address, details) VALUES
(1, 'LOGIN', 'auth', '192.168.1.50', '{"method":"password"}'),
(2, 'CREATE', 'ticket', '192.168.1.51', '{"title":"No Display — Desktop PC"}'),
(2, 'EDIT', 'ticket', '192.168.1.51', '{"status":"solved","resolution":"reseated RAM"}'),
(3, 'APPROVE', 'knowledge', '192.168.1.52', '{"article":"Printer Offline Fix"}'),
(1, 'CREATE', 'department', '192.168.1.50', '{"name":"Reception"}');
