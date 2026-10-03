<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/Database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/TicketRouteOrigin.php';
Auth::start();
Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error'=>'GET required'], 405);
json_response(['origin'=>TicketRouteOrigin::forUser((int)Auth::userId())]);
