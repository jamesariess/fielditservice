<?php
/**
 * Equipment API - Save (Create/Update/Delete)
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT', 'DELETE'])) { json_response(['error' => 'Method not allowed'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];

$id = intval($input['id'] ?? 0);

// DELETE
if ($method === 'DELETE') {
    if ($id <= 0) { json_response(['error' => 'id required'], 400); exit; }
    try {
        Database::update('equipment', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        Database::insert('audit_logs', [
            'user_id' => Auth::userId(), 'action' => 'DELETE', 'resource_type' => 'equipment',
            'resource_id' => $id, 'details' => json_encode(['manufacturer' => $input['manufacturer'] ?? '']),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        json_response(['success' => true]);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
    exit;
}

// CREATE / UPDATE
$manufacturer = trim($input['manufacturer'] ?? '');
$model_name = trim($input['model_name'] ?? '');
$device_type = trim($input['device_type'] ?? 'Other');

if (empty($manufacturer) || empty($model_name)) {
    json_response(['error' => 'Manufacturer and model name are required'], 400);
    exit;
}

$saveData = [
    'manufacturer' => $manufacturer,
    'model_name' => $model_name,
    'device_type' => $device_type,
    'year' => trim($input['year'] ?? ''),
    'cpu' => trim($input['cpu'] ?? ''),
    'ram' => trim($input['ram'] ?? ''),
    'storage' => trim($input['storage'] ?? ''),
    'display_spec' => trim($input['display_spec'] ?? ''),
    'ports' => trim($input['ports'] ?? ''),
    'known_issues' => trim($input['known_issues'] ?? ''),
    'repair_guides' => trim($input['repair_guides'] ?? ''),
    'tools_needed' => trim($input['tools_needed'] ?? ''),
    'location' => trim($input['location'] ?? ''),
    'asset_tag' => trim($input['asset_tag'] ?? ''),
    'notes' => trim($input['notes'] ?? ''),
    'image_url' => trim($input['image_url'] ?? ''),
    'disassembly_guide' => trim($input['disassembly_guide'] ?? ''),
    'assembly_guide' => trim($input['assembly_guide'] ?? ''),
    'guide_videos' => trim($input['guide_videos'] ?? ''),
];

try {
    if ($id > 0) {
        $existing = Database::fetch("SELECT id FROM equipment WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$existing) { json_response(['error' => 'Not found'], 404); exit; }
        Database::update('equipment', $saveData, 'id = ?', [$id]);
        Database::insert('audit_logs', [
            'user_id' => Auth::userId(), 'action' => 'UPDATE', 'resource_type' => 'equipment',
            'resource_id' => $id, 'details' => json_encode(['manufacturer' => $manufacturer, 'model' => $model_name]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        json_response(['success' => true, 'id' => $id]);
    } else {
        $saveData['created_by'] = Auth::userId();
        $newId = Database::insert('equipment', $saveData);
        Database::insert('audit_logs', [
            'user_id' => Auth::userId(), 'action' => 'CREATE', 'resource_type' => 'equipment',
            'resource_id' => $newId, 'details' => json_encode(['manufacturer' => $manufacturer, 'model' => $model_name]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        json_response(['success' => true, 'id' => $newId]);
    }
} catch (Exception $e) {
    json_response(['error' => 'Failed to save: ' . $e->getMessage()], 500);
}
