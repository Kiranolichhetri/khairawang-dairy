<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;

/**
 * Coupon Service
 * 
 * Handles coupon validation and application logic.
 */
class CouponService
{
    /**
     * Validate a coupon code
     * 
     * @return array<string, mixed>
     */
    public function validate(string $code, float $cartTotal, ?int $userId = null): array
    {
        $coupon = Coupon::findByCode($code);
        
        if ($coupon === null) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code',
            ];
        }
        
        // Check if coupon is active
        if (!$coupon->isActive()) {
            return [
                'valid' => false,
                'message' => 'This coupon is no longer active',
            ];
        }
        
        // Check if coupon has started
        if (!$coupon->hasStarted()) {
            return [
                'valid' => false,
                'message' => 'This coupon is not yet active',
            ];
        }
        
        // Check if coupon is expired
        if ($coupon->isExpired()) {
            return [
                'valid' => false,
                'message' => 'This coupon has expired',
            ];
        }
        
        // Check max usage
        if ($coupon->hasReachedMaxUsage()) {
            return [
                'valid' => false,
                'message' => 'This coupon has reached its maximum usage limit',
            ];
        }
        
        // Check user-specific usage limit
        if ($userId !== null && $coupon->hasUserReachedLimit($userId)) {
            return [
                'valid' => false,
                'message' => 'You have already used this coupon',
            ];
        }
        
        // Check minimum order amount
        if (!$coupon->meetsMinimumOrder($cartTotal)) {
            $minOrder = $coupon->attributes['min_order_amount'] ?? 0;
            return [
                'valid' => false,
                'message' => sprintf('Minimum order amount of रू %.2f required', $minOrder),
            ];
        }
        
        // Calculate discount
        $discount = $coupon->calculateDiscount($cartTotal);
        
        return [
            'valid' => true,
            'coupon' => $coupon->toArray(),
            'discount' => $discount,
            'free_shipping' => $coupon->isFreeShipping(),
            'message' => 'Coupon applied successfully',
        ];
    }

    /**
     * Apply coupon to cart
     * 
     * @return array<string, mixed>
     */
    public function apply(string $code, float $cartTotal, ?int $userId = null): array
    {
        $validation = $this->validate($code, $cartTotal, $userId);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        return [
            'success' => true,
            'code' => strtoupper(trim($code)),
            'discount' => $validation['discount'],
            'free_shipping' => $validation['free_shipping'],
            'message' => $validation['message'],
        ];
    }

    /**
     * Record coupon usage after order completion
     */
    public function recordUsage(string $code, int $userId, int $orderId, float $discountAmount): bool
    {
        $coupon = Coupon::findByCode($code);
        
        if ($coupon === null) {
            return false;
        }
        
        return $coupon->recordUsage($userId, $orderId, $discountAmount);
    }

    /**
     * Get coupon by code
     */
    public function getCoupon(string $code): ?Coupon
    {
        return Coupon::findByCode($code);
    }

    /**
     * Get all coupons (for admin)
     * 
     * @return array<Coupon>
     */
    public function getAllCoupons(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $rows = Coupon::query()
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();
        
        return array_map(fn($row) => Coupon::hydrate($row), $rows);
    }

    /**
     * Get total coupon count
     */
    public function getTotalCount(): int
    {
        return Coupon::count();
    }

    /**
     * Get active coupon count
     */
    public function getActiveCount(): int
    {
        return Coupon::query()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Create a new coupon
     * 
     * @param array<string, mixed> $data
     */
    public function createCoupon(array $data): Coupon
    {
        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = Coupon::generateCode();
        } else {
            $data['code'] = strtoupper(trim($data['code']));
        }
        
        return Coupon::create($data);
    }

    /**
     * Update a coupon
     * 
     * @param array<string, mixed> $data
     */
    public function updateCoupon(int $id, array $data): ?Coupon
    {
        $coupon = Coupon::find($id);
        
        if ($coupon === null) {
            return null;
        }
        
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        
        $coupon->fill($data);
        $coupon->save();
        
        return $coupon;
    }

    /**
     * Delete a coupon
     */
    public function deleteCoupon(int $id): bool
    {
        $coupon = Coupon::find($id);
        
        if ($coupon === null) {
            return false;
        }
        
        return $coupon->delete();
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(int $id): ?Coupon
    {
        $coupon = Coupon::find($id);
        
        if ($coupon === null) {
            return null;
        }
        
        $currentStatus = $coupon->attributes['status'] ?? 'inactive';
        $coupon->status = $currentStatus === 'active' ? 'inactive' : 'active';
        $coupon->save();
        
        return $coupon;
    }

    /**
     * Get coupon usage statistics
     * 
     * @return array<string, mixed>
     */
    public function getCouponStats(int $couponId): array
    {
        $usageCount = CouponUsage::countForCoupon($couponId);
        $totalDiscount = CouponUsage::totalDiscountForCoupon($couponId);
        $usages = CouponUsage::forCoupon($couponId);
        
        return [
            'usage_count' => $usageCount,
            'total_discount' => $totalDiscount,
            'recent_usages' => array_slice($usages, 0, 10),
        ];
    }
}
