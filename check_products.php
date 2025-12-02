<?php
require 'vendor/autoload.php';

use MongoDB\Client;

$client = new Client('mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/');
$db = $client->selectDatabase('khairawang_dairy');

echo "=== PRODUCTS IN DATABASE ===\n\n";
echo "Total products: " .  $db->selectCollection('products')->countDocuments() . "\n\n";

foreach ($db->selectCollection('products')->find() as $prod) {
    echo "- " . ($prod['name_en'] ??  'unnamed') .  "\n";
    echo "  ID: " . $prod['_id'] . "\n";
    echo "  Status: " . ($prod['status'] ?? 'none') . "\n";
    $featured = isset($prod['featured']) && $prod['featured'] ? 'Yes' : 'No';
    echo "  Featured: " . $featured . "\n";
    echo "  Price: " . ($prod['price'] ??  0) . "\n\n";
}