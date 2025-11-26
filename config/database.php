<?php

declare(strict_types=1);

/**
 * Database Configuration
 * 
 * Database connection settings.
 */

return [
    /**
     * Default database connection
     */
    'default' => env('DB_CONNECTION', 'mysql'),

    /**
     * Database connections
     */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'khairawang_dairy'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'options' => [
                \PDO::ATTR_PERSISTENT => false,
            ],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', 'database/database.sqlite'),
            'prefix' => '',
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int) env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'khairawang_dairy'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],
    ],

    /**
     * Migration settings
     */
    'migrations' => [
        'table' => 'migrations',
        'path' => 'database/migrations',
    ],
];
