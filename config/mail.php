<?php

declare(strict_types=1);

/**
 * Mail Configuration
 * 
 * Email settings for the application.
 */

return [
    /**
     * Default mailer
     */
    'default' => env('MAIL_MAILER', 'smtp'),

    /**
     * Mailer configurations
     */
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => (int) env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'timeout' => 60,
            'auth_mode' => null,
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs',
        ],

        'log' => [
            'transport' => 'log',
            'channel' => 'mail',
        ],

        'array' => [
            'transport' => 'array',
        ],
    ],

    /**
     * Global "From" address
     */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@khairawangdairy.com'),
        'name' => env('MAIL_FROM_NAME', 'KHAIRAWANG DAIRY'),
    ],

    /**
     * Reply-to address
     */
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO', 'support@khairawangdairy.com'),
        'name' => env('MAIL_REPLY_TO_NAME', 'KHAIRAWANG DAIRY Support'),
    ],

    /**
     * Email templates
     */
    'templates' => [
        'order_confirmation' => 'emails/order-confirmation',
        'order_shipped' => 'emails/order-shipped',
        'password_reset' => 'emails/password-reset',
        'email_verification' => 'emails/email-verification',
        'welcome' => 'emails/welcome',
        'newsletter' => 'emails/newsletter',
    ],

    /**
     * Queue mail by default
     */
    'queue' => env('MAIL_QUEUE', false),
];
