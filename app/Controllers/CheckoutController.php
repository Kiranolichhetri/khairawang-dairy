<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Checkout Controller
 * 
 * Handles checkout flow and order processing.
 */
class CheckoutController
{
    private CartService $cartService;
    private OrderService $orderService;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->cartService = new CartService();
        $this->orderService = new OrderService();
        $this->paymentService = new PaymentService();
    }

    /**
     * Checkout page - returns cart and payment options
     */
    public function index(Request $request): Response
    {
        $cart = $this->cartService->getCartContents();
        
        if (empty($cart['items'])) {
            return Response::error('Cart is empty', 400);
        }
        
        // Validate stock before checkout
        $validation = $this->cartService->validateForCheckout();
        
        if (!$validation['valid']) {
            return Response::json([
                'success' => false,
                'message' => $validation['message'],
                'errors' => $validation['errors'] ?? [],
            ], 400);
        }
        
        // Get user info if logged in
        $session = Application::getInstance()?->session();
        $user = $session?->get('user');
        
        // Get available payment methods
        $paymentMethods = $this->paymentService->getPaymentMethods();
        
        return Response::json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'user' => $user ? [
                    'name' => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                ] : null,
                'payment_methods' => array_values($paymentMethods),
                'shipping' => [
                    'cost' => $cart['shipping'],
                    'free_threshold' => $cart['free_shipping_threshold'],
                    'is_free' => $cart['free_shipping'],
                ],
            ],
        ]);
    }

    /**
     * Process checkout - create order and initiate payment
     */
    public function process(Request $request): Response
    {
        // Validate required fields
        $required = ['name', 'email', 'phone', 'address', 'payment_method'];
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($request->input($field))) {
                $errors[$field] = [ucfirst($field) . ' is required'];
            }
        }
        
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        // Prepare shipping data
        $shippingData = [
            'name' => trim($request->input('name')),
            'email' => trim($request->input('email')),
            'phone' => trim($request->input('phone')),
            'address' => trim($request->input('address')),
            'city' => trim($request->input('city', '')),
            'notes' => trim($request->input('notes', '')),
            'payment_method' => $request->input('payment_method'),
        ];
        
        // Process checkout
        $result = $this->orderService->processCheckout($shippingData);
        
        if (!$result['success']) {
            return Response::error($result['message'], 400);
        }
        
        // For eSewa, return redirect info
        if (isset($result['redirect']) && $result['redirect'] === true && $result['method'] === 'esewa') {
            return Response::json([
                'success' => true,
                'redirect' => true,
                'payment_url' => $result['payment_url'],
                'params' => $result['params'],
                'order' => $result['order'],
            ]);
        }
        
        // For COD, return success
        return Response::json([
            'success' => true,
            'redirect' => false,
            'redirect_url' => '/checkout/success/' . $result['order']['order_number'],
            'order' => $result['order'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Order confirmation page
     */
    public function confirm(Request $request, string $orderNumber): Response
    {
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Check if user owns this order (if logged in)
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        if ($userId !== null && $order->attributes['user_id'] !== $userId) {
            // Allow viewing if email matches
            $userEmail = $session?->get('user')['email'] ?? '';
            if ($userEmail !== $order->attributes['shipping_email']) {
                return Response::error('Unauthorized', 403);
            }
        }
        
        $items = $order->itemsWithProducts();
        
        return Response::json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->getKey(),
                    'order_number' => $order->attributes['order_number'],
                    'status' => $order->getStatus()->value,
                    'status_label' => $order->getStatus()->label(),
                    'payment_status' => $order->getPaymentStatus()->value,
                    'payment_status_label' => $order->getPaymentStatus()->label(),
                    'payment_method' => $order->attributes['payment_method'],
                    'subtotal' => (float) $order->attributes['subtotal'],
                    'shipping_cost' => (float) $order->attributes['shipping_cost'],
                    'discount' => (float) $order->attributes['discount'],
                    'total' => (float) $order->attributes['total'],
                    'shipping_name' => $order->attributes['shipping_name'],
                    'shipping_email' => $order->attributes['shipping_email'],
                    'shipping_phone' => $order->attributes['shipping_phone'],
                    'shipping_address' => $order->attributes['shipping_address'],
                    'shipping_city' => $order->attributes['shipping_city'],
                    'notes' => $order->attributes['notes'],
                    'created_at' => $order->attributes['created_at'],
                ],
                'items' => array_map(function($item) {
                    $images = json_decode($item['images'] ?? '[]', true);
                    return [
                        'product_name' => $item['product_name'],
                        'variant_name' => $item['variant_name'],
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $item['price'],
                        'total' => (float) $item['total'],
                        'slug' => $item['slug'],
                        'image' => !empty($images) ? '/uploads/products/' . $images[0] : '/assets/images/product-placeholder.png',
                    ];
                }, $items),
            ],
        ]);
    }

    /**
     * Validate stock availability
     */
    public function validateStock(Request $request): Response
    {
        $validation = $this->cartService->validateForCheckout();
        
        return Response::json([
            'success' => true,
            'valid' => $validation['valid'],
            'message' => $validation['message'] ?? null,
            'errors' => $validation['errors'] ?? [],
        ]);
    }
}
