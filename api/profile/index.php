<?php
/**
 * Profile API - update user profile
 */
require_once dirname(dirname(__DIR__)) . '/config/app.php';
require_once dirname(dirname(__DIR__)) . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) {
    require_once APP_ROOT . '/includes/Database.php';
}
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); }
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';
$company = trim((string)($input['company_name'] ?? ''));
$locationName = trim((string)($input['location_name'] ?? ''));
$address = trim((string)($input['address'] ?? ''));
$latitude = trim((string)($input['latitude'] ?? ''));
$longitude = trim((string)($input['longitude'] ?? ''));

if ($company === '') { json_response(['error' => 'Company is required for ticket routing.'], 422); }
if ($address === '') { json_response(['error' => 'Company address is required for ticket routing.'], 422); }
if (!is_numeric($latitude) || !is_numeric($longitude) || (float)$latitude < -90 || (float)$latitude > 90 || (float)$longitude < -180 || (float)$longitude > 180) {
    json_response(['error' => 'Set a valid company location on the map before saving.'], 422);
}

$demo = !defined('DEMO_MODE') || DEMO_MODE;
if (!$demo) {
    $pdo = Database::getInstance();
    try {
        $pdo->beginTransaction();
        $sets = [];
        $vals = [];
        if ($name) { $sets[] = "full_name = ?"; $vals[] = $name; }
        if ($email) { $sets[] = "email = ?"; $vals[] = $email; }
        if ($phone) { $sets[] = "phone = ?"; $vals[] = $phone; }
        $locationId = null;
        if ($company !== '' && $address !== '') {
            $org = Database::fetch("SELECT id FROM organizations WHERE LOWER(name) = LOWER(?) LIMIT 1", [$company]);
            $orgId = $org ? (int)$org['id'] : (int)Database::insert('organizations', ['name' => $company]);
            $loc = Database::fetch(
                "SELECT id FROM locations WHERE organization_id = ? AND LOWER(address) = LOWER(?) LIMIT 1",
                [$orgId, $address]
            );
            $locData = [
                'organization_id' => $orgId,
                'name' => $locationName !== '' ? $locationName : mb_substr($address, 0, 100),
                'address' => $address,
                'latitude' => $latitude !== '' ? $latitude : null,
                'longitude' => $longitude !== '' ? $longitude : null,
            ];
            if ($loc) {
                $locationId = (int)$loc['id'];
                Database::update('locations', $locData, 'id = ?', [$locationId]);
            } else {
                $locationId = (int)Database::insert('locations', $locData);
            }
        }
        if ($locationId) { $sets[] = "location_id = ?"; $vals[] = $locationId; }
        if (!empty($sets)) {
            $vals[] = Auth::userId();
            Database::query("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
        }
        $pdo->commit();
        if ($name !== '') { $_SESSION['user_name'] = $name; }
        json_response([
            'success' => true,
            'message' => 'Profile updated',
            'location_id' => $locationId,
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        json_response(['error' => $e->getMessage()], 500);
    }
} else {
    json_response(['success' => true, 'message' => 'Profile updated', 'demo' => true]);
}
