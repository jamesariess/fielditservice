<?php
// Migration runner: add steps_approved + steps_approved_by columns
require_once __DIR__ . '/config/app.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once __DIR__ . '/includes/Database.php';
}
require_once __DIR__ . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    try {
        // Check if columns already exist
        $cols = Database::fetch("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'troubleshooting_sessions' AND COLUMN_NAME IN ('steps_approved', 'steps_approved_by')");
        
        $added = [];
        if (!$cols || !in_array('steps_approved', $cols ? [$cols['COLUMN_NAME']] : [])) {
            Database::query("ALTER TABLE troubleshooting_sessions ADD COLUMN steps_approved tinyint(1) DEFAULT 0");
            $added[] = 'steps_approved';
        }
        $cols2 = Database::fetch("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'troubleshooting_sessions' AND COLUMN_NAME = 'steps_approved_by'");
        if (!$cols2) {
            Database::query("ALTER TABLE troubleshooting_sessions ADD COLUMN steps_approved_by varchar(150) DEFAULT NULL");
            $added[] = 'steps_approved_by';
        }
        
        // Try to create index (ignore if exists)
        try { Database::query("CREATE INDEX idx_sess_steps_approved ON troubleshooting_sessions (steps_approved)"); } catch (Exception $e) {}
        
        echo json_encode(['success' => true, 'added' => $added, 'msg' => 'Migration applied']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => true, 'added' => [], 'msg' => 'Demo mode - no migration needed']);
}