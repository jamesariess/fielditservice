<?php
if (!defined('APP_ROOT')) define('APP_ROOT',dirname(__DIR__,2));
require_once APP_ROOT.'/config/app.php';
require_once APP_ROOT.'/config/demo.php';
require_once APP_ROOT.'/includes/helpers.php';
require_once APP_ROOT.'/includes/Database.php';
require_once APP_ROOT.'/includes/Auth.php';
require_once APP_ROOT.'/includes/DashboardData.php';
Auth::requirePermission('dashboard.view');
header('Cache-Control: no-store, private');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error'=>'Method not allowed'],405);
if (defined('DEMO_MODE') && DEMO_MODE) json_response(['error'=>'Live dashboard is unavailable in demo mode'],503);
try {
    json_response(DashboardData::load());
} catch (Throwable $e) {
    error_log('Dashboard refresh failed: '.$e->getMessage());
    json_response(['error'=>'Dashboard data could not be refreshed. Please retry.'],503);
}
