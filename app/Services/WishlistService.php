<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Wishlist;
use App\Models\Product;
use Core\Application;
use Core\Database;

/**
 * Wishlist Service
 * 
 * Handles user wishlist operations.
 */
class WishlistService
{
    private ?Database $db = null;
    private CartService $cartService;

    public function __construct(?CartService $cartService = null)
    {
        $this->cartService = $cartService ?? new CartService();
    }

    /**
     * Get database connection
     */
    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->db = $app->db();
            }
        }
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not available');
        }
        
        return $this->db;
    }

    /**
     * Get wishlist items for a user
     * 
     * @return array<array<string, mixed>>
     */
    public function getWishlistItems(int $userId): array
    {
        $items = Wishlist::getItemsWithProducts($userId);
        
        return array_map(function($item) {
            $images = json_decode($item['images'] ?? '[]', true);
            $price = (float) ($item['sale_price'] ?? $item['price'] ?? 0);
            $originalPrice = (float) ($item['price'] ?? 0);
            
            return [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $item['name_en'],
                'slug' => $item['slug'],
                'price' => $price,
                'original_price' => $originalPrice,
                'on_sale' => $item['sale_price'] !== null && $item['sale_price'] < $originalPrice,
                'image' => !empty($images) ? '/uploads/products/' . $images[0] : '/assets/images/product-placeholder.png',
                'stock' => (int) $item['stock'],
                'in_stock' => (int) $item['stock'] > 0,
                'status' => $item['status'],
                'available' => $item['status'] === 'published' && (int) $item['stock'] > 0,
                'added_at' => $item['created_at'],
            ];
        }, $items);
    }

    /**
     * Get wishlist count
     */
    public function getWishlistCount(int $userId): int
    {
        return Wishlist::getCount($userId);
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist(int $userId, int $productId): bool
    {
        return Wishlist::isInWishlist($userId, $productId);
    }

    /**
     * Add product to wishlist
     * 
     * @return array{success: bool, message: string}
     */
    public function addToWishlist(int $userId, int $productId): array
    {
        // Check if product exists
        $product = Product::find($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check if already in wishlist
        if (Wishlist::isInWishlist($userId, $productId)) {
            return ['success' => false, 'message' => 'Product is already in your wishlist'];
        }
        
        Wishlist::addItem($userId, $productId);
        
        return [
            'success' => true,
            'message' => 'Product added to wishlist',
        ];
    }

    /**
     * Remove product from wishlist
     * 
     * @return array{success: bool, message: string}
     */
    public function removeFromWishlist(int $userId, int $productId): array
    {
        if (!Wishlist::isInWishlist($userId, $productId)) {
            return ['success' => false, 'message' => 'Product is not in your wishlist'];
        }
        
        Wishlist::removeItem($userId, $productId);
        
        return [
            'success' => true,
            'message' => 'Product removed from wishlist',
        ];
    }

    /**
     * Toggle product in wishlist
     * 
     * @return array{success: bool, message: string, added: bool}
     */
    public function toggleWishlist(int $userId, int $productId): array
    {
        // Check if product exists
        $product = Product::find($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Product not found', 'added' => false];
        }
        
        $result = Wishlist::toggleItem($userId, $productId);
        
        return [
            'success' => true,
            'message' => $result['added'] ? 'Product added to wishlist' : 'Product removed from wishlist',
            'added' => $result['added'],
        ];
    }

    /**
     * Move item from wishlist to cart
     * 
     * @return array{success: bool, message: string}
     */
    public function moveToCart(int $userId, int $productId): array
    {
        // Check if in wishlist
        if (!Wishlist::isInWishlist($userId, $productId)) {
            return ['success' => false, 'message' => 'Product is not in your wishlist'];
        }
        
        // Check if product is available
        $product = Product::find($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        if ($product->attributes['status'] !== 'published') {
            return ['success' => false, 'message' => 'Product is not available'];
        }
        
        if (((int) $product->attributes['stock']) <= 0) {
            return ['success' => false, 'message' => 'Product is out of stock'];
        }
        
        // Add to cart
        $cartResult = $this->cartService->add($productId, 1);
        
        if (!$cartResult['success']) {
            return ['success' => false, 'message' => $cartResult['message']];
        }
        
        // Remove from wishlist
        Wishlist::removeItem($userId, $productId);
        
        return [
            'success' => true,
            'message' => 'Product moved to cart',
        ];
    }

    /**
     * Clear entire wishlist
     * 
     * @return array{success: bool, message: string}
     */
    public function clearWishlist(int $userId): array
    {
        Wishlist::clearForUser($userId);
        
        return [
            'success' => true,
            'message' => 'Wishlist cleared',
        ];
    }

    /**
     * Sync guest wishlist (from localStorage) with user wishlist after login
     * 
     * @param array<int> $guestProductIds
     * @return array{success: bool, synced: int}
     */
    public function syncGuestWishlist(int $userId, array $guestProductIds): array
    {
        $synced = 0;
        
        foreach ($guestProductIds as $productId) {
            $productId = (int) $productId;
            
            // Check if product exists and not already in wishlist
            $product = Product::find($productId);
            if ($product !== null && !Wishlist::isInWishlist($userId, $productId)) {
                Wishlist::addItem($userId, $productId);
                $synced++;
            }
        }
        
        return [
            'success' => true,
            'synced' => $synced,
        ];
    }
}
