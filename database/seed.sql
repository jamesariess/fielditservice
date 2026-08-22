-- Field IT Support Hub - Seed Data
USE fieldit_hub;

-- ============================================
-- ROLES
-- ============================================

INSERT INTO roles (id, name, description, is_system) VALUES
(1, 'Super Admin', 'Full system access', 1),
(2, 'Admin', 'Manage knowledge, users, equipment, troubleshooting', 1),
(3, 'Supervisor', 'Manage assigned department, review submissions', 1),
(4, 'Field IT', 'Troubleshoot, document, use AI, chat', 1),
(5, 'Standard User', 'View knowledge, create support requests', 1);

-- ============================================
-- PERMISSIONS
-- ============================================

INSERT INTO permissions (permission_key, module, action, description) VALUES
-- Dashboard
('dashboard.view', 'dashboard', 'view', 'View dashboard'),

-- Troubleshooting
('troubleshooting.view', 'troubleshooting', 'view', 'View troubleshooting guides'),
('troubleshooting.create', 'troubleshooting', 'create', 'Create troubleshooting sessions'),
('troubleshooting.edit', 'troubleshooting', 'edit', 'Edit troubleshooting flows'),
('troubleshooting.delete', 'troubleshooting', 'delete', 'Delete troubleshooting flows'),

-- Knowledge Base
('knowledge.view', 'knowledge', 'view', 'View knowledge base'),
('knowledge.create', 'knowledge', 'create', 'Create knowledge articles'),
('knowledge.edit', 'knowledge', 'edit', 'Edit knowledge articles'),
('knowledge.delete', 'knowledge', 'delete', 'Delete knowledge articles'),
('knowledge.approve', 'knowledge', 'approve', 'Approve knowledge articles'),
('knowledge.publish', 'knowledge', 'publish', 'Publish knowledge articles'),
('knowledge.manage', 'knowledge', 'manage', 'Manage all knowledge'),

-- Equipment
('equipment.view', 'equipment', 'view', 'View equipment database'),
('equipment.create', 'equipment', 'create', 'Create equipment records'),
('equipment.edit', 'equipment', 'edit', 'Edit equipment records'),
('equipment.delete', 'equipment', 'delete', 'Delete equipment records'),
('equipment.manage', 'equipment', 'manage', 'Manage all equipment'),

-- Commands
('commands.view', 'commands', 'view', 'View commands reference'),
('commands.create', 'commands', 'create', 'Create commands'),
('commands.edit', 'commands', 'edit', 'Edit commands'),
('commands.delete', 'commands', 'delete', 'Delete commands'),

-- Tools
('tools.view', 'tools', 'view', 'View tools reference'),
('tools.create', 'tools', 'create', 'Create tool entries'),
('tools.edit', 'tools', 'edit', 'Edit tool entries'),
('tools.delete', 'tools', 'delete', 'Delete tool entries'),
('tools.manage', 'tools', 'manage', 'Manage tools'),

-- AI
('ai.use', 'ai', 'use', 'Use AI assistant'),
('ai.web_search', 'ai', 'web_search', 'Use AI web search'),
('ai.manage', 'ai', 'manage', 'Manage AI settings'),

-- Tickets
('tickets.view', 'tickets', 'view', 'View tickets'),
('tickets.create', 'tickets', 'create', 'Create tickets'),
('tickets.edit', 'tickets', 'edit', 'Edit tickets'),
('tickets.assign', 'tickets', 'assign', 'Assign tickets'),

-- Documentation
('documentation.view', 'documentation', 'view', 'View documentation'),
('documentation.create', 'documentation', 'create', 'Submit documentation'),
('documentation.edit', 'documentation', 'edit', 'Edit documentation'),
('documentation.manage', 'documentation', 'manage', 'Manage documentation'),

-- Chat
('chat.use', 'chat', 'use', 'Use team chat'),

-- Users
('users.view', 'users', 'view', 'View users'),
('users.create', 'users', 'create', 'Create users'),
('users.edit', 'users', 'edit', 'Edit users'),
('users.delete', 'users', 'delete', 'Delete users'),
('users.manage', 'users', 'manage', 'Manage all users'),

