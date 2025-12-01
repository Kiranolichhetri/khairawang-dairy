<? php

require_once __DIR__ . '/vendor/autoload. php';

use MongoDB\Client;

$mongoUri = 'mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0. dxc9xkf.mongodb. net/';
$database = 'khairawang_dairy';

echo "Testing MongoDB Connection...\n\n";

try {
    $client = new Client($mongoUri);
    $db = $client->selectDatabase($database);
    
    echo "Connected to MongoDB!\n\n";
    
    $users = $db->selectCollection('users');
    $admin = $users->findOne(['email' => 'admin@khairawangdairy. com']);
    
    if ($admin) {
        echo "Admin user found!\n";
        echo "Email: " . $admin['email'] .  "\n";
        echo "Status: " . $admin['status'] . "\n";
        
        if (password_verify('admin123', $admin['password'])) {
            echo "Password CORRECT!\n";
        } else {
            echo "Password does NOT match!\n";
        }
    } else {
        echo "Admin user NOT found!\n";
    }
    
    echo "\nAll users:\n";
    $allUsers = $users->find()->toArray();
    foreach ($allUsers as $user) {
        echo "- " . $user['email'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
