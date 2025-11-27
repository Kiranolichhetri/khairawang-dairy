<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Coupon Model
 * 
 * Represents a discount coupon for the e-commerce platform.
 */
class Coupon extends Model
{
    protected static string $table = 'coupons';
    
    protected static array $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'maximum_discount',
        'max_uses',
        'uses_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'status',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'value' => 'float',
        'min_order_amount' => 'float',
        'maximum_discount' => 'float',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'per_user_limit' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Coupon types
     */
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE_SHIPPING = 'free_shipping';

    /**
     * Check if coupon is active
     */
    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? '') === 'active';
    }

    /**
     * Check if coupon has started
     */
    public function hasStarted(): bool
    {
        $startsAt = $this->attributes['starts_at'] ?? null;
        
        if ($startsAt === null) {
            return true;
        }
        
        return strtotime($startsAt) <= time();
    }

    /**
     * Check if coupon is expired
     */
    public function isExpired(): bool
    {
        $expiresAt = $this->attributes['expires_at'] ?? null;
        
        if ($expiresAt === null) {
            return false;
        }
        
        return strtotime($expiresAt) < time();
    }

    /**
     * Check if coupon has reached max usage
     */
    public function hasReachedMaxUsage(): bool
    {
        $maxUses = $this->attributes['max_uses'] ?? null;
        
        if ($maxUses === null) {
            return false;
        }
        
        return ($this->attributes['uses_count'] ?? 0) >= $maxUses;
    }

    /**
     * Check if user has reached their usage limit for this coupon
     */
    public function hasUserReachedLimit(int $userId): bool
    {
        $perUserLimit = $this->attributes['per_user_limit'] ?? 1;
        
        $usageCount = self::db()->table('coupon_usages')
            ->where('coupon_id', $this->getKey())
            ->where('user_id', $userId)
            ->count();
        
        return $usageCount >= $perUserLimit;
    }

    /**
     * Check if cart total meets minimum order requirement
     */
    public function meetsMinimumOrder(float $cartTotal): bool
    {
        $minOrder = $this->attributes['min_order_amount'] ?? 0;
        return $cartTotal >= $minOrder;
    }

    /**
     * Calculate discount amount for given cart total
     */
    public function calculateDiscount(float $cartTotal): float
    {
        $type = $this->attributes['type'] ?? self::TYPE_PERCENTAGE;
        $value = (float) ($this->attributes['value'] ?? 0);
        $maxDiscount = $this->attributes['maximum_discount'] ?? null;
        
        $discount = 0.0;
        
        switch ($type) {
            case self::TYPE_PERCENTAGE:
                $discount = $cartTotal * ($value / 100);
                break;
                
            case self::TYPE_FIXED:
                $discount = min($value, $cartTotal);
                break;
                
            case self::TYPE_FREE_SHIPPING:
                // Free shipping discount is handled separately
                $discount = 0.0;
                break;
        }
        
        // Apply maximum discount cap if set
        if ($maxDiscount !== null && $discount > $maxDiscount) {
            $discount = (float) $maxDiscount;
        }
        
        return round($discount, 2);
    }

    /**
     * Check if coupon provides free shipping
     */
    public function isFreeShipping(): bool
    {
        return ($this->attributes['type'] ?? '') === self::TYPE_FREE_SHIPPING;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): bool
    {
        $this->uses_count = ($this->attributes['uses_count'] ?? 0) + 1;
        return $this->save();
    }

    /**
     * Record coupon usage
     */
    public function recordUsage(int $userId, int $orderId, float $discountAmount): bool
    {
        self::db()->insert('coupon_usages', [
            'coupon_id' => $this->getKey(),
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);
        
        return $this->incrementUsage();
    }

    /**
     * Get coupon type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->attributes['type'] ?? '') {
            self::TYPE_PERCENTAGE => 'Percentage Discount',
            self::TYPE_FIXED => 'Fixed Amount',
            self::TYPE_FREE_SHIPPING => 'Free Shipping',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted value display
     */
    public function getFormattedValue(): string
    {
        $type = $this->attributes['type'] ?? self::TYPE_PERCENTAGE;
        $value = (float) ($this->attributes['value'] ?? 0);
        
        return match ($type) {
            self::TYPE_PERCENTAGE => $value . '%',
            self::TYPE_FIXED => 'रू ' . number_format($value, 2),
            self::TYPE_FREE_SHIPPING => 'Free Shipping',
            default => (string) $value,
        };
    }

    /**
     * Find coupon by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::findBy('code', strtoupper(trim($code)));
    }

    /**
     * Get active coupons
     * 
     * @return array<self>
     */
    public static function active(): array
    {
        $now = date('Y-m-d H:i:s');
        
        $rows = static::query()
            ->where('status', 'active')
            ->whereRaw("(starts_at IS NULL OR starts_at <= '{$now}')")
            ->whereRaw("(expires_at IS NULL OR expires_at > '{$now}')")
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Generate unique coupon code
     */
    public static function generateCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::findByCode($code) !== null);
        
        return $code;
    }
}
