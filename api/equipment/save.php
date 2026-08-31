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

$rawInput = file_get_contents('php://input');
$input = [];

if (is_string($rawInput) && strlen($rawInput) > 0) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

$id = intval($input['id'] ?? 0);
$type = trim($input['type'] ?? ($_POST['type'] ?? 'equipment'));
$equipmentTableExists = Database::fetch("SHOW TABLES LIKE 'equipment'") !== null;

// DELETE
if ($method === 'DELETE') {
    if ($id <= 0) { json_response(['error' => 'id required'], 400); exit; }
    try {
        if ($type === 'tool') {
            Database::delete('tools', 'id = ?', [$id]);
        } elseif ($equipmentTableExists) {
            Database::update('equipment', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        } else {
            Database::delete('device_models', 'id = ?', [$id]);
        }
        Database::insert('audit_logs', [
            'user_id' => Auth::userId(), 'action' => 'DELETE', 'resource_type' => $type,
            'resource_id' => $id, 'details' => json_encode(['id' => $id]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        json_response(['success' => true]);
    } catch (Exception $e) { json_response(['error' => $e->getMessage()], 500); }
    exit;
}

// CREATE / UPDATE
if ($type === 'tool') {
    $name = trim($input['name'] ?? '');

    if (empty($name)) {
        json_response(['error' => 'Tool name is required'], 400);
        exit;
    }

    $saveData = [
        'name' => $name,
        'icon' => trim($input['icon'] ?? ''),
        'purpose' => trim($input['description'] ?? trim($input['purpose'] ?? '')),
        'when_to_use' => trim($input['when_to_use'] ?? ''),
        'how_to_use' => trim($input['how_to_use'] ?? ''),
        'safety' => trim($input['safety'] ?? ''),
        'related_issues' => trim($input['related_issues'] ?? ''),
    ];

    try {
        if ($id > 0) {
            $existing = Database::fetch("SELECT id FROM tools WHERE id = ?", [$id]);
            if (!$existing) { json_response(['error' => 'Not found'], 404); exit; }
            Database::update('tools', $saveData, 'id = ?', [$id]);
            Database::insert('audit_logs', [
                'user_id' => Auth::userId(), 'action' => 'UPDATE', 'resource_type' => 'tool',
                'resource_id' => $id, 'details' => json_encode(['name' => $name]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
            json_response(['success' => true, 'id' => $id]);
        } else {
            $saveData['created_at'] = date('Y-m-d H:i:s');
            $newId = Database::insert('tools', $saveData);
            Database::insert('audit_logs', [
                'user_id' => Auth::userId(), 'action' => 'CREATE', 'resource_type' => 'tool',
                'resource_id' => $newId, 'details' => json_encode(['name' => $name]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
            json_response(['success' => true, 'id' => $newId]);
        }
    } catch (Exception $e) {
        json_response(['error' => 'Failed to save: ' . $e->getMessage()], 500);
    }
} else {
    $manufacturer = trim($input['manufacturer'] ?? '');
    $model_name = trim($input['model_name'] ?? '');
    $device_type = trim($input['device_type'] ?? 'Other');

    if (empty($manufacturer) || empty($model_name)) {
        json_response(['error' => 'Manufacturer and model name are required'], 400);
        exit;
    }

    if ($equipmentTableExists) {
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
    } else {
        $manufacturerData = Database::fetch("SELECT id FROM manufacturers WHERE name = ? LIMIT 1", [$manufacturer]);
        $manufacturerId = $manufacturerData['id'] ?? Database::insert('manufacturers', ['name' => $manufacturer]);
        $saveData = [
            'manufacturer_id' => $manufacturerId,
            'manufacturer_name' => $manufacturer,
            'model' => $model_name,
            'device_type' => $device_type,
            'generation' => trim($input['year'] ?? ''),
            'specs' => json_encode(array_filter([
                trim($input['cpu'] ?? ''),
                trim($input['ram'] ?? ''),
                trim($input['storage'] ?? '')
            ])),
            'known_issues' => trim($input['known_issues'] ?? ''),
            'tools_needed' => trim($input['tools_needed'] ?? ''),
            'required_tools' => trim($input['tools_needed'] ?? ''),
        ];

        try {
            if ($id > 0) {
                $existing = Database::fetch("SELECT id FROM device_models WHERE id = ?", [$id]);
                if (!$existing) { json_response(['error' => 'Not found'], 404); exit; }
                Database::update('device_models', $saveData, 'id = ?', [$id]);
                Database::insert('audit_logs', [
                    'user_id' => Auth::userId(), 'action' => 'UPDATE', 'resource_type' => 'equipment',
                    'resource_id' => $id, 'details' => json_encode(['manufacturer' => $manufacturer, 'model' => $model_name]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]);
                json_response(['success' => true, 'id' => $id]);
            } else {
                $newId = Database::insert('device_models', $saveData);
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
    }
}
