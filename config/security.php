<?php

declare(strict_types=1);

/**
 * Security Configuration
 * 
 * Security-related settings for the application.
 */

return [
    /**
     * Password hashing
     */
    'passwords' => [
        /**
         * Hashing algorithm
         * Argon2id is recommended for PHP 8.2+
         */
        'algorithm' => PASSWORD_ARGON2ID,

        /**
         * Argon2id options
         */
        'options' => [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,
            'threads' => 3,
        ],

        /**
         * Minimum password length
         */
        'min_length' => 8,

        /**
         * Require complexity (uppercase, lowercase, number)
         */
        'require_complexity' => true,
    ],

    /**
     * Session security
     */
    'session' => [
        'name' => 'KHAIRAWANG_SESSION',
        'lifetime' => 7200,     // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => env('SESSION_SECURE', true),
        'httponly' => true,
        'samesite' => 'Lax',
        'driver' => 'file',     // file, database
        'table' => 'sessions',
    ],

    /**
     * CSRF protection
     */
    'csrf' => [
        'enabled' => true,
        'token_lifetime' => 7200,   // 2 hours
        'excluded_uris' => [
            '/api/webhook/*',
            '/api/payment/callback',
        ],
    ],

    /**
     * Rate limiting
     */
    'rate_limiting' => [
        'enabled' => true,

        /**
         * Default rate limits
         */
        'limits' => [
            'api' => [
                'max_attempts' => 60,
                'decay_seconds' => 60,
            ],
            'login' => [
                'max_attempts' => 5,
                'decay_seconds' => 300,     // 5 minutes
            ],
            'register' => [
                'max_attempts' => 3,
                'decay_seconds' => 3600,    // 1 hour
            ],
            'password_reset' => [
                'max_attempts' => 3,
                'decay_seconds' => 3600,
            ],
            'contact' => [
                'max_attempts' => 5,
                'decay_seconds' => 3600,
            ],
        ],
    ],

    /**
     * XSS protection
     */
    'xss' => [
        /**
         * Auto-escape output in views
         */
        'auto_escape' => true,

        /**
         * HTML Purifier for rich text
         */
        'purifier' => [
            'allowed_tags' => ['p', 'br', 'b', 'i', 'u', 'strong', 'em', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
            'allowed_attributes' => ['a.href', 'a.title'],
        ],
    ],

    /**
     * SQL injection prevention
     */
    'sql' => [
        /**
         * Always use prepared statements (enforced by framework)
         */
        'prepared_statements' => true,
    ],

    /**
     * Content Security Policy
     */
    'csp' => [
        'enabled' => env('CSP_ENABLED', false),
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "https://cdn.jsdelivr.net"],
            'style-src' => ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
            'font-src' => ["'self'", "https://fonts.gstatic.com"],
            'img-src' => ["'self'", "data:", "https:"],
            'connect-src' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
        ],
    ],

    /**
     * Security headers
     */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /**
     * File upload security
     */
    'uploads' => [
        /**
         * Allowed MIME types
         */
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],

        /**
         * Max file size in bytes (5MB)
         */
        'max_size' => 5 * 1024 * 1024,

        /**
         * Sanitize filenames
         */
        'sanitize_filename' => true,

        /**
         * Generate random filename
         */
        'random_filename' => true,
    ],

    /**
     * API security
     */
    'api' => [
        /**
         * Enable API key authentication
         */
        'key_auth' => false,

        /**
         * API key header name
         */
        'key_header' => 'X-API-Key',

        /**
         * Enable JWT authentication
         */
        'jwt_enabled' => false,

        /**
         * JWT settings
         */
        'jwt' => [
            'secret' => env('JWT_SECRET', ''),
            'algorithm' => 'HS256',
            'lifetime' => 3600,     // 1 hour
            'refresh_lifetime' => 604800,   // 7 days
        ],
    ],

    /**
     * Trusted proxies (for load balancers)
     */
    'trusted_proxies' => [
        // Add your proxy IPs here
    ],

    /**
     * Logging security events
     */
    'logging' => [
        'failed_logins' => true,
        'suspicious_activity' => true,
        'admin_actions' => true,
    ],
];
