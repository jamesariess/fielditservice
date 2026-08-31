<?php
/**
 * Ticket Counts API
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    json_response(['error' => 'Method not allowed'], 405);
    exit;
}

// Require authentication
Auth::requireLogin();

try {
    $pending = Database::count('troubleshooting_sessions', "result IS NULL OR result = 'in_progress'");
    $resolved = Database::count('troubleshooting_sessions', "result = 'solved'");
    
    json_response([
        'success' => true,
        'pending' => $pending,
        'resolved' => $resolved
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to fetch ticket counts'], 500);
}
?>