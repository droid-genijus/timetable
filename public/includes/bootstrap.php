<?php

declare(strict_types=1);

// Log every error/exception (incl. uncaught DB errors below) to a file
// outside the public docroot's reach - public/config/ is .htaccess-denied -
// instead of letting them surface as a bare 500 with nothing in the response.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../config/error.log');

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

$config = require __DIR__ . '/../config/config.php';
$isProduction = ($config['app_env'] ?? 'local') === 'production';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $isProduction,
    'samesite' => 'Lax',
]);
session_name('timetable_session');
session_start();
