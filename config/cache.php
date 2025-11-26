<?php

declare(strict_types=1);

/**
 * Cache Configuration
 * 
 * Caching settings for the application.
 */

return [
    /**
     * Default cache driver
     */
    'default' => env('CACHE_DRIVER', 'file'),

    /**
     * Cache stores
     */
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => 'storage/cache',
        ],

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null, // Uses default
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => (int) env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int) env('REDIS_CACHE_DB', 1),
        ],
    ],

    /**
     * Cache key prefix
     */
    'prefix' => 'khairawang_cache_',

    /**
     * Default TTL (time to live) in seconds
     */
    'ttl' => 3600,

    /**
     * Specific cache durations
     */
    'durations' => [
        'products' => 1800,      // 30 minutes
        'categories' => 3600,   // 1 hour
        'settings' => 86400,    // 24 hours
        'pages' => 7200,        // 2 hours
    ],
];
