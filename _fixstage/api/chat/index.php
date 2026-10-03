<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { json_response(['error' => 'GET required'], 405); exit; }
$id = (int)($_GET['conversation_id'] ?? 0);
if (!$id) { json_response(['error' => 'conversation_id required'], 400); exit; }
$member = Database::fetch('SELECT id FROM chat_participants WHERE conversation_id = ? AND user_id = ?', [$id, Auth::userId()]);
if (!$member) { json_response(['error' => 'Not a member of this conversation'], 403); exit; }
$messages = Database::fetchAll("SELECT cm.id, cm.content, cm.created_at, cm.user_id, u.full_name, DATE_FORMAT(cm.created_at, '%l:%i %p') AS time FROM chat_messages cm INNER JOIN users u ON u.id = cm.user_id WHERE cm.conversation_id = ? ORDER BY cm.created_at ASC, cm.id ASC", [$id]);
$participants = Database::fetch('SELECT COUNT(*) AS total FROM chat_participants WHERE conversation_id = ?', [$id]);
json_response(['success' => true, 'messages' => $messages, 'participant_count' => (int)($participants['total'] ?? 0)]);
