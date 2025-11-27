<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CouponService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Coupon Controller
 * 
 * Handles coupon application and removal for the cart.
 */
class CouponController
{
    private CouponService $couponService;

    public function __construct()
    {
        $this->couponService = new CouponService();
    }

    /**
     * Apply coupon to cart
     */
    public function apply(Request $request): Response
    {
        $code = trim($request->input('code', ''));
        
        if (empty($code)) {
            return Response::json([
                'success' => false,
                'message' => 'Please enter a coupon code',
            ], 400);
        }
        
        // Get cart total from request or calculate it
        $cartTotal = (float) $request->input('cart_total', 0);
        
        // Get user ID if logged in
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        $result = $this->couponService->apply($code, $cartTotal, $userId ? (int) $userId : null);
        
        if (!$result['success']) {
            return Response::json($result, 400);
        }
        
        // Store coupon in session
        $session?->set('applied_coupon', [
            'code' => $result['code'],
            'discount' => $result['discount'],
            'free_shipping' => $result['free_shipping'],
        ]);
        
        return Response::json($result);
    }

    /**
     * Remove coupon from cart
     */
    public function remove(Request $request): Response
    {
        $session = Application::getInstance()?->session();
        $session?->remove('applied_coupon');
        
        return Response::json([
            'success' => true,
            'message' => 'Coupon removed',
        ]);
    }

    /**
     * Validate coupon code (AJAX)
     */
    public function validate(Request $request): Response
    {
        $code = trim($request->input('code', ''));
        $cartTotal = (float) $request->input('cart_total', 0);
        
        if (empty($code)) {
            return Response::json([
                'valid' => false,
                'message' => 'Please enter a coupon code',
            ], 400);
        }
        
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        $result = $this->couponService->validate($code, $cartTotal, $userId ? (int) $userId : null);
        
        return Response::json($result);
    }
}