-- Roles & Permissions
('roles.manage', 'roles', 'manage', 'Manage roles and permissions'),

-- Departments
('departments.view', 'departments', 'view', 'View departments'),
('departments.manage', 'departments', 'manage', 'Manage departments'),

-- Contacts
('contacts.view', 'contacts', 'view', 'View department contacts'),
('contacts.manage', 'contacts', 'manage', 'Manage contacts'),

-- Audit
('audit.view', 'audit', 'view', 'View audit logs'),

-- System
('system.settings', 'system', 'settings', 'Manage system settings'),
('system.export', 'system', 'export', 'Export data');

-- ============================================
-- ROLE PERMISSIONS
-- ============================================

-- Super Admin: all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Admin: most permissions except system settings
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_key NOT IN ('system.settings');

-- Supervisor
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_key IN (
    'dashboard.view', 'troubleshooting.view', 'troubleshooting.create', 'troubleshooting.edit',
    'knowledge.view', 'knowledge.create', 'knowledge.edit', 'knowledge.approve',
    'equipment.view', 'commands.view', 'tools.view',
    'ai.use', 'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.assign',
    'documentation.view', 'documentation.create', 'documentation.manage',
    'chat.use', 'users.view', 'users.edit', 'departments.view', 'contacts.view', 'contacts.manage'
);

-- Field IT
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE permission_key IN (
    'dashboard.view', 'troubleshooting.view', 'troubleshooting.create',
    'knowledge.view', 'knowledge.create',
    'equipment.view', 'commands.view', 'tools.view',
    'ai.use', 'tickets.view', 'tickets.create',
    'documentation.view', 'documentation.create',
    'chat.use'
);

-- Standard User
INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE permission_key IN (
    'dashboard.view', 'knowledge.view', 'equipment.view', 'commands.view', 'tools.view',
    'tickets.view', 'tickets.create', 'chat.use'
);

-- ============================================
-- DEPARTMENTS
-- ============================================

INSERT INTO departments (id, name, description) VALUES
(1, 'IT Department', 'Information Technology'),
(2, 'Finance', 'Finance and Accounting'),
(3, 'Marketing', 'Marketing and Sales'),
(4, 'HR', 'Human Resources'),
(5, 'Operations', 'Operations'),
(6, 'Security', 'Security and Facilities');

-- ============================================
-- USERS (passwords are bcrypt hashed)
-- admin123 = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- fieldit123 = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- Note: In production, use proper bcrypt hashes. These are placeholder hashes.
-- ============================================

INSERT INTO users (id, email, password_hash, full_name, role_id, department_id, status) VALUES
(1, 'admin@fieldit.local', '$2y$10$YourHashHere1234567890abcdefghijklmnop', 'System Admin', 1, 1, 'active'),
(2, 'fieldit@fieldit.local', '$2y$10$YourHashHere1234567890abcdefghijklmnop', 'Juan Dela Cruz', 4, 1, 'active'),
(3, 'supervisor@fieldit.local', '$2y$10$YourHashHere1234567890abcdefghijklmnop', 'Maria Santos', 3, 1, 'active'),
(4, 'user@fieldit.local', '$2y$10$YourHashHere1234567890abcdefghijklmnop', 'Carlo Reyes', 5, 2, 'active');

-- ============================================
-- TROUBLESHOOTING CATEGORIES
-- ============================================

INSERT INTO troubleshooting_categories (name, slug, icon, sort_order) VALUES
('Display Issues', 'display', 'monitor', 1),
('Power Issues', 'power', 'power', 2),
('Audio Issues', 'audio', 'volume-x', 3),
('Network Issues', 'network', 'wifi', 4),
('Printer Issues', 'printer', 'printer', 5),
('CCTV Issues', 'cctv', 'camera', 6),
('Software Issues', 'software', 'app-window', 7),
('Hardware Issues', 'hardware', 'cpu', 8);

-- ============================================
-- DEVICE TYPES
-- ============================================

