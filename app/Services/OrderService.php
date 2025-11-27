<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Core\Application;

/**
 * Order Service
 * 
 * Handles order creation and processing.
 */
class OrderService
{
    private CartService $cartService;
    private StockService $stockService;
    private PaymentService $paymentService;

    public function __construct(
        ?CartService $cartService = null,
        ?StockService $stockService = null,
        ?PaymentService $paymentService = null
    ) {
        $this->cartService = $cartService ?? new CartService();
        $this->stockService = $stockService ?? new StockService();
        $this->paymentService = $paymentService ?? new PaymentService();
    }

    /**
     * Create order from cart
     * 
     * @param array<string, mixed> $shippingData
     * @return array<string, mixed>
     */
    public function createOrder(array $shippingData): array
    {
        // Validate cart
        $validation = $this->cartService->validateForCheckout();
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        $cart = $this->cartService->getCart();
        $cartContents = $this->cartService->getCartContents();
        
        // Validate required shipping data
        $requiredFields = ['name', 'email', 'phone', 'address'];
        foreach ($requiredFields as $field) {
            if (empty($shippingData[$field])) {
                return ['success' => false, 'message' => "Missing required field: {$field}"];
            }
        }
        
        // Validate email format with stricter validation
        $email = $shippingData['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        // Additional email validation: check for common typos and valid domain format
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Validate phone (Nepal mobile numbers start with 98 or 97)
        $phone = preg_replace('/[^0-9]/', '', $shippingData['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return ['success' => false, 'message' => 'Phone number must be 10-15 digits'];
        }
        // For Nepal, validate mobile number format
        if (strlen($phone) === 10 && !preg_match('/^(98|97|96)[0-9]{8}$/', $phone)) {
            return ['success' => false, 'message' => 'Invalid Nepal mobile number format'];
        }
        $shippingData['phone'] = $phone;
        
        // Add shipping cost and discount
        $shippingData['shipping_cost'] = $cartContents['shipping'];
        $shippingData['discount'] = $shippingData['discount'] ?? 0;
        
        try {
            // Create the order
            $order = Order::createFromCart($cart, $shippingData);
            
            return [
                'success' => true,
                'message' => 'Order created successfully',
                'order' => [
                    'id' => $order->getKey(),
                    'order_number' => $order->attributes['order_number'],
                    'total' => $order->attributes['total'],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process checkout with payment
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function processCheckout(array $data): array
    {
        // Create order first
        $orderResult = $this->createOrder($data);
        
        if (!$orderResult['success']) {
            return $orderResult;
        }
        
        $orderId = $orderResult['order']['order_number'];
        $total = $orderResult['order']['total'];
        $paymentMethod = $data['payment_method'] ?? 'cod';
        
        // Update order with payment method
        $order = Order::findByOrderNumber($orderId);
        if ($order) {
            $order->payment_method = $paymentMethod;
            $order->save();
        }
        
        // Initiate payment
        if ($paymentMethod !== 'cod') {
            $cartContents = $this->cartService->getCartContents();
            $shipping = $cartContents['shipping'];
            $subtotal = $cartContents['subtotal'];
            
            $paymentResult = $this->paymentService->initiatePayment(
                $paymentMethod,
                $orderId,
                $subtotal,
                $shipping
            );
            
            if (!$paymentResult['success']) {
                return $paymentResult;
            }
            
            return array_merge($orderResult, $paymentResult);
        }
        
        // For COD, just return success
        return array_merge($orderResult, [
            'method' => 'cod',
            'redirect' => false,
            'redirect_url' => '/checkout/success/' . $orderId,
        ]);
    }

    /**
     * Handle payment success callback
     * 
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function handlePaymentSuccess(string $method, array $params): array
    {
        $verification = $this->paymentService->verifyPayment($method, $params);
        
        if (!$verification['success']) {
            return $verification;
        }
        
        $orderId = $params['oid'] ?? $params['order_id'] ?? '';
        $transactionId = $verification['transaction_id'] ?? '';
        
        $order = Order::findByOrderNumber($orderId);
        
        if ($order === null) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Update payment status
        $order->updatePaymentStatus(PaymentStatus::PAID, $transactionId);
        $order->updateStatus(OrderStatus::PROCESSING);
        
        return [
            'success' => true,
            'message' => 'Payment successful',
            'order_number' => $orderId,
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * Handle payment failure callback
     * 
     * @param array<string, string> $params
     * @return array<string, mixed>
     */
    public function handlePaymentFailure(string $method, array $params): array
    {
        $orderId = $params['oid'] ?? $params['order_id'] ?? '';
        
        $order = Order::findByOrderNumber($orderId);
        
        if ($order !== null) {
            $order->updatePaymentStatus(PaymentStatus::FAILED);
            
            // Restore stock since payment failed
            $items = [];
            foreach ($order->items() as $item) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ];
            }
            $this->stockService->restoreStock($items);
        }
        
        return [
            'success' => false,
            'message' => 'Payment failed',
            'order_number' => $orderId,
        ];
    }

    /**
     * Get order by order number
     */
    public function getOrder(string $orderNumber): ?Order
    {
        return Order::findByOrderNumber($orderNumber);
    }

    /**
     * Get orders for user
     * 
     * @return array<Order>
     */
    public function getUserOrders(int $userId): array
    {
        return Order::forUser($userId);
    }

    /**
     * Cancel order
     * 
     * @return array<string, mixed>
     */
    public function cancelOrder(string $orderNumber, ?int $userId = null): array
    {
        $order = Order::findByOrderNumber($orderNumber);
        
        if ($order === null) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Check if user owns this order
        if ($userId !== null && $order->attributes['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        if (!$order->canCancel()) {
            return ['success' => false, 'message' => 'Order cannot be cancelled'];
        }
        
        if ($order->cancel()) {
            return ['success' => true, 'message' => 'Order cancelled successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to cancel order'];
    }

    /**
     * Track order status
     * 
     * @return array<string, mixed>
     */
    public function trackOrder(string $orderNumber): array
    {
        $order = Order::findByOrderNumber($orderNumber);
        
        if ($order === null) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $status = $order->getStatus();
        
        return [
            'success' => true,
            'order_number' => $orderNumber,
            'status' => $status->value,
            'status_label' => $status->label(),
            'status_color' => $status->color(),
            'payment_status' => $order->getPaymentStatus()->value,
            'payment_status_label' => $order->getPaymentStatus()->label(),
            'can_cancel' => $order->canCancel(),
            'created_at' => $order->attributes['created_at'],
            'updated_at' => $order->attributes['updated_at'],
        ];
    }
}
