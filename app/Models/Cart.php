<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Cart Model
 * 
 * Represents a shopping cart with items.
 */
class Cart extends Model
{
    protected static string $table = 'carts';
    
    protected static array $fillable = [
        'user_id',
        'session_id',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get cart items
     * 
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        return self::db()->table('cart_items')
            ->where('cart_id', $this->getKey())
            ->get();
    }

    /**
     * Get cart items with product details
     * 
     * @return array<int, array<string, mixed>>
     */
    public function itemsWithProducts(): array
    {
        return self::db()->table('cart_items')
            ->select([
                'cart_items.*',
                'products.name_en',
                'products.name_ne',
                'products.slug',
                'products.price',
                'products.sale_price',
                'products.images',
                'products.stock',
            ])
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->where('cart_items.cart_id', $this->getKey())
            ->get();
    }

    /**
     * Add item to cart
     */
    public function addItem(string|int $productId, int $quantity = 1, string|int|null $variantId = null): bool
    {
        // Check if product exists
        $product = Product::find($productId);
        
        if ($product === null || !$product->isPublished()) {
            return false;
        }
        
        // Check stock
        if ($product->attributes['stock'] < $quantity) {
            return false;
        }
        
        // Check if item already exists in cart
        $existingItem = self::db()->table('cart_items')
            ->where('cart_id', $this->getKey())
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();
        
        if ($existingItem !== null) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($newQuantity > $product->attributes['stock']) {
                $newQuantity = $product->attributes['stock'];
            }
            
            self::db()->update(
                'cart_items',
                ['quantity' => $newQuantity],
                ['id' => $existingItem['id']]
            );
        } else {
            // Add new item
            self::db()->insert('cart_items', [
                'cart_id' => $this->getKey(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }
        
        // Update cart timestamp
        $this->touch();
        
        return true;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity(string|int $itemId, int $quantity): bool
    {
        $item = self::db()->table('cart_items')
            ->where('id', $itemId)
            ->where('cart_id', $this->getKey())
            ->first();
        
        if ($item === null) {
            return false;
        }
        
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }
        
        // Check stock
        $product = Product::find($item['product_id']);
        
        if ($product === null || $quantity > $product->attributes['stock']) {
            return false;
        }
        
        self::db()->update(
            'cart_items',
            ['quantity' => $quantity],
            ['id' => $itemId]
        );
        
        $this->touch();
        
        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string|int $itemId): bool
    {
        $deleted = self::db()->delete('cart_items', [
            'id' => $itemId,
            'cart_id' => $this->getKey(),
        ]);
        
        $this->touch();
        
        return $deleted > 0;
    }

    /**
     * Clear cart
     */
    public function clear(): bool
    {
        self::db()->query(
            "DELETE FROM cart_items WHERE cart_id = ?",
            [$this->getKey()]
        );
        
        $this->touch();
        
        return true;
    }

    /**
     * Update timestamp
     */
    private function touch(): void
    {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Get item count
     */
    public function getItemCount(): int
    {
        $result = self::db()->selectOne(
            "SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?",
            [$this->getKey()]
        );
        
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get subtotal
     */
    public function getSubtotal(): float
    {
        $items = $this->itemsWithProducts();
        $subtotal = 0.0;
        
        foreach ($items as $item) {
            $price = $item['sale_price'] ?? $item['price'];
            
            if ($item['sale_price'] !== null && $item['sale_price'] > 0) {
                $price = $item['sale_price'];
            } else {
                $price = $item['price'];
            }
            
            $subtotal += (float) $price * $item['quantity'];
        }
        
        return $subtotal;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->getItemCount() === 0;
    }

    /**
     * Get or create cart for user
     */
    public static function forUser(int $userId): self
    {
        $cartData = static::query()
            ->where('user_id', $userId)
            ->first();
        
        if ($cartData !== null) {
            return static::hydrate($cartData);
        }
        
        return static::create(['user_id' => $userId]);
    }

    /**
     * Get or create cart for session
     */
    public static function forSession(string $sessionId): self
    {
        $cartData = self::db()->table(static::$table)
            ->where('session_id', $sessionId)
            ->first();
        
        if ($cartData !== null) {
            return static::hydrate($cartData);
        }
        
        return static::create(['session_id' => $sessionId]);
    }

    /**
     * Merge guest cart into user cart after login
     */
    public static function mergeGuestCart(int $userId, string $sessionId): void
    {
        // Get guest cart
        $guestCart = self::db()->table(static::$table)
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();
        
        if ($guestCart === null) {
            return;
        }
        
        // Get or create user cart
        $userCart = static::forUser($userId);
        
        // Get guest cart items
        $guestItems = self::db()->table('cart_items')
            ->where('cart_id', $guestCart['id'])
            ->get();
        
        // Merge items
        foreach ($guestItems as $item) {
            $userCart->addItem(
                $item['product_id'],
                $item['quantity'],
                $item['variant_id']
            );
        }
        
        // Delete guest cart and items
        self::db()->delete('cart_items', ['cart_id' => $guestCart['id']]);
        self::db()->delete(static::$table, ['id' => $guestCart['id']]);
    }

    /**
     * Clean up abandoned carts (older than specified days)
     */
    public static function cleanupAbandoned(int $days = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Get cart IDs to delete
        $carts = self::db()->select(
            "SELECT id FROM carts WHERE updated_at < ? AND user_id IS NULL",
            [$cutoffDate]
        );
        
        if (empty($carts)) {
            return 0;
        }
        
        $cartIds = array_column($carts, 'id');
        $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
        
        // Delete cart items
        self::db()->query(
            "DELETE FROM cart_items WHERE cart_id IN ({$placeholders})",
            $cartIds
        );
        
        // Delete carts
        return self::db()->query(
            "DELETE FROM carts WHERE id IN ({$placeholders})",
            $cartIds
        )->rowCount();
    }
}
