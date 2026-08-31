<?php
/**
 * Knowledge Base Stats API
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    json_response(['error' => 'Method not allowed'], 405);
    exit;
}

// Require authentication
Auth::requireLogin();

try {
    $totalArticles = Database::count('knowledge_articles');
    $pendingReview = Database::count('knowledge_articles', "status = 'pending'");
    
    json_response([
        'success' => true,
        'articles' => $totalArticles,
        'pending_review' => $pendingReview
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to fetch KB stats'], 500);
}
?>