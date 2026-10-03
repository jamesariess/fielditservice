<?php
require_once dirname(__DIR__) . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Geocoder.php';

Auth::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error' => 'GET required'], 405);
$query = trim((string)($_GET['q'] ?? ''));
if ($query === '') json_response(['error' => 'Address is required'], 422);
if (mb_strlen($query) > 1000) json_response(['error' => 'Address is too long'], 422);

$result = Geocoder::forward($query);
if (!$result) json_response(['error' => 'Address was not found. Try a Google Maps link or click the map to set the pin.'], 404);
json_response(['success' => true] + $result);
