<?php
/**
 * Backward-compatible AI database constants.
 * AI and Hub data intentionally share the main application database so the
 * project can be deployed on hosts that provide only one MySQL database.
 */
if (!defined('DB_NAME')) { require_once __DIR__ . '/app.php'; }
define('AI_DB_HOST', DB_HOST);
define('AI_DB_PORT', defined('DB_PORT') ? DB_PORT : 3306);
define('AI_DB_NAME', DB_NAME);
define('AI_DB_USER', DB_USER);
define('AI_DB_PASS', DB_PASS);
define('AI_DB_CHARSET', DB_CHARSET);
