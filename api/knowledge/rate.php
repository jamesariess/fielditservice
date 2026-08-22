<?php
/**
 * API: Knowledge Article Rating
 * POST /api/knowledge/rate.php - rate an article
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(dirname(__DIR__)))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = Auth::userId();
$articleId = (int)($input['article_id'] ?? 0);
$rating = $input['rating'] ?? null;
$solved = $input['solved'] ?? null;
$feedback = sanitize($input['feedback'] ?? '');

if (!$articleId || !$rating) {
    json_response(['error' => 'article_id and rating required'], 400);
}

if (!in_array($rating, ['helpful', 'not_helpful', 'solved', 'partial', 'no'])) {
    json_response(['error' => 'Invalid rating'], 400);
}

if (defined('DEMO_MODE') && DEMO_MODE) {
    json_response(['success' => true]);
}

// Upsert rating
$existing = Database::fetch("SELECT id FROM knowledge_ratings WHERE article_id = ? AND user_id = ?", [$articleId, $userId]);

if ($existing) {
    Database::update('knowledge_ratings', [
        'rating' => $rating,
        'solved' => $solved,
        'feedback' => $feedback,
    ], 'id = ?', [$existing['id']]);
} else {
    Database::insert('knowledge_ratings', [
        'article_id' => $articleId,
        'user_id' => $userId,
        'rating' => $rating,
        'solved' => $solved,
        'feedback' => $feedback,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

// Update article counters
$useCountUpdate = 'UPDATE knowledge_articles SET use_count = use_count + 1 WHERE id = ?';
Database::query($useCountUpdate, [$articleId]);

if ($rating === 'helpful' || $rating === 'solved') {
    Database::query("UPDATE knowledge_articles SET helpful_count = helpful_count + 1 WHERE id = ?", [$articleId]);
    if ($rating === 'solved' || $solved === 'yes') {
        Database::query("UPDATE knowledge_articles SET success_count = success_count + 1 WHERE id = ?", [$articleId]);
    }
} elseif ($rating === 'not_helpful' || $rating === 'no') {
    Database::query("UPDATE knowledge_articles SET not_helpful_count = not_helpful_count + 1 WHERE id = ?", [$articleId]);
}

json_response(['success' => true]);
