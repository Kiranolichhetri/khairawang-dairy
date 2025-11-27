<?php

declare(strict_types=1);

namespace App\Controllers\Account;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\CartService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Account Order Controller
 * 
 * Handles order history and management for user accounts.
 */
class OrderController
{
    private OrderService $orderService;
    private CartService $cartService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->cartService = new CartService();
    }

    /**
     * Get current user ID from session
     */
    private function getUserId(): ?int
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        return $userId ? (int) $userId : null;
    }

    /**
     * List all orders with pagination
     */
    public function index(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(1, (int) $request->query('per_page', 10)));
        $status = $request->query('status');
        
        $orders = $this->orderService->getUserOrders($userId);
        
        // Filter by status if provided
        if ($status && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $orders = array_filter($orders, fn($order) => $order->getStatus()->value === $status);
            $orders = array_values($orders);
        }
        
        // Paginate
        $total = count($orders);
        $offset = ($page - 1) * $perPage;
        $paginatedOrders = array_slice($orders, $offset, $perPage);
        
        $formatted = array_map(function($order) {
            return [
                'id' => $order->getKey(),
                'order_number' => $order->attributes['order_number'],
                'status' => $order->getStatus()->value,
                'status_label' => $order->getStatus()->label(),
                'status_color' => $order->getStatus()->color(),
                'payment_status' => $order->getPaymentStatus()->value,
                'payment_status_label' => $order->getPaymentStatus()->label(),
                'payment_method' => $order->attributes['payment_method'],
                'total' => (float) $order->attributes['total'],
                'item_count' => $order->getItemCount(),
                'can_cancel' => $order->canCancel(),
                'created_at' => $order->attributes['created_at'],
            ];
        }, $paginatedOrders);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $formatted,
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('account.orders.index', [
            'title' => 'Order History',
            'orders' => $formatted,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
            'filter' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * View order details
     */
    public function show(Request $request, string $orderNumber): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Order not found');
            return Response::redirect('/account/orders');
        }
        
        // Check authorization
        if ($order->attributes['user_id'] !== $userId) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 403);
            }
            return Response::redirect('/account/orders');
        }
        
        $items = $order->itemsWithProducts();
        
        $orderData = [
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
        ];
        
        $itemsData = array_map(function($item) {
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
        }, $items);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'order' => $orderData,
                    'items' => $itemsData,
                ],
            ]);
        }
        
        return Response::view('account.orders.show', [
            'title' => 'Order #' . $orderNumber,
            'order' => $orderData,
            'items' => $itemsData,
        ]);
    }

    /**
     * Track order status
     */
    public function track(Request $request, string $orderNumber): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Order not found');
            return Response::redirect('/account/orders');
        }
        
        // Check authorization
        if ($order->attributes['user_id'] !== $userId) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 403);
            }
            return Response::redirect('/account/orders');
        }
        
        $result = $this->orderService->trackOrder($orderNumber);
        $timeline = $this->getStatusTimeline($result['status']);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => array_merge($result, ['timeline' => $timeline]),
            ]);
        }
        
        return Response::view('account.orders.track', [
            'title' => 'Track Order #' . $orderNumber,
            'order' => $result,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, string $orderNumber): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->orderService->cancelOrder($orderNumber, $userId);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/orders/' . $orderNumber);
    }

    /**
     * Reorder items from a previous order
     */
    public function reorder(Request $request, string $orderNumber): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Order not found');
            return Response::redirect('/account/orders');
        }
        
        // Check authorization
        if ($order->attributes['user_id'] !== $userId) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 403);
            }
            return Response::redirect('/account/orders');
        }
        
        // Add items to cart
        $items = $order->items();
        $addedCount = 0;
        $unavailableItems = [];
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product === null || $product->attributes['status'] !== 'published') {
                $unavailableItems[] = $item['product_name'];
                continue;
            }
            
            if (((int) $product->attributes['stock']) < $item['quantity']) {
                $unavailableItems[] = $item['product_name'] . ' (limited stock)';
                continue;
            }
            
            $result = $this->cartService->add((int) $item['product_id'], (int) $item['quantity']);
            
            if ($result['success']) {
                $addedCount++;
            }
        }
        
        $session = Application::getInstance()?->session();
        
        if ($addedCount > 0) {
            $message = "{$addedCount} item(s) added to your cart.";
            
            if (!empty($unavailableItems)) {
                $message .= ' Some items are unavailable: ' . implode(', ', $unavailableItems);
            }
            
            $session?->success($message);
            
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => true,
                    'message' => $message,
                    'added_count' => $addedCount,
                    'unavailable' => $unavailableItems,
                ]);
            }
            
            return Response::redirect('/cart');
        }
        
        $session?->error('Unable to add items to cart. Products may be unavailable.');
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => false,
                'message' => 'Unable to add items to cart',
                'unavailable' => $unavailableItems,
            ], 400);
        }
        
        return Response::redirect('/account/orders/' . $orderNumber);
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(Request $request, string $orderNumber): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $order = $this->orderService->getOrder($orderNumber);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Order not found');
            return Response::redirect('/account/orders');
        }
        
        // Check authorization
        if ($order->attributes['user_id'] !== $userId) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 403);
            }
            return Response::redirect('/account/orders');
        }
        
        // Redirect to the invoice download endpoint
        return Response::redirect('/invoice/' . $orderNumber . '/download');
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
