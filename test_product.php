<?php
require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

$mongoUri = 'mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/';
$database = 'khairawang_dairy';

echo "=== Testing Product Creation ===\n\n";

try {
    $client = new Client($mongoUri);
    $db = $client->selectDatabase($database);
    
    echo "Connected to MongoDB!\n\n";
    
    // List categories
    echo "Categories:\n";
    $categories = $db->selectCollection('categories');
    foreach ($categories->find() as $cat) {
        echo "  - " .  ($cat['name_en'] ?? 'unnamed') . " (ID: " .  $cat['_id'] . ")\n";
    }
    
    // Get first category ID
    $firstCategory = $categories->findOne();
    $categoryId = (string) $firstCategory['_id'];
    
    echo "\nUsing category ID: " . $categoryId .  "\n\n";
    
    // Create a test product
    $products = $db->selectCollection('products');
    
    $result = $products->insertOne([
        'category_id' => $categoryId,
        'name_en' => 'Test Milk Product',
        'name_ne' => '',
        'slug' => 'test-milk-product-' . time(),
        'description_en' => 'A test product',
        'short_description' => 'Test',
        'price' => 100.00,
        'sale_price' => null,
        'sku' => 'TEST-001',
        'stock' => 50,
        'low_stock_threshold' => 10,
        'weight' => 1.0,
        'images' => [],
        'featured' => false,
        'status' => 'published',
        'seo_title' => '',
        'seo_description' => '',
        'deleted_at' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    
    echo "Product created with ID: " . $result->getInsertedId() . "\n\n";
    
    // Count products
    echo "Total products now: " . $products->countDocuments() . "\n";
    
    // List products
    echo "\nProducts:\n";
    foreach ($products->find() as $prod) {
        echo "  - " .  ($prod['name_en'] ?? 'unnamed') . " (ID: " . $prod['_id'] . ")\n";
    }
    
    echo "\n=== SUCCESS ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
