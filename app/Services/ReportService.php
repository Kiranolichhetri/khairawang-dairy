<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;
use Core\Application;
use Core\Database;

/**
 * Report Service
 * 
 * Handles generating various reports for the admin dashboard.
 */
class ReportService
{
    private ?Database $db = null;

    /**
     * Get database connection
     */
    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->db = $app->db();
            }
        }
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not available');
        }
        
        return $this->db;
    }

    /**
     * Get sales report data
     * 
     * @return array<string, mixed>
     */
    public function getSalesReport(string $dateFrom, string $dateTo, string $period = 'day'): array
    {
        $db = $this->db();
        
        // Total sales
        $totalSales = $db->selectOne(
            "SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as count 
             FROM orders 
             WHERE payment_status = ? AND DATE(created_at) BETWEEN ? AND ?",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        // Sales by period
        $groupFormat = match($period) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };
        
        $salesByPeriod = $db->select(
            "SELECT DATE_FORMAT(created_at, ?) as period, 
                    COALESCE(SUM(total), 0) as revenue, 
                    COUNT(*) as orders
             FROM orders 
             WHERE payment_status = ? AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY period
             ORDER BY period ASC",
            [$groupFormat, PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        // Sales by payment method
        $salesByPayment = $db->select(
            "SELECT payment_method, 
                    COALESCE(SUM(total), 0) as revenue, 
                    COUNT(*) as orders
             FROM orders 
             WHERE payment_status = ? AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY payment_method",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        // Orders by status
        $ordersByStatus = $db->select(
            "SELECT status, COUNT(*) as count
             FROM orders 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY status",
            [$dateFrom, $dateTo]
        );
        
        // Average order value
        $avgOrder = $totalSales['count'] > 0 
            ? (float) $totalSales['total'] / (int) $totalSales['count'] 
            : 0;
        
        return [
            'total_revenue' => (float) ($totalSales['total'] ?? 0),
            'total_orders' => (int) ($totalSales['count'] ?? 0),
            'average_order_value' => $avgOrder,
            'sales_by_period' => $salesByPeriod,
            'sales_by_payment' => $salesByPayment,
            'orders_by_status' => $ordersByStatus,
        ];
    }

    /**
     * Get product performance report
     * 
     * @return array<string, mixed>
     */
    public function getProductReport(string $dateFrom, string $dateTo, int $limit = 20): array
    {
        $db = $this->db();
        
        // Top selling products
        $topProducts = $db->select(
            "SELECT p.id, p.name_en as name, p.sku, p.price,
                    COALESCE(SUM(oi.quantity), 0) as units_sold,
                    COALESCE(SUM(oi.total), 0) as revenue
             FROM products p
             LEFT JOIN order_items oi ON p.id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.id 
                    AND o.payment_status = ? 
                    AND DATE(o.created_at) BETWEEN ? AND ?
             WHERE p.deleted_at IS NULL
             GROUP BY p.id
             ORDER BY units_sold DESC
             LIMIT ?",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo, $limit]
        );
        
        // Low performing products
        $lowProducts = $db->select(
            "SELECT p.id, p.name_en as name, p.sku, p.price, p.stock,
                    COALESCE(SUM(oi.quantity), 0) as units_sold
             FROM products p
             LEFT JOIN order_items oi ON p.id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.id 
                    AND o.payment_status = ? 
                    AND DATE(o.created_at) BETWEEN ? AND ?
             WHERE p.deleted_at IS NULL AND p.status = 'published'
             GROUP BY p.id
             HAVING units_sold = 0
             ORDER BY p.stock DESC
             LIMIT ?",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo, $limit]
        );
        
        // Sales by category
        $salesByCategory = $db->select(
            "SELECT c.id, c.name_en as name,
                    COALESCE(SUM(oi.quantity), 0) as units_sold,
                    COALESCE(SUM(oi.total), 0) as revenue
             FROM categories c
             LEFT JOIN products p ON c.id = p.category_id
             LEFT JOIN order_items oi ON p.id = oi.product_id
             LEFT JOIN orders o ON oi.order_id = o.id 
                    AND o.payment_status = ? 
                    AND DATE(o.created_at) BETWEEN ? AND ?
             GROUP BY c.id
             ORDER BY revenue DESC",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        return [
            'top_products' => $topProducts,
            'low_products' => $lowProducts,
            'sales_by_category' => $salesByCategory,
        ];
    }

    /**
     * Get customer report
     * 
     * @return array<string, mixed>
     */
    public function getCustomerReport(string $dateFrom, string $dateTo): array
    {
        $db = $this->db();
        
        // New customers
        $newCustomers = $db->selectOne(
            "SELECT COUNT(*) as count FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE r.name = 'customer' AND DATE(u.created_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );
        
        // Total customers
        $totalCustomers = $db->selectOne(
            "SELECT COUNT(*) as count FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             WHERE r.name = 'customer'"
        );
        
        // Top customers by orders
        $topCustomers = $db->select(
            "SELECT u.id, u.name, u.email,
                    COUNT(o.id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_spent
             FROM users u
             INNER JOIN roles r ON u.role_id = r.id
             LEFT JOIN orders o ON u.id = o.user_id 
                    AND o.payment_status = ?
                    AND DATE(o.created_at) BETWEEN ? AND ?
             WHERE r.name = 'customer'
             GROUP BY u.id
             HAVING total_orders > 0
             ORDER BY total_spent DESC
             LIMIT 10",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        // Repeat customers
        $repeatCustomers = $db->selectOne(
            "SELECT COUNT(DISTINCT user_id) as count FROM (
                SELECT user_id, COUNT(*) as order_count
                FROM orders
                WHERE payment_status = ? AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY user_id
                HAVING order_count > 1
             ) as repeat_orders",
            [PaymentStatus::PAID->value, $dateFrom, $dateTo]
        );
        
        return [
            'new_customers' => (int) ($newCustomers['count'] ?? 0),
            'total_customers' => (int) ($totalCustomers['count'] ?? 0),
            'repeat_customers' => (int) ($repeatCustomers['count'] ?? 0),
            'top_customers' => $topCustomers,
        ];
    }

    /**
     * Get inventory report
     * 
     * @return array<string, mixed>
     */
    public function getInventoryReport(): array
    {
        $db = $this->db();
        
        // Low stock products
        $lowStock = $db->select(
            "SELECT id, name_en as name, sku, stock, low_stock_threshold
             FROM products
             WHERE stock <= low_stock_threshold AND deleted_at IS NULL AND status = 'published'
             ORDER BY stock ASC"
        );
        
        // Out of stock products
        $outOfStock = $db->select(
            "SELECT id, name_en as name, sku
             FROM products
             WHERE stock = 0 AND deleted_at IS NULL AND status = 'published'"
        );
        
        // Total inventory value
        $inventoryValue = $db->selectOne(
            "SELECT COALESCE(SUM(price * stock), 0) as value
             FROM products
             WHERE deleted_at IS NULL"
        );
        
        // Products by status
        $productsByStatus = $db->select(
            "SELECT status, COUNT(*) as count
             FROM products
             WHERE deleted_at IS NULL
             GROUP BY status"
        );
        
        // Stock summary
        $stockSummary = $db->selectOne(
            "SELECT 
                COUNT(*) as total_products,
                SUM(stock) as total_units,
                AVG(stock) as avg_stock
             FROM products
             WHERE deleted_at IS NULL"
        );
        
        return [
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'inventory_value' => (float) ($inventoryValue['value'] ?? 0),
            'products_by_status' => $productsByStatus,
            'total_products' => (int) ($stockSummary['total_products'] ?? 0),
            'total_units' => (int) ($stockSummary['total_units'] ?? 0),
            'average_stock' => (float) ($stockSummary['avg_stock'] ?? 0),
        ];
    }

    /**
     * Export sales report to CSV
     */
    public function exportSalesReport(string $dateFrom, string $dateTo): string
    {
        $data = $this->getSalesReport($dateFrom, $dateTo);
        
        $csv = "Sales Report ({$dateFrom} to {$dateTo})\n\n";
        $csv .= "Summary\n";
        $csv .= "Total Revenue,{$data['total_revenue']}\n";
        $csv .= "Total Orders,{$data['total_orders']}\n";
        $csv .= "Average Order Value,{$data['average_order_value']}\n\n";
        
        $csv .= "Sales by Period\n";
        $csv .= "Period,Revenue,Orders\n";
        foreach ($data['sales_by_period'] as $row) {
            $csv .= "{$row['period']},{$row['revenue']},{$row['orders']}\n";
        }
        
        $csv .= "\nSales by Payment Method\n";
        $csv .= "Method,Revenue,Orders\n";
        foreach ($data['sales_by_payment'] as $row) {
            $csv .= "{$row['payment_method']},{$row['revenue']},{$row['orders']}\n";
        }
        
        return $csv;
    }

    /**
     * Export product report to CSV
     */
    public function exportProductReport(string $dateFrom, string $dateTo): string
    {
        $data = $this->getProductReport($dateFrom, $dateTo);
        
        $csv = "Product Performance Report ({$dateFrom} to {$dateTo})\n\n";
        $csv .= "Top Selling Products\n";
        $csv .= "ID,Name,SKU,Price,Units Sold,Revenue\n";
        foreach ($data['top_products'] as $row) {
            $name = str_replace(',', ' ', $row['name']);
            $csv .= "{$row['id']},\"{$name}\",{$row['sku']},{$row['price']},{$row['units_sold']},{$row['revenue']}\n";
        }
        
        return $csv;
    }

    /**
     * Export customer report to CSV
     */
    public function exportCustomerReport(string $dateFrom, string $dateTo): string
    {
        $data = $this->getCustomerReport($dateFrom, $dateTo);
        
        $csv = "Customer Report ({$dateFrom} to {$dateTo})\n\n";
        $csv .= "Summary\n";
        $csv .= "Total Customers,{$data['total_customers']}\n";
        $csv .= "New Customers,{$data['new_customers']}\n";
        $csv .= "Repeat Customers,{$data['repeat_customers']}\n\n";
        
        $csv .= "Top Customers\n";
        $csv .= "ID,Name,Email,Total Orders,Total Spent\n";
        foreach ($data['top_customers'] as $row) {
            $name = str_replace(',', ' ', $row['name']);
            $csv .= "{$row['id']},\"{$name}\",{$row['email']},{$row['total_orders']},{$row['total_spent']}\n";
        }
        
        return $csv;
    }

    /**
     * Export inventory report to CSV
     */
    public function exportInventoryReport(): string
    {
        $data = $this->getInventoryReport();
        
        $csv = "Inventory Report (" . date('Y-m-d') . ")\n\n";
        $csv .= "Summary\n";
        $csv .= "Total Products,{$data['total_products']}\n";
        $csv .= "Total Units,{$data['total_units']}\n";
        $csv .= "Inventory Value,{$data['inventory_value']}\n\n";
        
        $csv .= "Low Stock Products\n";
        $csv .= "ID,Name,SKU,Stock,Threshold\n";
        foreach ($data['low_stock'] as $row) {
            $name = str_replace(',', ' ', $row['name']);
            $csv .= "{$row['id']},\"{$name}\",{$row['sku']},{$row['stock']},{$row['low_stock_threshold']}\n";
        }
        
        return $csv;
    }
}
