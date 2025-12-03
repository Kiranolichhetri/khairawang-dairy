<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;

/**
 * Stock Service
 * 
 * Handles stock management operations.
 */
class StockService
{
    /**
     * Check if product has sufficient stock
     */
    public function hasStock(string|int $productId, int $quantity): bool
    {
        $app = \Core\Application::getInstance();
        
        // Use MongoDB if it's the default connection
        if ($app?->isMongoDbDefault()) {
            try {
                $mongo = $app->mongo();
                $product = $mongo->findOne('products', [
                    '_id' => new \MongoDB\BSON\ObjectId($productId)
                ]);
                
                if ($product === null) {
                    return false;
                }
                
                $stock = (int) ($product['stock'] ?? 0);
                return $stock >= $quantity;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        // Fallback to MySQL
        $product = Product::find($productId);
        
        if ($product === null) {
            return false;
        }
        
        return ($product->attributes['stock'] ?? 0) >= $quantity;
    }

    /**
     * Check stock availability for multiple products
     * 
     * @param array<array{product_id: int, quantity: int}> $items
     * @return array<string, mixed>
     */
    public function validateStock(array $items): array
    {
        $errors = [];
        $valid = true;
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product === null) {
                $errors[] = [
                    'product_id' => $item['product_id'],
                    'message' => 'Product not found',
                ];
                $valid = false;
                continue;
            }
            
            if (!$product->isPublished()) {
                $errors[] = [
                    'product_id' => $item['product_id'],
                    'name' => $product->getName(),
                    'message' => 'Product is not available',
                ];
                $valid = false;
                continue;
            }
            
            $stock = $product->attributes['stock'] ?? 0;
            if ($stock < $item['quantity']) {
                $errors[] = [
                    'product_id' => $item['product_id'],
                    'name' => $product->getName(),
                    'requested' => $item['quantity'],
                    'available' => $stock,
                    'message' => "Only {$stock} units available",
                ];
                $valid = false;
            }
        }
        
        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }

    /**
     * Reduce stock for multiple products
     * 
     * @param array<array{product_id: int, quantity: int}> $items
     */
    public function reduceStock(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product === null) {
                return false;
            }
            
            if (!$product->reduceStock($item['quantity'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Restore stock for multiple products (e.g., when cancelling order)
     * 
     * @param array<array{product_id: int, quantity: int}> $items
     */
    public function restoreStock(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product === null) {
                continue;
            }
            
            $product->increaseStock($item['quantity']);
        }
        
        return true;
    }

    /**
     * Get low stock products
     * 
     * @return array<Product>
     */
    public function getLowStockProducts(): array
    {
        return Product::lowStock();
    }

    /**
     * Get out of stock products
     * 
     * @return array<Product>
     */
    public function getOutOfStockProducts(): array
    {
        return Product::outOfStock();
    }
}