INSERT INTO device_types (name, icon) VALUES
('Laptop', 'laptop'), ('Desktop', 'desktop'), ('Server', 'server'),
('Monitor', 'monitor'), ('Printer', 'printer'), ('Router', 'router'),
('Switch', 'network'), ('Access Point', 'wifi'), ('CCTV Camera', 'camera'),
('DVR/NVR', 'hard-drive'), ('UPS', 'battery'), ('POS', 'credit-card'),
('Scanner', 'scan'), ('Other', 'smartphone');

-- ============================================
-- MANUFACTURERS
-- ============================================

INSERT INTO manufacturers (name) VALUES
('Lenovo'), ('Dell'), ('HP'), ('Cisco'), ('Hikvision'),
('Epson'), ('Brother'), ('Canon'), ('Samsung'), ('ASUS');

-- ============================================
-- COMMAND CATEGORIES
-- ============================================

INSERT INTO command_categories (name, slug) VALUES
('Network', 'network'), ('System', 'system'), ('Disk', 'disk'),
('Process', 'process'), ('User', 'user'), ('Security', 'security');

-- ============================================
-- SAMPLE COMMANDS
-- ============================================

INSERT INTO commands (category_id, command, description, when_to_use, example, expected_output, common_errors, next_steps, risk_level) VALUES
(1, 'ipconfig', 'Display current IP configuration', 'When troubleshooting network connectivity', 'ipconfig /all', 'Shows IP, subnet, gateway, DNS', 'Media disconnected', 'Check cable if no adapter listed', 'safe'),
(1, 'ipconfig /release', 'Release current IP address', 'IP conflict or DHCP issues', 'ipconfig /release', 'IP released', '', 'Follow with /renew', 'safe'),
(1, 'ipconfig /renew', 'Request new IP from DHCP', 'After release or no IP assigned', 'ipconfig /renew', 'New IP assigned', '169.254.x.x = DHCP failure', 'Check DHCP server', 'safe'),
(1, 'ipconfig /flushdns', 'Clear DNS cache', 'DNS resolution fails', 'ipconfig /flushdns', 'Cache flushed', '', 'Test with nslookup', 'safe'),
(1, 'ping', 'Test connectivity', 'Verify network reachability', 'ping 8.8.8.8', 'Reply with TTL/time', 'Request timed out = unreachable', 'Trace route to find break', 'safe'),
(1, 'nslookup', 'Query DNS records', 'DNS problems suspected', 'nslookup google.com', 'Returns resolved IP', 'DNS timeout = DNS server issue', 'Try alternate DNS', 'safe'),
(1, 'tracert', 'Trace network path', 'Find where connection breaks', 'tracert google.com', 'List of hops with times', 'Timeout at hop = issue there', 'Check that hop', 'safe'),
(2, 'sfc /scannow', 'Scan and repair system files', 'System file corruption suspected', 'sfc /scannow (Admin)', 'Verification results', 'Cannot run = needs admin', 'Try DISM first', 'caution'),
(2, 'DISM /Online /Cleanup-Image /RestoreHealth', 'Repair Windows image', 'sfc cannot repair', 'DISM /Online /Cleanup-Image /RestoreHealth', 'Image repair result', '', 'Run sfc again after', 'caution'),
(2, 'systeminfo', 'Display system information', 'Gathering device details', 'systeminfo', 'OS, RAM, CPU, adapters', '', 'Record for escalation', 'safe'),
(3, 'chkdsk', 'Check disk for errors', 'Disk errors suspected', 'chkdsk C: /f /r', 'Check stages and repairs', 'Volume in use = schedule restart', 'Schedule for next boot', 'caution'),
(4, 'taskmgr', 'Open Task Manager', 'Check resource usage', 'taskmgr', 'Task Manager window', '', 'Check CPU/Memory/Disk tabs', 'safe'),
(4, 'taskkill', 'Kill a process', 'Unresponsive application', 'taskkill /PID 1234 /F', 'Process terminated', 'Access denied = need admin', 'Use with caution', 'caution');
