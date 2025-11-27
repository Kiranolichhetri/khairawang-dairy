<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Admin Dashboard Controller
 * 
 * Displays main admin dashboard with statistics and recent activity.
 */
class DashboardController
{
    /**
     * Main dashboard page
     */
    public function index(Request $request): Response
    {
        $stats = $this->getStats($request);
        
        return Response::view('admin.dashboard.index', [
            'title' => 'Dashboard',
            'stats' => $stats['data'],
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request): Response
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::json([
                'success' => false,
                'message' => 'Database connection error',
            ], 500);
        }
        
        // Sales statistics
        $todayRevenue = $this->getRevenueForPeriod($db, 'today');
        $weekRevenue = $this->getRevenueForPeriod($db, 'week');
        $monthRevenue = $this->getRevenueForPeriod($db, 'month');
        $yearRevenue = $this->getRevenueForPeriod($db, 'year');
        
        // Order counts by status
        $orderStats = $this->getOrderStatsByStatus($db);
        
        // Total customers
        $customerCount = $this->getCustomerCount($db);
        
        // Low stock products
        $lowStockProducts = $this->getLowStockProducts($db);
        
        // Recent orders
        $recentOrders = Order::recent(10);
        
        // Top selling products
        $topProducts = $this->getTopSellingProducts($db);
        
        // Today's orders count
        $todayOrders = $this->getOrderCountForPeriod($db, 'today');
        
        $data = [
            'revenue' => [
                'today' => $todayRevenue,
                'week' => $weekRevenue,
                'month' => $monthRevenue,
                'year' => $yearRevenue,
            ],
            'orders' => [
                'total' => $orderStats['total'],
                'pending' => $orderStats['pending'],
                'processing' => $orderStats['processing'],
                'shipped' => $orderStats['shipped'],
                'delivered' => $orderStats['delivered'],
                'cancelled' => $orderStats['cancelled'],
                'today' => $todayOrders,
            ],
            'customers' => [
                'total' => $customerCount,
            ],
            'products' => [
                'low_stock' => $lowStockProducts,
                'top_selling' => $topProducts,
            ],
            'recent_orders' => array_map(function($order) {
                return [
                    'id' => $order->getKey(),
                    'order_number' => $order->attributes['order_number'],
                    'customer_name' => $order->getCustomerName(),
                    'total' => $order->attributes['total'],
                    'status' => $order->getStatus()->value,
                    'status_label' => $order->getStatus()->label(),
                    'status_color' => $order->getStatus()->color(),
                    'payment_status' => $order->getPaymentStatus()->value,
                    'created_at' => $order->attributes['created_at'],
                ];
            }, $recentOrders),
        ];
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }
        
        return Response::json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get revenue for a specific period
     */
    private function getRevenueForPeriod($db, string $period): float
    {
        $sql = "SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE payment_status = ?";
        $params = [PaymentStatus::PAID->value];
        
        switch ($period) {
            case 'today':
                $sql .= " AND DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
                break;
        }
        
        $result = $db->selectOne($sql, $params);
        return (float) ($result['revenue'] ?? 0);
    }

    /**
     * Get order count for a specific period
     */
    private function getOrderCountForPeriod($db, string $period): int
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        
        switch ($period) {
            case 'today':
                $sql .= " AND DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        $result = $db->selectOne($sql);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get order statistics by status
     * 
     * @return array<string, int>
     */
    private function getOrderStatsByStatus($db): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
        $results = $db->select($sql);
        
        $stats = [
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'packed' => 0,
            'shipped' => 0,
            'out_for_delivery' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'returned' => 0,
        ];
        
        foreach ($results as $row) {
            $status = $row['status'];
            $count = (int) $row['count'];
            $stats[$status] = $count;
            $stats['total'] += $count;
        }
        
        return $stats;
    }

    /**
     * Get total customer count
     */
    private function getCustomerCount($db): int
    {
        $sql = "SELECT COUNT(*) as count FROM users u 
                INNER JOIN roles r ON u.role_id = r.id 
                WHERE r.name = 'customer'";
        $result = $db->selectOne($sql);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get low stock products
     * 
     * @return array<array<string, mixed>>
     */
    private function getLowStockProducts($db, int $limit = 5): array
    {
        $sql = "SELECT id, name_en as name, slug, stock, low_stock_threshold 
                FROM products 
                WHERE stock <= low_stock_threshold AND deleted_at IS NULL
                ORDER BY stock ASC 
                LIMIT ?";
        
        return $db->select($sql, [$limit]);
    }

    /**
     * Get top selling products
     * 
     * @return array<array<string, mixed>>
     */
    private function getTopSellingProducts($db, int $limit = 5): array
    {
        $sql = "SELECT p.id, p.name_en as name, p.slug, p.price, 
                       COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                WHERE p.deleted_at IS NULL
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT ?";
        
        return $db->select($sql, [$limit]);
    }

    /**
     * Get sales chart data
     */
    public function getSalesChart(Request $request): Response
    {
        $app = Application::getInstance();
        $db = $app?->db();
        
        if ($db === null) {
            return Response::json([
                'success' => false,
                'message' => 'Database connection error',
            ], 500);
        }
        
        $period = $request->query('period', 'week');
        $data = [];
        
        switch ($period) {
            case 'week':
                $sql = "SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue 
                        FROM orders 
                        WHERE payment_status = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        GROUP BY DATE(created_at)
                        ORDER BY date ASC";
                break;
            case 'month':
                $sql = "SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue 
                        FROM orders 
                        WHERE payment_status = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY DATE(created_at)
                        ORDER BY date ASC";
                break;
            case 'year':
                $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as date, COALESCE(SUM(total), 0) as revenue 
                        FROM orders 
                        WHERE payment_status = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY date ASC";
                break;
            default:
                $sql = "SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue 
                        FROM orders 
                        WHERE payment_status = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        GROUP BY DATE(created_at)
                        ORDER BY date ASC";
        }
        
        $results = $db->select($sql, [PaymentStatus::PAID->value]);
        
        foreach ($results as $row) {
            $data[] = [
                'date' => $row['date'],
                'revenue' => (float) $row['revenue'],
            ];
        }
        
        return Response::json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
