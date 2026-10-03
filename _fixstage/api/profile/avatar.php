<?php
/**
 * Profile picture API — POST /api/profile/avatar
 *
 * Multipart form data:
 *   avatar    the image file (JPG, PNG, WebP or GIF, max 8 MB)
 *   _csrf     CSRF token (the router checks the same token for every POST)
 * or
 *   remove=1  delete the current picture instead
 *
 * The file is stored in public/uploads/avatars/ and its URL is remembered on
 * users.avatar_url — the column the app already ships with — so the picture
 * appears in the header avatar, the sidebar block and the profile page.
 * The previous picture is deleted so the folder does not fill up with orphans.
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Activity.php';
Auth::start();
Auth::requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); }

$avatarUserId = (int)Auth::userId();
$avatarDir    = APP_ROOT . '/public/uploads/avatars';
$avatarDemo   = !defined('DEMO_MODE') || DEMO_MODE;

/**
 * Delete a previously stored picture — only one of ours. A URL pointing
 * anywhere else (grabbed from another site) is left alone.
 */
function avatarDeletePrevious(?string $url): void
{
    if (!$url) { return; }
    $path = (string)(parse_url($url, PHP_URL_PATH) ?: '');
    if (strpos($path, '/uploads/avatars/') === false) { return; }
    $file = APP_ROOT . '/public/uploads/avatars/' . basename($path);
    if (is_file($file)) { @unlink($file); }
}

if (!empty($_POST['remove'])) {
    if (!$avatarDemo) {
        try {
            $previous = Database::fetch("SELECT avatar_url FROM users WHERE id = ?", [$avatarUserId]);
            Database::query("UPDATE users SET avatar_url = NULL WHERE id = ?", [$avatarUserId]);
            avatarDeletePrevious($previous['avatar_url'] ?? null);
            Activity::log('UPDATE', 'profile_avatar', $avatarUserId, ['removed' => true]);
        } catch (Throwable $e) {
            json_response(['error' => 'Could not remove the picture: ' . $e->getMessage()], 500);
        }
    }
    Auth::setAvatar(null);
    json_response(['success' => true, 'url' => null, 'message' => 'Profile picture removed.']);
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'No picture was uploaded (error code ' . ($_FILES['avatar']['error'] ?? 'unknown') . ').'], 400);
}

$avatarFile    = $_FILES['avatar'];
$avatarMaxSize = 8 * 1024 * 1024;
$avatarMimes   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

if ($avatarFile['size'] > $avatarMaxSize) {
    json_response(['error' => 'That picture is larger than 8 MB. Pick a smaller one.'], 400);
}

// Sniff the real type from the bytes, never from the client's file name.
$avatarMime = null;
if (function_exists('finfo_open')) {
    $avatarFinfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($avatarFinfo) { $avatarMime = finfo_file($avatarFinfo, $avatarFile['tmp_name']); finfo_close($avatarFinfo); }
}
if ($avatarMime === null) { $avatarMime = (string)$avatarFile['type']; }
if (!isset($avatarMimes[$avatarMime])) {
    json_response(['error' => 'That file is not an image. Use JPG, PNG, WebP or GIF.'], 400);
}

if (!is_dir($avatarDir) && !@mkdir($avatarDir, 0755, true) && !is_dir($avatarDir)) {
    json_response(['error' => 'The uploads/avatars folder could not be created on the server.'], 500);
}

$avatarName = 'avatar_' . $avatarUserId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $avatarMimes[$avatarMime];
$avatarPath = $avatarDir . '/' . $avatarName;
if (!move_uploaded_file($avatarFile['tmp_name'], $avatarPath)) {
    json_response(['error' => 'The picture could not be saved on the server.'], 500);
}
@chmod($avatarPath, 0644);

// app_base() keeps the stored URL correct on a sub-folder install and on a
// domain-root install like InfinityFree.
$avatarUrl = app_base() . 'uploads/avatars/' . $avatarName;

if (!$avatarDemo) {
    try {
        $previous = Database::fetch("SELECT avatar_url FROM users WHERE id = ?", [$avatarUserId]);
        Database::query("UPDATE users SET avatar_url = ? WHERE id = ?", [$avatarUrl, $avatarUserId]);
        avatarDeletePrevious($previous['avatar_url'] ?? null);
        Activity::log('UPDATE', 'profile_avatar', $avatarUserId, ['file' => $avatarName, 'size' => (int)$avatarFile['size']]);
    } catch (Throwable $e) {
        @unlink($avatarPath);
        json_response(['error' => 'The picture was uploaded but could not be saved to your account: ' . $e->getMessage()], 500);
    }
}

Auth::setAvatar($avatarUrl);
json_response([
    'success' => true,
    'url'     => $avatarUrl,
    'message' => 'Profile picture updated.',
]);
