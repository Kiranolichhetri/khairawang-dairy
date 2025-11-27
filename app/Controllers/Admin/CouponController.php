<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Coupon;
use App\Services\CouponService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Coupon Controller
 * 
 * Handles coupon management in the admin panel.
 */
class CouponController
{
    private CouponService $couponService;

    public function __construct()
    {
        $this->couponService = new CouponService();
    }

    /**
     * List all coupons
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        
        $coupons = $this->couponService->getAllCoupons($page, $perPage);
        $total = $this->couponService->getTotalCount();
        
        $formattedCoupons = [];
        foreach ($coupons as $coupon) {
            $formattedCoupons[] = $this->formatCoupon($coupon);
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'coupons' => $formattedCoupons,
                ],
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('admin.coupons.index', [
            'title' => 'Coupons',
            'coupons' => $formattedCoupons,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Show create coupon form
     */
    public function create(Request $request): Response
    {
        return Response::view('admin.coupons.create', [
            'title' => 'Create Coupon',
            'coupon_types' => [
                Coupon::TYPE_PERCENTAGE => 'Percentage Discount',
                Coupon::TYPE_FIXED => 'Fixed Amount',
                Coupon::TYPE_FREE_SHIPPING => 'Free Shipping',
            ],
        ]);
    }

    /**
     * Store new coupon
     */
    public function store(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'type' => 'required',
            'value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/coupons/create');
        }

        // Validate code uniqueness if provided
        $code = trim($request->input('code', ''));
        if (!empty($code)) {
            $existing = Coupon::findByCode($code);
            if ($existing !== null) {
                $app = Application::getInstance();
                $session = $app?->session();
                $session?->flashErrors(['code' => ['This coupon code already exists.']]);
                $session?->flashInput($request->all());
                
                return Response::redirect('/admin/coupons/create');
            }
        }

        $coupon = $this->couponService->createCoupon([
            'code' => $code,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'type' => $request->input('type'),
            'value' => (float) $request->input('value'),
            'min_order_amount' => (float) $request->input('min_order_amount', 0),
            'maximum_discount' => $request->input('maximum_discount') ? (float) $request->input('maximum_discount') : null,
            'max_uses' => $request->input('max_uses') ? (int) $request->input('max_uses') : null,
            'per_user_limit' => (int) $request->input('per_user_limit', 1),
            'starts_at' => $request->input('starts_at') ?: null,
            'expires_at' => $request->input('expires_at') ?: null,
            'status' => $request->input('status', 'active'),
        ]);

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Coupon created successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'coupon' => $coupon->toArray(),
            ], 201);
        }

        return Response::redirect('/admin/coupons');
    }

    /**
     * Show edit coupon form
     */
    public function edit(Request $request, string $id): Response
    {
        $coupon = Coupon::find((int) $id);
        
        if ($coupon === null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Coupon not found.');
            
            return Response::redirect('/admin/coupons');
        }
        
        return Response::view('admin.coupons.edit', [
            'title' => 'Edit Coupon',
            'coupon' => $coupon->toArray(),
            'coupon_types' => [
                Coupon::TYPE_PERCENTAGE => 'Percentage Discount',
                Coupon::TYPE_FIXED => 'Fixed Amount',
                Coupon::TYPE_FREE_SHIPPING => 'Free Shipping',
            ],
            'stats' => $this->couponService->getCouponStats((int) $id),
        ]);
    }

    /**
     * Update coupon
     */
    public function update(Request $request, string $id): Response
    {
        $coupon = Coupon::find((int) $id);
        
        if ($coupon === null) {
            if ($request->expectsJson()) {
                return Response::error('Coupon not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Coupon not found.');
            
            return Response::redirect('/admin/coupons');
        }
        
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'type' => 'required',
            'value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/coupons/' . $id . '/edit');
        }

        // Validate code uniqueness if changed
        $code = trim($request->input('code', ''));
        if (!empty($code) && strtoupper($code) !== strtoupper($coupon->attributes['code'] ?? '')) {
            $existing = Coupon::findByCode($code);
            if ($existing !== null) {
                $app = Application::getInstance();
                $session = $app?->session();
                $session?->flashErrors(['code' => ['This coupon code already exists.']]);
                $session?->flashInput($request->all());
                
                return Response::redirect('/admin/coupons/' . $id . '/edit');
            }
        }

        $this->couponService->updateCoupon((int) $id, [
            'code' => $code ?: $coupon->attributes['code'],
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'type' => $request->input('type'),
            'value' => (float) $request->input('value'),
            'min_order_amount' => (float) $request->input('min_order_amount', 0),
            'maximum_discount' => $request->input('maximum_discount') ? (float) $request->input('maximum_discount') : null,
            'max_uses' => $request->input('max_uses') ? (int) $request->input('max_uses') : null,
            'per_user_limit' => (int) $request->input('per_user_limit', 1),
            'starts_at' => $request->input('starts_at') ?: null,
            'expires_at' => $request->input('expires_at') ?: null,
            'status' => $request->input('status', 'active'),
        ]);

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Coupon updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Coupon updated successfully',
            ]);
        }

        return Response::redirect('/admin/coupons');
    }

    /**
     * Delete coupon
     */
    public function delete(Request $request, string $id): Response
    {
        $deleted = $this->couponService->deleteCoupon((int) $id);
        
        if (!$deleted) {
            if ($request->expectsJson()) {
                return Response::error('Coupon not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Coupon not found.');
            
            return Response::redirect('/admin/coupons');
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Coupon deleted successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Coupon deleted successfully',
            ]);
        }

        return Response::redirect('/admin/coupons');
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(Request $request, string $id): Response
    {
        $coupon = $this->couponService->toggleStatus((int) $id);
        
        if ($coupon === null) {
            if ($request->expectsJson()) {
                return Response::error('Coupon not found', 404);
            }
            
            return Response::redirect('/admin/coupons');
        }

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Coupon status updated',
                'status' => $coupon->attributes['status'],
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Coupon status updated!');

        return Response::redirect('/admin/coupons');
    }

    /**
     * Generate a new coupon code
     */
    public function generateCode(Request $request): Response
    {
        $code = Coupon::generateCode();
        
        return Response::json([
            'success' => true,
            'code' => $code,
        ]);
    }

    /**
     * Format coupon for API response
     * 
     * @return array<string, mixed>
     */
    private function formatCoupon(Coupon $coupon): array
    {
        return [
            'id' => $coupon->getKey(),
            'code' => $coupon->attributes['code'] ?? '',
            'name' => $coupon->attributes['name'] ?? '',
            'description' => $coupon->attributes['description'] ?? '',
            'type' => $coupon->attributes['type'] ?? '',
            'type_label' => $coupon->getTypeLabel(),
            'value' => (float) ($coupon->attributes['value'] ?? 0),
            'formatted_value' => $coupon->getFormattedValue(),
            'min_order_amount' => (float) ($coupon->attributes['min_order_amount'] ?? 0),
            'maximum_discount' => $coupon->attributes['maximum_discount'],
            'max_uses' => $coupon->attributes['max_uses'],
            'uses_count' => (int) ($coupon->attributes['uses_count'] ?? 0),
            'per_user_limit' => (int) ($coupon->attributes['per_user_limit'] ?? 1),
            'starts_at' => $coupon->attributes['starts_at'],
            'expires_at' => $coupon->attributes['expires_at'],
            'status' => $coupon->attributes['status'] ?? 'inactive',
            'is_active' => $coupon->isActive(),
            'is_expired' => $coupon->isExpired(),
            'created_at' => $coupon->attributes['created_at'],
        ];
    }
}
