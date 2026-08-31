<?php
/**
 * Heartbeat API - keeps session alive during active use
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['error' => 'Method not allowed'], 405);
    exit;
}

// Require authentication
Auth::requireLogin();

// Just return success - the session activity is already tracked by Auth::requireLogin()
// being called in the API route handling
json_response(['success' => true, 'timestamp' => time()]);
?>