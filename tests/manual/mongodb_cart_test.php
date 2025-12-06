#!/usr/bin/env php
<?php

/**
 * MongoDB Cart Functionality Test Script
 * 
 * This script tests the MongoDB cart operations without requiring HTTP requests.
 * Run with: php tests/manual/mongodb_cart_test.php
 * 
 * Prerequisites:
 * - MongoDB connection configured in config/database.php with default='mongodb'
 * - At least one product in MongoDB products collection
 */

declare(strict_types=1);

// Determine project root directory
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/vendor/autoload.php';

use Core\Application;
use Core\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;

// Color output helpers
function success(string $msg): void {
    echo "\033[32m✓ $msg\033[0m\n";
}

function error(string $msg): void {
    echo "\033[31m✗ $msg\033[0m\n";
}

function info(string $msg): void {
    echo "\033[34mℹ $msg\033[0m\n";
}

function title(string $msg): void {
    echo "\n\033[1;33m=== $msg ===\033[0m\n";
}

// Start test
echo "\n";
echo "╔═══════════════════════════════════════════╗\n";
echo "║   MongoDB Cart Functionality Test         ║\n";
echo "╚═══════════════════════════════════════════╝\n";

try {
    // Initialize application
    $app = new Application($projectRoot);
    
    // Check if MongoDB is default
    title('Configuration Check');
    if (!$app->isMongoDbDefault()) {
        error('MongoDB is not set as default database');
        error('Please set database.default to "mongodb" in config/database.php');
        exit(1);
    }
    success('MongoDB is set as default database');
    
    // Test MongoDB connection
    title('MongoDB Connection');
    $mongo = $app->mongo();
    $mongo->getDatabase();
    success('MongoDB connection successful');
    
    // Get a test product
    title('Product Lookup');
    $products = $mongo->find('products', ['status' => 'published'], ['limit' => 1]);
    
    if (empty($products)) {
        error('No published products found in MongoDB');
        info('Please add at least one product with status="published"');
        exit(1);
    }
    
    $testProduct = $products[0];
    $productId = $testProduct['_id'];
    $productName = $testProduct['name_en'] ?? 'Unknown';
    success("Found test product: $productName (ID: $productId)");
    
    // Verify Product model can find it
    $product = Product::find($productId);
    if ($product === null) {
        error('Product::find() could not find the product');
        exit(1);
    }
    success('Product::find() works correctly');
    
    // Start session
    title('Session Management');
    $session = $app->session();
    $session->start();
    $testSessionId = 'test_' . bin2hex(random_bytes(16)); // 32 hex characters
    $session->set('cart_session_id', $testSessionId);
    success("Test session created: $testSessionId");
    
    // Test 1: Create cart and add item
    title('Test 1: Add Item to Cart');
    $cart = Cart::forSession($testSessionId);
    success('Cart created for session');
    
    $initialCount = $cart->getItemCount();
    info("Initial cart item count: $initialCount");
    
    $added = $cart->addItem($productId, 2);
    if (!$added) {
        error('Failed to add item to cart');
        exit(1);
    }
    success('Item added to cart');
    
    // Re-fetch cart to verify persistence
    $cart = Cart::forSession($testSessionId);
    $newCount = $cart->getItemCount();
    if ($newCount !== 2) {
        error("Expected item count 2, got $newCount");
        exit(1);
    }
    success("Cart item count verified: $newCount");
    
    // Test 2: Get items with products
    title('Test 2: Get Items with Product Details');
    $items = $cart->itemsWithProducts();
    if (empty($items)) {
        error('Cart items are empty');
        exit(1);
    }
    success('Cart items retrieved: ' . count($items) . ' item(s)');
    
    $firstItem = $items[0];
    $requiredFields = ['id', 'product_id', 'name_en', 'price', 'quantity', 'stock'];
    foreach ($requiredFields as $field) {
        if (!isset($firstItem[$field])) {
            error("Missing required field: $field");
            exit(1);
        }
    }
    success('All required fields present in cart item');
    
    // Test 3: Update quantity
    title('Test 3: Update Item Quantity');
    $itemId = $firstItem['id'];
    info("Item ID: $itemId");
    
    $updated = $cart->updateItemQuantity($itemId, 3);
    if (!$updated) {
        error('Failed to update item quantity');
        exit(1);
    }
    success('Item quantity updated');
    
    $cart = Cart::forSession($testSessionId);
    $newCount = $cart->getItemCount();
    if ($newCount !== 3) {
        error("Expected item count 3, got $newCount");
        exit(1);
    }
    success("Updated quantity verified: $newCount");
    
    // Test 4: CartService integration
    title('Test 4: CartService Integration');
    $cartService = new CartService();
    $contents = $cartService->getCartContents();
    
    if ($contents['count'] !== 3) {
        error("CartService returned wrong count: {$contents['count']}");
        exit(1);
    }
    success("CartService returned correct count: {$contents['count']}");
    
    if (empty($contents['items'])) {
        error('CartService returned empty items');
        exit(1);
    }
    success('CartService returned items with product details');
    
    $subtotal = $contents['subtotal'];
    $total = $contents['total'];
    info("Cart subtotal: NPR " . number_format($subtotal, 2));
    info("Cart total: NPR " . number_format($total, 2));
    
    // Test 5: Remove item
    title('Test 5: Remove Item from Cart');
    $removed = $cart->removeItem($itemId);
    if (!$removed) {
        error('Failed to remove item');
        exit(1);
    }
    success('Item removed from cart');
    
    $cart = Cart::forSession($testSessionId);
    $finalCount = $cart->getItemCount();
    if ($finalCount !== 0) {
        error("Expected item count 0, got $finalCount");
        exit(1);
    }
    success('Cart is now empty');
    
    // Test 6: Verify MongoDB document structure
    title('Test 6: MongoDB Document Structure');
    $cartDoc = $mongo->findOne('carts', ['session_id' => $testSessionId]);
    if ($cartDoc === null) {
        error('Cart document not found in MongoDB');
        exit(1);
    }
    success('Cart document found in MongoDB');
    
    $requiredCartFields = ['_id', 'session_id', 'items', 'created_at', 'updated_at'];
    foreach ($requiredCartFields as $field) {
        if (!isset($cartDoc[$field])) {
            error("Missing required cart field: $field");
            exit(1);
        }
    }
    success('Cart document has all required fields');
    
    // Cleanup
    title('Cleanup');
    $mongo->deleteOne('carts', ['session_id' => $testSessionId]);
    success('Test cart removed from MongoDB');
    
    // All tests passed
    echo "\n";
    echo "╔═══════════════════════════════════════════╗\n";
    echo "║  \033[1;32m✓ ALL TESTS PASSED\033[0m                    ║\n";
    echo "╚═══════════════════════════════════════════╝\n";
    echo "\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "\n";
    error('Test failed with exception: ' . $e->getMessage());
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
