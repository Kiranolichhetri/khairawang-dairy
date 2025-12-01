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

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Remove quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// Get MongoDB connection details from environment
$mongoUri = $_ENV['MONGO_URI'] ?? 'mongodb://localhost:27017';
$database = $_ENV['MONGO_DATABASE'] ?? 'khairawang_dairy';

echo "===========================================\n";
echo "KHAIRAWANG DAIRY - MongoDB Seeder\n";
echo "===========================================\n\n";

echo "Connecting to MongoDB...\n";
echo "URI: " . preg_replace('/:[^:@]+@/', ':****@', $mongoUri) . "\n";
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
    
    // Drop existing roles
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
    
    // Get admin role ID
    $adminRole = $rolesCollection->findOne(['name' => 'admin']);
    $adminRoleId = $adminRole['_id'];
    
    // =========================================
    // Seed Admin User
    // =========================================
    echo "\nSeeding admin user...\n";
    
    $usersCollection = $db->selectCollection('users');
    
    // Check if admin already exists
    $existingAdmin = $usersCollection->findOne(['email' => 'admin@khairawangdairy.com']);
    
    if ($existingAdmin) {
        echo "  Admin user already exists, updating...\n";
        $usersCollection->updateOne(
            ['email' => 'admin@khairawangdairy.com'],
            ['$set' => [
                'role_id' => (string) $adminRoleId,
                'password' => password_hash('admin123', PASSWORD_ARGON2ID, [
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
            'password' => password_hash('admin123', PASSWORD_ARGON2ID, [
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
    
    // =========================================
    // Create Indexes
    // =========================================
    echo "\nCreating indexes...\n";
    
    // Users indexes
    $usersCollection->createIndex(['email' => 1], ['unique' => true]);
    $usersCollection->createIndex(['status' => 1]);
    $usersCollection->createIndex(['role_id' => 1]);
    $usersCollection->createIndex(['google_id' => 1]);
    echo "  Created users indexes\n";
    
    // Products indexes
    $productsCollection = $db->selectCollection('products');
    $productsCollection->createIndex(['slug' => 1], ['unique' => true]);
    $productsCollection->createIndex(['status' => 1]);
    $productsCollection->createIndex(['category_id' => 1]);
    $productsCollection->createIndex(['featured' => 1]);
    $productsCollection->createIndex(['deleted_at' => 1]);
    $productsCollection->createIndex(
        ['name_en' => 'text', 'name_ne' => 'text', 'description_en' => 'text'],
        ['name' => 'product_search']
    );
    echo "  Created products indexes\n";
    
    // Orders indexes
    $ordersCollection = $db->selectCollection('orders');
    $ordersCollection->createIndex(['order_number' => 1], ['unique' => true]);
    $ordersCollection->createIndex(['user_id' => 1]);
    $ordersCollection->createIndex(['status' => 1]);
    $ordersCollection->createIndex(['payment_status' => 1]);
    $ordersCollection->createIndex(['created_at' => -1]);
    echo "  Created orders indexes\n";
    
    // Categories indexes
    $categoriesCollection = $db->selectCollection('categories');
    $categoriesCollection->createIndex(['slug' => 1], ['unique' => true]);
    $categoriesCollection->createIndex(['status' => 1]);
    $categoriesCollection->createIndex(['parent_id' => 1]);
    echo "  Created categories indexes\n";
    
    // Carts indexes
    $cartsCollection = $db->selectCollection('carts');
    $cartsCollection->createIndex(['user_id' => 1]);
    $cartsCollection->createIndex(['session_id' => 1]);
    echo "  Created carts indexes\n";
    
    // Reviews indexes
    $reviewsCollection = $db->selectCollection('reviews');
    $reviewsCollection->createIndex(['product_id' => 1]);
    $reviewsCollection->createIndex(['user_id' => 1]);
    $reviewsCollection->createIndex(['status' => 1]);
    echo "  Created reviews indexes\n";
    
    // Settings indexes
    $settingsCollection->createIndex(['key' => 1], ['unique' => true]);
    $settingsCollection->createIndex(['group' => 1]);
    echo "  Created settings indexes\n";
    
    // Roles indexes
    $rolesCollection->createIndex(['name' => 1], ['unique' => true]);
    echo "  Created roles indexes\n";
    
    echo "\n===========================================\n";
    echo "Database seeding completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Admin Login Credentials:\n";
    echo "  Email: admin@khairawangdairy.com\n";
    echo "  Password: admin123\n\n";
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
