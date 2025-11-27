<?php

declare(strict_types=1);

/**
 * Application Configuration
 * 
 * General application settings.
 */

return [
    /**
     * Application name
     */
    'name' => env('APP_NAME', 'KHAIRAWANG DAIRY'),

    /**
     * Application environment (local, staging, production)
     */
    'env' => env('APP_ENV', 'production'),

    /**
     * Debug mode - NEVER enable in production!
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Application URL
     */
    'url' => env('APP_URL', 'http://localhost'),

    /**
     * Timezone
     */
    'timezone' => 'Asia/Kathmandu',

    /**
     * Default locale
     */
    'locale' => 'en',

    /**
     * Supported locales
     */
    'locales' => ['en', 'ne'],

    /**
     * Encryption key (32 characters minimum)
     */
    'key' => env('APP_KEY', ''),

    /**
     * Assets version (for cache busting)
     */
    'asset_version' => '1.0.0',

    /**
     * Currency settings
     */
    'currency' => [
        'code' => 'NPR',
        'symbol' => 'Rs.',
        'decimals' => 2,
    ],

    /**
     * Company information
     */
    'company' => [
        'name' => 'KHAIRAWANG DAIRY',
        'email' => 'info@khairawangdairy.com',
        'phone' => '+977-9800000000',
        'address' => 'Kathmandu, Nepal',
    ],

    /**
     * Pagination defaults
     */
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /**
     * Upload settings
     */
    'uploads' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'path' => 'uploads',
    ],
];
