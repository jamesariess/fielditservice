<?php
/**
 * API: Favorites
 * GET /api/favorites - list user favorites
 * POST /api/favorites - toggle favorite
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = Auth::userId();
    $type = $_GET['type'] ?? null;

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['favorites' => []]);
    }

    if ($type) {
        $favs = Database::fetchAll("SELECT * FROM favorites WHERE user_id = ? AND item_type = ?", [$userId, $type]);
    } else {
        $favs = Database::fetchAll("SELECT * FROM favorites WHERE user_id = ?", [$userId]);
    }
    json_response(['favorites' => $favs]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = Auth::userId();
    $itemType = $input['item_type'] ?? '';
    $itemId = (int)($input['item_id'] ?? 0);

    if (!$itemType || !$itemId) {
        json_response(['error' => 'item_type and item_id required'], 400);
    }

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['success' => true, 'favorited' => true]);
    }

    // Toggle: check if exists
    $existing = Database::fetch("SELECT id FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?", [$userId, $itemType, $itemId]);

    if ($existing) {
        Database::delete('favorites', 'id = ?', [$existing['id']]);
        json_response(['success' => true, 'favorited' => false]);
    } else {
        Database::insert('favorites', [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        json_response(['success' => true, 'favorited' => true]);
    }
}

json_response(['error' => 'Method not allowed'], 405);
