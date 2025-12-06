<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\MongoDB;

/**
 * Cart Model
 * 
 * Represents a shopping cart with items.
 * Supports both MySQL and MongoDB backends.
 */
class Cart extends Model
{
    protected static string $table = 'carts';
    
    protected static array $fillable = [
        'user_id',
        'session_id',
        'items',
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
        if (static::isMongoDb()) {
            // For MongoDB, items are embedded in the cart document
            return $this->attributes['items'] ?? [];
        }
        
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
        if (static::isMongoDb()) {
            return $this->itemsWithProductsMongo();
        }
        
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
     * Get cart items with product details from MongoDB
     * 
     * @return array<int, array<string, mixed>>
     */
    private function itemsWithProductsMongo(): array
    {
        $items = $this->attributes['items'] ?? [];
        $result = [];
        
        if (empty($items)) {
            return $result;
        }
        
        $mongo = static::mongo();
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId === null) {
                continue;
            }
            
            try {
                $product = $mongo->findOne('products', [
                    '_id' => MongoDB::objectId($productId)
                ]);
            } catch (\Exception $e) {
                continue;
            }
            
            if ($product === null) {
                continue;
            }
            
            // Merge cart item with product details
            $result[] = [
                'id' => $item['id'] ?? $productId,
                'product_id' => $productId,
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'name_en' => $product['name_en'] ?? '',
                'name_ne' => $product['name_ne'] ?? '',
                'slug' => $product['slug'] ?? '',
                'price' => $product['price'] ?? 0,
                'sale_price' => $product['sale_price'] ?? null,
                'images' => $product['images'] ?? [],
                'stock' => $product['stock'] ?? 0,
            ];
        }
        
