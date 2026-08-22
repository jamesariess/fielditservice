<?php
/**
 * API: Toggle Favorite
 * POST /api/favorites/toggle.php
 * Body: { "item_type": "troubleshooting", "item_id": 1 }
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$itemType = $input['item_type'] ?? '';
$itemId = (int)($input['item_id'] ?? 0);

if (empty($itemType) || !$itemId) {
    json_response(['error' => 'item_type and item_id required'], 400);
    exit;
}

try {
    // Check if already favorited
    $existing = Database::fetch(
        "SELECT id FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?",
        [Auth::userId(), $itemType, $itemId]
    );

    if ($existing) {
        Database::delete('favorites', 'id = ?', [$existing['id']]);
        json_response(['success' => true, 'favorited' => false]);
    } else {
        Database::insert('favorites', [
            'user_id' => Auth::userId(),
            'item_type' => $itemType,
            'item_id' => $itemId,
        ]);
        json_response(['success' => true, 'favorited' => true]);
    }
} catch (Exception $e) {
    json_response(['error' => 'Failed to toggle favorite'], 500);
}
