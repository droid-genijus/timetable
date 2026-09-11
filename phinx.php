<?php

declare(strict_types=1);

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => getenv('PHINX_ENVIRONMENT') ?: 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'name' => getenv('DB_DATABASE') ?: 'timetable',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: '',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'charset' => 'utf8mb4',
        ],
        'local' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'name' => getenv('DB_DATABASE') ?: 'timetable_local',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: '',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
