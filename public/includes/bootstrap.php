<?php

declare(strict_types=1);

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
