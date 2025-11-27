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
    'default' => 'mysql',

    /**
     * Database connections
     */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'khairawang_dairy',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => false,
            ],
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'database/database.sqlite',
            'prefix' => '',
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0. 1',
            'port' => 5432,
            'database' => 'khairawang_dairy',
            'username' => 'postgres',
            'password' => '',
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