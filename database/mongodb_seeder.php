<?php

/**
 * MongoDB Database Seeder
 * 
 * Creates initial collections and seeds data for the Khairawang Dairy application.
 * 
 * Usage: php database/mongodb_seeder.php
 */

declare(strict_types=1);

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// Load environment variables using phpdotenv
if (!class_exists('Dotenv\Dotenv')) {
    die("❌ phpdotenv not installed. Run: composer require vlucas/phpdotenv\n");
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoUri = $_ENV['MONGO_URI'] ?? '';
$database = $_ENV['MONGO_DATABASE'] ?? 'khairawang_dairy';

if (empty($mongoUri)) {
    die("❌ MONGO_URI is empty. Check your .env file.\n");
}

echo "===========================================\n";
echo "KHAIRAWANG DAIRY - MongoDB Seeder\n";
echo "===========================================\n\n";

echo "Connecting to MongoDB...\n";

// Mask credentials in URI for display
$maskedUri = $mongoUri;
if (preg_match('#^(mongodb(?:\+srv)?://)([^:]+):([^@]+)@(.+)$#', $mongoUri, $matches)) {
    $maskedUri = $matches[1] . $matches[2] . ':****@' . $matches[4];
}
echo "URI: {$maskedUri}\n";
echo "Database: {$database}\n\n";

try {
    $client = new Client($mongoUri);
    $db = $client->selectDatabase($database);
    
    echo "Connected successfully!\n\n";
    
    // =========================================
    // Seed Roles Collection
    // =========================================
    echo "Seeding roles collection...\n";
    
    $rolesCollection = $db->selectCollection('roles');
    $rolesCollection->drop();
    
    $roles = [
        [
            'name' => 'admin',
            'permissions' => ['*'],
            'created_at' => new UTCDateTime(),
        ],
        [
            'name' => 'manager',
            'permissions' => [
                'view_products',
                'manage_products',
                'view_orders',
                'manage_orders',
                'view_customers',
                'view_reports'
            ],
            'created_at' => new UTCDateTime(),
        ],
        [
            'name' => 'staff',
            'permissions' => [
                'view_products',
                'view_orders',
                'update_order_status',
                'view_customers'
            ],
            'created_at' => new UTCDateTime(),
        ],
        [
            'name' => 'customer',
            'permissions' => [
                'view_products',
                'place_order',
                'view_own_orders',
                'update_profile'
            ],
            'created_at' => new UTCDateTime(),
        ],
    ];
    
    $result = $rolesCollection->insertMany($roles);
    echo "  Inserted " . count($result->getInsertedIds()) . " roles\n";
    
    $adminRole = $rolesCollection->findOne(['name' => 'admin']);
    $adminRoleId = $adminRole['_id'];
    
    // =========================================
    // Seed Admin User
    // =========================================
    echo "\nSeeding admin user...\n";
    
    $usersCollection = $db->selectCollection('users');
    $existingAdmin = $usersCollection->findOne(['email' => 'admin@khairawangdairy.com']);
    $defaultAdminPassword = 'admin123';
    
    if ($existingAdmin) {
        echo "  Admin user already exists, updating...\n";
        $usersCollection->updateOne(
            ['email' => 'admin@khairawangdairy.com'],
            ['$set' => [
                'role_id' => (string) $adminRoleId,
                'password' => password_hash($defaultAdminPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3,
                ]),
                'status' => 'active',
                'updated_at' => new UTCDateTime(),
            ]]
        );
        echo "  Admin user updated\n";
    } else {
        $adminUser = [
            'role_id' => (string) $adminRoleId,
            'email' => 'admin@khairawangdairy.com',
            'password' => password_hash($defaultAdminPassword, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3,
            ]),
            'name' => 'Admin User',
            'phone' => '+977-9800000000',
            'avatar' => null,
            'google_id' => null,
            'email_verified_at' => new UTCDateTime(),
            'status' => 'active',
            'remember_token' => null,
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ];
        
        $result = $usersCollection->insertOne($adminUser);
        echo "  Created admin user with ID: " . $result->getInsertedId() . "\n";
    }
    
    // =========================================
    // Seed Default Settings
    // =========================================
    echo "\nSeeding settings collection...\n";
    
    $settingsCollection = $db->selectCollection('settings');
    
    $settings = [
        ['key' => 'site_name', 'value' => 'KHAIRAWANG DAIRY', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_tagline', 'value' => 'Premium Dairy Products', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_email', 'value' => 'info@khairawangdairy.com', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_phone', 'value' => '+977-9800000000', 'type' => 'string', 'group' => 'general'],
        ['key' => 'site_address', 'value' => 'Kathmandu, Nepal', 'type' => 'string', 'group' => 'general'],
        ['key' => 'currency_code', 'value' => 'NPR', 'type' => 'string', 'group' => 'general'],
        ['key' => 'currency_symbol', 'value' => 'Rs.', 'type' => 'string', 'group' => 'general'],
        ['key' => 'shipping_cost', 'value' => '100', 'type' => 'integer', 'group' => 'shipping'],
        ['key' => 'free_shipping_threshold', 'value' => '1000', 'type' => 'integer', 'group' => 'shipping'],
        ['key' => 'tax_rate', 'value' => '13', 'type' => 'integer', 'group' => 'tax'],
        ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],
    ];
    
    $insertedCount = 0;
    foreach ($settings as $setting) {
        $existing = $settingsCollection->findOne(['key' => $setting['key']]);
        if (!$existing) {
            $setting['created_at'] = new UTCDateTime();
            $setting['updated_at'] = new UTCDateTime();
            $settingsCollection->insertOne($setting);
            $insertedCount++;
        }
    }
    echo "  Inserted {$insertedCount} settings\n";
    
    echo "\n===========================================\n";
    echo "Database seeding completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Admin Login Credentials:\n";
    echo "  Email: admin@khairawangdairy.com\n";
    echo "  Password: admin123\n";
    echo "\n  IMPORTANT: Change this password immediately\n";
    echo "  after first login in production!\n\n";
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
