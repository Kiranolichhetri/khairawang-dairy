<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mongodb'),

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
            'uri' => env('MONGO_URI', 'mongodb://localhost:27017'),
            'database' => env('MONGO_DATABASE', 'khairawang_dairy'),
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'path' => 'database/migrations',
    ],
];