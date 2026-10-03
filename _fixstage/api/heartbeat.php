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

// A heartbeat is only sent when the user has actively interacted with the page,
// so it legitimately counts as activity for the idle timeout.
$_SESSION['last_activity'] = time();

json_response(['success' => true, 'timestamp' => time()]);
?>