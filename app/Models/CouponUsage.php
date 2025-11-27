<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Coupon Usage Model
 * 
 * Tracks coupon usage by users for orders.
 */
class CouponUsage extends Model
{
    protected static string $table = 'coupon_usages';
    
    protected static array $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'coupon_id' => 'integer',
        'user_id' => 'integer',
        'order_id' => 'integer',
        'discount_amount' => 'float',
        'used_at' => 'datetime',
    ];

    /**
     * Get the coupon for this usage
     * 
     * @return array<string, mixed>|null
     */
    public function coupon(): ?array
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Get the user who used the coupon
     * 
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the order this usage is for
     * 
     * @return array<string, mixed>|null
     */
    public function order(): ?array
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get usage count for a specific coupon
     */
    public static function countForCoupon(int $couponId): int
    {
        return static::query()
            ->where('coupon_id', $couponId)
            ->count();
    }

    /**
     * Get usage count for a specific user on a specific coupon
     */
    public static function countForUserCoupon(int $userId, int $couponId): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('coupon_id', $couponId)
            ->count();
    }

    /**
     * Get all usages for a coupon
     * 
     * @return array<self>
     */
    public static function forCoupon(int $couponId): array
    {
        $rows = static::query()
            ->where('coupon_id', $couponId)
            ->orderBy('used_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get total discount amount for a coupon
     */
    public static function totalDiscountForCoupon(int $couponId): float
    {
        $result = self::db()->selectOne(
            "SELECT SUM(discount_amount) as total FROM coupon_usages WHERE coupon_id = ?",
            [$couponId]
        );
        
        return (float) ($result['total'] ?? 0);
    }
}
