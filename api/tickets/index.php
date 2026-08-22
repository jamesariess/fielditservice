<?php
/**
 * API: Tickets / Troubleshooting Sessions
 * GET /api/tickets - list user tickets
 * POST /api/tickets - create new ticket
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

$userId = Auth::userId();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $status = $_GET['status'] ?? null;

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['tickets' => [], 'total' => 0]);
    }

    $sql = "SELECT t.*, u.full_name as user_name FROM tickets t LEFT JOIN users u ON u.id = t.user_id WHERE t.user_id = ?";
    $params = [$userId];

    if ($status) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY t.created_at DESC LIMIT 50";
    $tickets = Database::fetchAll($sql, $params);
    json_response(['tickets' => $tickets, 'total' => count($tickets)]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $title = sanitize($input['title'] ?? '');
    $description = sanitize($input['description'] ?? '');
    $priority = $input['priority'] ?? 'medium';
    $deviceType = sanitize($input['device_type'] ?? '');
    $model = sanitize($input['model'] ?? '');
    $serial = sanitize($input['serial_number'] ?? '');
    $customerName = sanitize($input['customer_name'] ?? '');
    $location = sanitize($input['location'] ?? '');

    if (empty($title)) {
        json_response(['error' => 'Title is required'], 400);
    }

    if (defined('DEMO_MODE') && DEMO_MODE) {
        json_response(['success' => true, 'ticket' => ['id' => 1, 'ticket_number' => 'TK-0001', 'title' => $title]]);
    }

    $ticketNumber = 'TK-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $sessionId = Database::insert('troubleshooting_sessions', [
        'user_id' => $userId,
        'customer_name' => $customerName,
        'location' => $location,
        'device_type' => $deviceType,
        'model' => $model,
        'serial_number' => $serial,
        'problem_description' => $description,
        'priority' => $priority,
        'status' => 'new',
        'started_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $ticketId = Database::insert('tickets', [
        'ticket_number' => $ticketNumber,
        'session_id' => $sessionId,
        'user_id' => $userId,
        'title' => $title,
        'description' => $description,
        'priority' => $priority,
        'status' => 'open',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    // Create notification for supervisor
    Database::insert('notifications', [
        'user_id' => 1,
        'type' => 'new_ticket',
        'title' => 'New Ticket: ' . $ticketNumber,
        'message' => $title,
        'url' => '/tickets',
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    // Audit log
    Database::insert('audit_logs', [
        'user_id' => $userId,
        'action' => 'CREATE',
        'resource_type' => 'ticket',
        'resource_id' => $ticketId,
        'details' => json_encode(['ticket_number' => $ticketNumber]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    json_response(['success' => true, 'ticket' => ['id' => $ticketId, 'ticket_number' => $ticketNumber, 'title' => $title]]);
}

json_response(['error' => 'Method not allowed'], 405);