        return $result;
    }

    /**
     * Add item to cart
     */
    public function addItem(string|int $productId, int $quantity = 1, string|int|null $variantId = null): bool
    {
        if (static::isMongoDb()) {
            return $this->addItemMongo((string) $productId, $quantity, $variantId !== null ? (string) $variantId : null);
        }
        
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
     * Add item to cart in MongoDB
     */
    private function addItemMongo(string $productId, int $quantity, ?string $variantId): bool
    {
        $mongo = static::mongo();
        
        // Verify product exists
        try {
            $product = $mongo->findOne('products', [
                '_id' => MongoDB::objectId($productId)
            ]);
        } catch (\Exception $e) {
            return false;
        }
        
        if ($product === null) {
            return false;
        }
        
        // Check if published
        $status = $product['status'] ?? '';
        if ($status !== 'published') {
            return false;
        }
        
        $stock = (int) ($product['stock'] ?? 0);
        if ($stock < $quantity) {
            return false;
        }
        
        // Get current items
        $items = $this->attributes['items'] ?? [];
        $found = false;
        
        // Check if item already exists
        foreach ($items as $index => $item) {
            if (($item['product_id'] ?? '') === $productId && 
                ($item['variant_id'] ?? null) === $variantId) {
                // Update quantity
                $newQuantity = ($item['quantity'] ?? 0) + $quantity;
                $items[$index]['quantity'] = min($newQuantity, $stock);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Add new item with unique ID
            $items[] = [
                'id' => bin2hex(random_bytes(12)),
                'product_id' => $productId,
                'quantity' => $quantity,
                'variant_id' => $variantId,
                'added_at' => new \MongoDB\BSON\UTCDateTime(),
            ];
        }
        
        // Update cart
        $this->attributes['items'] = $items;
        $this->touchMongo();
        
        return true;
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity(string|int $itemId, int $quantity): bool
    {
        if (static::isMongoDb()) {
            return $this->updateItemQuantityMongo((string) $itemId, $quantity);
        }
        
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
     * Update item quantity in MongoDB
     */
    private function updateItemQuantityMongo(string $itemId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItemMongo($itemId);
        }
        
        $items = $this->attributes['items'] ?? [];
        $found = false;
        $productId = null;
        
        foreach ($items as $index => $item) {
            // Match by item id
            if (($item['id'] ?? '') === $itemId) {
                $productId = $item['product_id'];
                $found = true;
                
                // Check stock
                $mongo = static::mongo();
                try {
                    $product = $mongo->findOne('products', [
                        '_id' => MongoDB::objectId($productId)
                    ]);
                } catch (\Exception $e) {
                    return false;
                }
                
                if ($product === null) {
                    return false;
                }
                
                $stock = (int) ($product['stock'] ?? 0);
                if ($quantity > $stock) {
                    return false;
                }
                
                $items[$index]['quantity'] = $quantity;
                break;
            }
        }
        
        if (!$found) {
            return false;
        }
        
        $this->attributes['items'] = $items;
        $this->touchMongo();
        
        return true;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string|int $itemId): bool
    {
        if (static::isMongoDb()) {
            return $this->removeItemMongo((string) $itemId);
        }
        
        $deleted = self::db()->delete('cart_items', [
            'id' => $itemId,
            'cart_id' => $this->getKey(),
        ]);
        
        $this->touch();
        
        return $deleted > 0;
    }

    /**
     * Remove item from cart in MongoDB
     */
    private function removeItemMongo(string $itemId): bool
    {
        $items = $this->attributes['items'] ?? [];
        $initialCount = count($items);
        
        $items = array_values(array_filter($items, function ($item) use ($itemId) {
            // Match by item id only
            return ($item['id'] ?? '') !== $itemId;
        }));
        
        if (count($items) === $initialCount) {
            return false;
        }
        
        $this->attributes['items'] = $items;
        $this->touchMongo();
        
        return true;
    }

    /**
     * Clear cart
     */
    public function clear(): bool
    {
        if (static::isMongoDb()) {
            $this->attributes['items'] = [];
            $this->touchMongo();
            return true;
        }
        
        self::db()->query(
            "DELETE FROM cart_items WHERE cart_id = ?",
            [$this->getKey()]
        );
        
        $this->touch();
        
        return true;
    }

    /**
     * Update timestamp (MySQL)
     */
    private function touch(): void
    {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Update timestamp (MongoDB)
     */
    private function touchMongo(): void
    {
        $mongo = static::mongo();
        $filter = $this->getMongoIdFilter();
        
        $mongo->updateOne(static::$table, $filter, [
            '$set' => [
                'items' => $this->attributes['items'],
                'updated_at' => new \MongoDB\BSON\UTCDateTime()
            ]
        ]);
    }

    /**
     * Get MongoDB _id filter from a raw ID value
     * 
     * @param mixed $id The ID value (can be string, ObjectId, etc.)
     * @return array<string, mixed>
     */
    private static function getMongoIdFilterFromValue(mixed $id): array
    {
        if (is_string($id) && MongoDB::isValidObjectId($id)) {
            return ['_id' => MongoDB::objectId($id)];
        }
        return ['_id' => $id];
    }

    /**
     * Get item count
     */
    public function getItemCount(): int
    {
        if (static::isMongoDb()) {
            $items = $this->attributes['items'] ?? [];
            $total = 0;
            foreach ($items as $item) {
                $total += (int) ($item['quantity'] ?? 0);
            }
            return $total;
        }
        
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
        if (static::isMongoDb()) {
            $mongo = static::mongo();
            $cart = $mongo->findOne(static::$table, ['user_id' => $userId]);
            
            if ($cart !== null) {
                return static::hydrate($cart);
            }
            
            // Create new cart
            $id = $mongo->insertOne(static::$table, [
                'user_id' => $userId,
                'session_id' => null,
                'items' => [],
            ]);
            
            // Fetch the created cart to get complete data with timestamps
            $cart = $mongo->findOne(static::$table, ['_id' => MongoDB::objectId($id)]);
            
            return static::hydrate($cart);
        }
        
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
        if (static::isMongoDb()) {
            $mongo = static::mongo();
            $cart = $mongo->findOne(static::$table, ['session_id' => $sessionId]);
            
            if ($cart !== null) {
                return static::hydrate($cart);
            }
            
            // Create new cart
            $id = $mongo->insertOne(static::$table, [
                'session_id' => $sessionId,
                'user_id' => null,
                'items' => [],
            ]);
            
            // Fetch the created cart to get complete data with timestamps
            $cart = $mongo->findOne(static::$table, ['_id' => MongoDB::objectId($id)]);
            
            return static::hydrate($cart);
        }
        
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
        if (static::isMongoDb()) {
            static::mergeGuestCartMongo($userId, $sessionId);
            return;
        }
        
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
     * Merge guest cart into user cart in MongoDB
     */
    private static function mergeGuestCartMongo(int $userId, string $sessionId): void
    {
        $mongo = static::mongo();
        
        // Get guest cart
        $guestCart = $mongo->findOne(static::$table, [
            'session_id' => $sessionId,
            'user_id' => null
        ]);
        
        if ($guestCart === null) {
            return;
        }
        
        // Get or create user cart
        $userCart = static::forUser($userId);
        
        // Get guest cart items (embedded in cart document)
        $guestItems = $guestCart['items'] ?? [];
        
        // Merge items
        foreach ($guestItems as $item) {
            $userCart->addItem(
                $item['product_id'],
                $item['quantity'],
                $item['variant_id'] ?? null
            );
        }
        
        // Delete guest cart
        $guestId = $guestCart['_id'] ?? null;
        if ($guestId) {
            $filter = static::getMongoIdFilterFromValue($guestId);
            $mongo->deleteOne(static::$table, $filter);
        }
    }

    /**
     * Clean up abandoned carts (older than specified days)
     */
    public static function cleanupAbandoned(int $days = 30): int
    {
        if (static::isMongoDb()) {
            $cutoffDate = new \MongoDB\BSON\UTCDateTime(
                (new \DateTime("-{$days} days"))->getTimestamp() * 1000
            );
            
            $mongo = static::mongo();
            return $mongo->deleteMany(static::$table, [
                'updated_at' => ['$lt' => $cutoffDate],
                'user_id' => null
            ]);
        }
        
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
