<?php

declare(strict_types=1);

return [
    // Hardcode to mongodb (env() is not loading properly)
    'default' => 'mongodb',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'khairawang_dairy'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => false,
            ],
        ],
        'mongodb' => [
            'driver' => 'mongodb',
            'uri' => 'mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/',
            'database' => 'khairawang_dairy',
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => 'database/migrations',
    ],
];
