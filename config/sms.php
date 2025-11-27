<?php

declare(strict_types=1);

/**
 * SMS Configuration
 * 
 * SMS gateway settings for Nepal SMS providers.
 */

return [
    /**
     * Default SMS driver
     */
    'default' => env('SMS_DRIVER', 'sparrow'),

    /**
     * SMS drivers configuration
     */
    'drivers' => [
        'sparrow' => [
            'token' => env('SPARROW_SMS_TOKEN', ''),
            'from' => env('SPARROW_SMS_FROM', 'KhairawangDairy'),
            'url' => 'https://api.sparrowsms.com/v2/sms/',
        ],

        'aakash' => [
            'token' => env('AAKASH_SMS_TOKEN', ''),
            'from' => env('AAKASH_SMS_FROM', ''),
            'url' => 'https://aakashsms.com/api/v3/send_sms',
        ],

        'log' => [
            'channel' => 'sms',
        ],
    ],

    /**
     * Default sender ID
     */
    'from' => env('SMS_FROM', 'KhairawangDairy'),

    /**
     * Enable SMS notifications
     */
    'enabled' => env('SMS_ENABLED', true),

    /**
     * OTP settings
     */
    'otp' => [
        'length' => 6,
        'expiry' => 5, // minutes
    ],
];
