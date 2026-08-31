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

// Update login time to current time
$_SESSION['login_time'] = time();

// Also update the session cookie lifetime if needed
// This is handled by PHP's session mechanism

// Return success
json_response(['success' => true, 'message' => 'Session extended']);
?>