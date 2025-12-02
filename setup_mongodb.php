<?php

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

$mongoUri = 'mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/';
$database = 'khairawang_dairy';

echo "=== Setting up MongoDB ===\n\n";

try {
    $client = new Client($mongoUri);
    $db = $client->selectDatabase($database);
    
    echo "Connected to MongoDB!\n\n";
    
    echo "Creating roles...\n";
    $roles = $db->selectCollection('roles');
    $roles->drop();
    
    $roles->insertMany([
        ['name' => 'admin', 'permissions' => ['*']],
        ['name' => 'manager', 'permissions' => ['manage_products', 'manage_orders']],
        ['name' => 'staff', 'permissions' => ['view_orders']],
        ['name' => 'customer', 'permissions' => ['place_order']]
    ]);
    echo "Roles created!\n\n";
    
    $adminRole = $roles->findOne(['name' => 'admin']);
    $adminRoleId = (string) $adminRole['_id'];
    
    echo "Creating admin user...\n";
    $users = $db->selectCollection('users');
    $users->deleteMany(['email' => 'admin@khairawangdairy.com']);
    
    $password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    
    $users->insertOne([
        'role_id' => $adminRoleId,
        'email' => 'admin@khairawangdairy.com',
        'password' => $password,
        'name' => 'Admin User',
        'phone' => '+977-9800000000',
        'status' => 'active',
        'email_verified_at' => new UTCDateTime(),
        'created_at' => new UTCDateTime(),
        'updated_at' => new UTCDateTime()
    ]);
    
    echo "Admin user created!\n\n";
    
    echo "Creating indexes...\n";
    $users->createIndex(['email' => 1], ['unique' => true]);
    $roles->createIndex(['name' => 1], ['unique' => true]);
    echo "Indexes created!\n\n";
    
    echo "=== SETUP COMPLETE ===\n\n";
    echo "Login Credentials:\n";
    echo "  Email: admin@khairawangdairy.com\n";
    echo "  Password: admin123\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
