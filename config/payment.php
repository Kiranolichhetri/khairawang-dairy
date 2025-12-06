<?php

declare(strict_types=1);

/**
 * Payment Configuration
 * 
 * Payment gateway settings (eSewa for Nepal).
 */

return [
    /**
     * Default payment gateway
     */
    'default' => env('PAYMENT_GATEWAY', 'esewa'),

    /**
     * Enable test mode
     */
    'test_mode' => env('PAYMENT_TEST_MODE', true),

    /**
     * eSewa configuration
     * 
     * Nepal's leading digital wallet payment gateway
     * 
     * Sandbox Credentials (for testing):
     * - Merchant Code: EPAYTEST
     * - Test URL: https://uat.esewa.com.np/epay/main
     * - Verify URL: https://uat.esewa.com.np/epay/transrec
     * 
     * Production Setup:
     * 1. Register at https://esewa.com.np/merchant
     * 2. Obtain merchant code and secret key
     * 3. Set PAYMENT_TEST_MODE=false in .env
     * 4. Configure ESEWA_MERCHANT_CODE and ESEWA_SECRET_KEY in .env
     */
    'esewa' => [
        /**
         * Merchant code provided by eSewa
         * Default: EPAYTEST (sandbox)
         */
        'merchant_code' => env('ESEWA_MERCHANT_CODE', 'EPAYTEST'),

        /**
         * Secret key for verification (required for production)
         * Leave empty for sandbox testing
         */
        'secret_key' => env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q'),

        /**
         * API URLs
         */
        'urls' => [
            'payment' => [
                'test' => 'https://uat.esewa.com.np/epay/main',
                'live' => 'https://esewa.com.np/epay/main',
            ],
            'verify' => [
                'test' => 'https://uat.esewa.com.np/epay/transrec',
                'live' => 'https://esewa.com.np/epay/transrec',
            ],
        ],

        /**
         * Callback URLs (relative to APP_URL)
         */
        'success_url' => env('ESEWA_SUCCESS_URL', '/payment/esewa/success'),
        'failure_url' => env('ESEWA_FAILURE_URL', '/payment/esewa/failure'),

        /**
         * Transaction timeout in seconds
         */
        'timeout' => 30,

        /**
         * Enable transaction logging
         */
        'log_transactions' => env('ESEWA_LOG_TRANSACTIONS', true),

        /**
         * Maximum retry attempts for verification
         */
        'max_verify_attempts' => 3,
    ],

    /**
     * Khalti configuration (alternative payment gateway)
     */
    'khalti' => [
        'public_key' => env('KHALTI_PUBLIC_KEY', ''),
        'secret_key' => env('KHALTI_SECRET_KEY', ''),

        'urls' => [
            'initiate' => [
                'test' => 'https://a.khalti.com/api/v2/epayment/initiate/',
                'live' => 'https://khalti.com/api/v2/epayment/initiate/',
            ],
            'verify' => [
                'test' => 'https://a.khalti.com/api/v2/epayment/lookup/',
                'live' => 'https://khalti.com/api/v2/epayment/lookup/',
            ],
        ],

        'return_url' => env('KHALTI_RETURN_URL', '/payment/khalti/callback'),
        'website_url' => env('APP_URL', 'http://localhost'),
    ],

    /**
     * Cash on Delivery (COD)
     */
    'cod' => [
        'enabled' => true,
        'max_amount' => 50000.00, // Max order amount for COD
        'extra_charge' => 0.00,
    ],

    /**
     * Supported payment methods
     */
    'methods' => [
        'esewa' => [
            'name' => 'eSewa',
            'icon' => 'esewa.png',
            'enabled' => true,
        ],
        'khalti' => [
            'name' => 'Khalti',
            'icon' => 'khalti.png',
            'enabled' => false,
        ],
        'cod' => [
            'name' => 'Cash on Delivery',
            'icon' => 'cod.png',
            'enabled' => true,
        ],
    ],
];
