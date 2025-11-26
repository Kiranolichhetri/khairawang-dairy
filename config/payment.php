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
     */
    'esewa' => [
        /**
         * Merchant code provided by eSewa
         */
        'merchant_code' => env('ESEWA_MERCHANT_CODE', 'EPAYTEST'),

        /**
         * Secret key for verification
         */
        'secret_key' => env('ESEWA_SECRET_KEY', ''),

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
         * Callback URLs
         */
        'success_url' => env('ESEWA_SUCCESS_URL', '/payment/success'),
        'failure_url' => env('ESEWA_FAILURE_URL', '/payment/failure'),
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
