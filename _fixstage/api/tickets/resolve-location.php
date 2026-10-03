<?php
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }

require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Geocoder.php';

Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST required'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$ticketId = (int)($input['ticket_id'] ?? 0);
if ($ticketId < 1) {
    json_response(['error' => 'A valid ticket is required'], 422);
}

$ticket = Database::fetch(
    "SELECT id, user_id, company_name, location, address, latitude, longitude
     FROM troubleshooting_sessions WHERE id = ? LIMIT 1",
    [$ticketId]
);
if (!$ticket || !Auth::canViewTicketOwner((int)$ticket['user_id'])) {
    json_response(['error' => 'Ticket not found'], 404);
}

$lat = trim((string)($ticket['latitude'] ?? ''));
$lng = trim((string)($ticket['longitude'] ?? ''));
if ($lat !== '' && $lng !== '') {
    json_response(['success' => true, 'lat' => (float)$lat, 'lng' => (float)$lng, 'cached' => true]);
}

$address = trim((string)($ticket['address'] ?: $ticket['location']));
if ($address === '') {
    json_response(['error' => 'Add a client address or map pin to calculate ETA'], 422);
}

$clientLat = filter_var($input['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
$clientLng = filter_var($input['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
$hasClientCoordinates = $clientLat !== false && $clientLng !== false
    && $clientLat >= -90 && $clientLat <= 90 && $clientLng >= -180 && $clientLng <= 180;
$resolved = $hasClientCoordinates
    ? ['lat' => $clientLat, 'lng' => $clientLng, 'address' => $address]
    : Geocoder::forward($address);
if (!$resolved || !isset($resolved['lat'], $resolved['lng'])) {
    json_response(['error' => 'Address could not be located. Open the ticket and click the exact destination on the map.'], 404);
}

$lat = (float)$resolved['lat'];
$lng = (float)$resolved['lng'];
Database::update('troubleshooting_sessions', [
    'latitude' => $lat,
    'longitude' => $lng,
], 'id = ?', [$ticketId]);

// Keep the shared company address book useful for the next ticket too.
$company = trim((string)($ticket['company_name'] ?? ''));
if ($company !== '' && trim((string)($ticket['address'] ?? '')) !== '') {
    try {
        Database::query(
            "UPDATE locations l
             JOIN organizations o ON o.id = l.organization_id
             SET l.latitude = ?, l.longitude = ?
             WHERE LOWER(o.name) = LOWER(?) AND LOWER(l.address) = LOWER(?)",
            [$lat, $lng, $company, trim((string)$ticket['address'])]
        );
    } catch (Throwable $e) {
        // Ticket coordinates are already saved; address-book enrichment is optional.
    }
}

json_response([
    'success' => true,
    'lat' => $lat,
    'lng' => $lng,
    'address' => (string)($resolved['address'] ?? $address),
]);
