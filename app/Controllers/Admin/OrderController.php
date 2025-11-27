<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Admin Order Controller
 * 
 * Handles order management in the admin panel.
 */
class OrderController
{
    /**
     * List all orders with filters
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = trim($request->query('q', '') ?? '');
        
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Build query
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];
        
        if ($status && in_array($status, array_column(OrderStatus::cases(), 'value'))) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($paymentStatus && in_array($paymentStatus, array_column(PaymentStatus::cases(), 'value'))) {
            $sql .= " AND payment_status = ?";
            $params[] = $paymentStatus;
        }
        
        if (!empty($search)) {
            $sql .= " AND (order_number LIKE ? OR shipping_name LIKE ? OR shipping_email LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as count', $sql);
        $countResult = $db->selectOne($countSql, $params);
        $total = (int) ($countResult['count'] ?? 0);
        
        // Add ordering and pagination
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $ordersData = $db->select($sql, $params);
        
        $orders = [];
        foreach ($ordersData as $row) {
            $order = Order::find($row['id']);
            if ($order !== null) {
                $orders[] = $this->formatOrder($order);
            }
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                ],
                'meta' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }
        
        return Response::view('admin.orders.index', [
            'title' => 'Orders',
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'filters' => [
                'status' => $status,
                'payment_status' => $paymentStatus,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Show order details
     */
    public function show(Request $request, string $id): Response
    {
        $order = Order::find((int) $id);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Order not found.');
            
            return Response::redirect('/admin/orders');
        }
        
        $orderDetails = $this->formatOrder($order);
        $orderDetails['items'] = $order->itemsWithProducts();
        $orderDetails['customer'] = $order->user();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $orderDetails,
            ]);
        }
        
        return Response::view('admin.orders.show', [
            'title' => 'Order ' . $order->attributes['order_number'],
            'order' => $orderDetails,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, string $id): Response
    {
        $order = Order::find((int) $id);
        
        if ($order === null) {
            if ($request->expectsJson()) {
                return Response::error('Order not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Order not found.');
            
            return Response::redirect('/admin/orders');
        }
        
        $newStatus = $request->input('status');
        
        if (!$newStatus || !in_array($newStatus, array_column(OrderStatus::cases(), 'value'))) {
            if ($request->expectsJson()) {
                return Response::error('Invalid status', 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Invalid status.');
            
            return Response::redirect('/admin/orders/' . $id);
        }
        
        $newStatusEnum = OrderStatus::from($newStatus);
        
        if (!$order->updateStatus($newStatusEnum)) {
            if ($request->expectsJson()) {
                return Response::error('Cannot transition to this status', 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Cannot update order to this status.');
            
            return Response::redirect('/admin/orders/' . $id);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Order status updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'status' => $newStatus,
            ]);
        }

        return Response::redirect('/admin/orders/' . $id);
    }

    /**
     * Print invoice
     */
    public function printInvoice(Request $request, string $id): Response
    {
        $order = Order::find((int) $id);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        $orderDetails = $this->formatOrder($order);
        $orderDetails['items'] = $order->itemsWithProducts();
        $orderDetails['customer'] = $order->user();
        
        return Response::view('admin.orders.invoice', [
            'title' => 'Invoice - ' . $order->attributes['order_number'],
            'order' => $orderDetails,
        ]);
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request): Response
    {
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::error('Database connection error', 500);
        }
        
        // Build query
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $orders = $db->select($sql, $params);
        
        // Build CSV content
        $csv = "Order Number,Customer Name,Email,Phone,Address,City,Subtotal,Shipping,Discount,Total,Payment Method,Payment Status,Status,Date\n";
        
        foreach ($orders as $order) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s",%.2f,%.2f,%.2f,%.2f,"%s","%s","%s","%s"' . "\n",
                $order['order_number'],
                str_replace('"', '""', $order['shipping_name']),
                $order['shipping_email'],
                $order['shipping_phone'],
                str_replace('"', '""', $order['shipping_address']),
                $order['shipping_city'] ?? '',
                $order['subtotal'],
                $order['shipping_cost'],
                $order['discount'],
                $order['total'],
                $order['payment_method'],
                $order['payment_status'],
                $order['status'],
                $order['created_at']
            );
        }
        
        $response = new Response($csv);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');
        
        return $response;
    }

    /**
     * Add order note
     */
    public function addNote(Request $request, string $id): Response
    {
        $order = Order::find((int) $id);
        
        if ($order === null) {
            return Response::error('Order not found', 404);
        }
        
        $note = $request->input('note');
        
        if (empty($note)) {
            return Response::error('Note is required', 400);
        }
        
        // Append note to existing notes
        $existingNotes = $order->attributes['notes'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] " . $note;
        
        $order->notes = $existingNotes ? $existingNotes . "\n" . $newNote : $newNote;
        $order->save();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Note added successfully',
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Note added successfully!');

        return Response::redirect('/admin/orders/' . $id);
    }

    /**
     * Format order for response
     * 
     * @return array<string, mixed>
     */
    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->getKey(),
            'order_number' => $order->attributes['order_number'],
            'customer_name' => $order->getCustomerName(),
            'shipping_name' => $order->attributes['shipping_name'],
            'shipping_email' => $order->attributes['shipping_email'],
            'shipping_phone' => $order->attributes['shipping_phone'],
            'shipping_address' => $order->attributes['shipping_address'],
            'shipping_city' => $order->attributes['shipping_city'],
            'subtotal' => (float) $order->attributes['subtotal'],
            'shipping_cost' => (float) $order->attributes['shipping_cost'],
            'discount' => (float) $order->attributes['discount'],
            'total' => (float) $order->attributes['total'],
            'payment_method' => $order->attributes['payment_method'],
            'payment_status' => $order->getPaymentStatus()->value,
            'payment_status_label' => $order->getPaymentStatus()->label(),
            'payment_status_color' => $order->getPaymentStatus()->color(),
            'status' => $order->getStatus()->value,
            'status_label' => $order->getStatus()->label(),
            'status_color' => $order->getStatus()->color(),
            'transaction_id' => $order->attributes['transaction_id'],
            'notes' => $order->attributes['notes'],
            'item_count' => $order->getItemCount(),
            'created_at' => $order->attributes['created_at'],
            'updated_at' => $order->attributes['updated_at'],
        ];
    }
}
