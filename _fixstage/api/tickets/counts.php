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
    $scope = Auth::canViewAllTickets() ? '' : ' AND user_id = ' . (int)Auth::userId();
    $pending = Database::count('troubleshooting_sessions', "status IN ('new', 'in_progress', 'escalated')" . $scope);
    $resolved = Database::count('troubleshooting_sessions', "status IN ('solved', 'partial')" . $scope);
    
    json_response([
        'success' => true,
        'pending' => $pending,
        'resolved' => $resolved
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Failed to fetch ticket counts'], 500);
}
?>
