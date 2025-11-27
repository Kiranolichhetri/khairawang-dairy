<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Wishlist Model
 * 
 * Represents a user's wishlist item.
 */
class Wishlist extends Model
{
    protected static string $table = 'wishlists';
    
    protected static array $fillable = [
        'user_id',
        'product_id',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get wishlist items for a user
     * 
     * @return array<self>
     */
    public static function forUser(int $userId): array
    {
        $rows = static::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get wishlist items with product details
     * 
     * @return array<array<string, mixed>>
     */
    public static function getItemsWithProducts(int $userId): array
    {
        return self::db()->select(
            "SELECT w.*, p.name_en, p.slug, p.price, p.sale_price, p.images, p.stock, p.status
             FROM wishlists w
             LEFT JOIN products p ON w.product_id = p.id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC",
            [$userId]
        );
    }

    /**
     * Check if product is in user's wishlist
     */
    public static function isInWishlist(int $userId, int $productId): bool
    {
        $data = static::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        
        return $data !== null;
    }

    /**
     * Add product to wishlist
     */
    public static function addItem(int $userId, int $productId): ?self
    {
        // Check if already exists
        if (static::isInWishlist($userId, $productId)) {
            return static::findBy('user_id', $userId);
        }
        
        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public static function removeItem(int $userId, int $productId): bool
    {
        return self::db()->delete(static::getTable(), [
            'user_id' => $userId,
            'product_id' => $productId,
        ]) > 0;
    }

    /**
     * Toggle wishlist item
     * @return array{added: bool, removed: bool}
     */
    public static function toggleItem(int $userId, int $productId): array
    {
        if (static::isInWishlist($userId, $productId)) {
            static::removeItem($userId, $productId);
            return ['added' => false, 'removed' => true];
        }
        
        static::addItem($userId, $productId);
        return ['added' => true, 'removed' => false];
    }

    /**
     * Get wishlist count for a user
     */
    public static function getCount(int $userId): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Clear entire wishlist for a user
     */
    public static function clearForUser(int $userId): int
    {
        return self::db()->delete(static::getTable(), ['user_id' => $userId]);
    }

    /**
     * Get product
     * 
     * @return array<string, mixed>|null
     */
    public function product(): ?array
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
