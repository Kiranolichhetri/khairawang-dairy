<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Order Controller
 * 
 * Handles order history and tracking.
 */
class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * User's order history
     */
    public function index(Request $request): Response
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        if ($userId === null) {
            return Response::error('Unauthorized', 401);
        }
        
        $orders = $this->orderService->getUserOrders((int) $userId);
        
        $formatted = array_map(function($order) {
            return [
                'id' => $order->getKey(),
                'order_number' => $order->attributes['order_number'],
                'status' => $order->getStatus()->value,
                'status_label' => $order->getStatus()->label(),
                'status_color' => $order->getStatus()->color(),
                'payment_status' => $order->getPaymentStatus()->value,
                'payment_status_label' => $order->getPaymentStatus()->label(),
                'total' => (float) $order->attributes['total'],
                'item_count' => $order->getItemCount(),
                'can_cancel' => $order->canCancel(),
                'created_at' => $order->attributes['created_at'],
            ];
        }, $orders);
        
        return Response::json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Order details
     */
    public function show(Request $request, string $orderNumber): Response
    {
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        // Check authorization
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        if ($userId !== null && $order->attributes['user_id'] !== $userId) {
            return Response::error('Unauthorized', 403);
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
                    'status_color' => $order->getStatus()->color(),
                    'payment_status' => $order->getPaymentStatus()->value,
                    'payment_status_label' => $order->getPaymentStatus()->label(),
                    'payment_method' => $order->attributes['payment_method'],
                    'transaction_id' => $order->attributes['transaction_id'],
                    'subtotal' => (float) $order->attributes['subtotal'],
                    'shipping_cost' => (float) $order->attributes['shipping_cost'],
                    'discount' => (float) $order->attributes['discount'],
                    'total' => (float) $order->attributes['total'],
                    'shipping' => [
                        'name' => $order->attributes['shipping_name'],
                        'email' => $order->attributes['shipping_email'],
                        'phone' => $order->attributes['shipping_phone'],
                        'address' => $order->attributes['shipping_address'],
                        'city' => $order->attributes['shipping_city'],
                    ],
                    'notes' => $order->attributes['notes'],
                    'can_cancel' => $order->canCancel(),
                    'created_at' => $order->attributes['created_at'],
                    'updated_at' => $order->attributes['updated_at'],
                ],
                'items' => array_map(function($item) {
                    $images = json_decode($item['images'] ?? '[]', true);
                    return [
                        'id' => $item['id'],
                        'product_id' => $item['product_id'],
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
     * Track order status
     */
    public function track(Request $request, string $orderNumber): Response
    {
        $result = $this->orderService->trackOrder($orderNumber);
        
        if (!$result['success']) {
            return Response::error($result['message'], 404);
        }
        
        // Get order timeline
        $statusTimeline = $this->getStatusTimeline($result['status']);
        
        return Response::json([
            'success' => true,
            'data' => array_merge($result, ['timeline' => $statusTimeline]),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, string $orderNumber): Response
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        $result = $this->orderService->cancelOrder($orderNumber, $userId ? (int) $userId : null);
        
        if (!$result['success']) {
            return Response::error($result['message'], 400);
        }
        
        return Response::json($result);
    }

    /**
     * Get status timeline for tracking
     * 
     * @return array<array<string, mixed>>
     */
    private function getStatusTimeline(string $currentStatus): array
    {
        $statuses = [
            ['key' => 'pending', 'label' => 'Order Placed', 'icon' => 'shopping-bag'],
            ['key' => 'processing', 'label' => 'Processing', 'icon' => 'clock'],
            ['key' => 'packed', 'label' => 'Packed', 'icon' => 'package'],
            ['key' => 'shipped', 'label' => 'Shipped', 'icon' => 'truck'],
            ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'icon' => 'map-pin'],
            ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'check-circle'],
        ];
        
        $statusOrder = array_column($statuses, 'key');
        $currentIndex = array_search($currentStatus, $statusOrder);
        
        if ($currentStatus === 'cancelled') {
            return [
                ['key' => 'cancelled', 'label' => 'Cancelled', 'icon' => 'x-circle', 'completed' => true, 'current' => true],
            ];
        }
        
        if ($currentStatus === 'returned') {
            return array_merge($statuses, [
                ['key' => 'returned', 'label' => 'Returned', 'icon' => 'rotate-ccw', 'completed' => true, 'current' => true],
            ]);
        }
        
        return array_map(function($status, $index) use ($currentIndex) {
            return array_merge($status, [
                'completed' => $index <= $currentIndex,
                'current' => $index === $currentIndex,
            ]);
        }, $statuses, array_keys($statuses));
    }
}
