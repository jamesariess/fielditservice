<?php
/**
 * Admin Guard — central security for all admin pages.
 *
 * Require this file at the TOP of any admin page, BEFORE the layout renders,
 * so unauthorized users never see any admin markup.
 *
 * Usage:
 *     $required_permission = 'users.manage';   // required permission key
 *     require APP_ROOT . '/includes/admin_guard.php';
 *
 * The router (public/index.php) enforces the same map, so this is defense-in-depth.
 */

if (!isset($required_permission) || !is_string($required_permission) || $required_permission === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('[Security] Admin page misconfigured: missing $required_permission.');
}

Auth::requirePermission($required_permission); // exits with 403 (HTML or JSON) when unauthorized