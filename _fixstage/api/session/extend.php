<?php
/**
 * Session Extension API
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['error' => 'Method not allowed'], 405);
    exit;
}

// Require authentication
Auth::requireLogin();

// Refresh the IDLE timer only. login_time (absolute lifetime) is intentionally
// left untouched — a user cannot extend the absolute 8h cap, only the idle window.
$_SESSION['last_activity'] = time();

// Return success
json_response(['success' => true, 'message' => 'Session extended']);
?>