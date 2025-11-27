<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Core\Application;

/**
 * Inventory Service
 * 
 * Handles inventory management, stock alerts, and movement tracking.
 */
class InventoryService
{
    private ?EmailService $emailService = null;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService;
    }

    /**
     * Get products below their low stock threshold
     * 
     * @return array<Product>
     */
    public function checkLowStock(): array
    {
        return Product::lowStock();
    }

    /**
     * Get out of stock products
     * 
     * @return array<Product>
     */
    public function getOutOfStock(): array
    {
        return Product::outOfStock();
    }

    /**
     * Update stock for a product
     * 
     * @return array<string, mixed>
     */
    public function updateStock(
        int $productId,
        int $quantity,
        string $type = 'adjustment',
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        $product = Product::find($productId);
        
        if ($product === null) {
            return [
                'success' => false,
                'message' => 'Product not found',
            ];
        }
        
        $currentStock = $product->attributes['stock'] ?? 0;
        $stockBefore = $currentStock;
        
        switch ($type) {
            case StockMovement::TYPE_IN:
                $newStock = $currentStock + abs($quantity);
                break;
                
            case StockMovement::TYPE_OUT:
                $newStock = max(0, $currentStock - abs($quantity));
                break;
                
            case StockMovement::TYPE_ADJUSTMENT:
                $newStock = max(0, $currentStock + $quantity);
                break;
                
            case StockMovement::TYPE_RESERVED:
                $newStock = max(0, $currentStock - abs($quantity));
                break;
                
            case StockMovement::TYPE_RELEASED:
                $newStock = $currentStock + abs($quantity);
                break;
                
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid stock movement type',
                ];
        }
        
        // Update product stock
        $product->stock = $newStock;
        $product->save();
        
        // Create stock movement record
        StockMovement::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
            'notes' => $notes,
            'created_by' => $userId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
        
        // Check for low stock alert
        $this->checkAndSendLowStockAlert($product);
        
        return [
            'success' => true,
            'message' => 'Stock updated successfully',
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
        ];
    }

    /**
     * Reserve stock for an order
     * 
     * @return array<string, mixed>
     */
    public function reserveStock(int $productId, int $quantity, ?int $orderId = null): array
    {
        return $this->updateStock(
            $productId,
            $quantity,
            StockMovement::TYPE_RESERVED,
            'Reserved for order',
            null,
            StockMovement::REF_ORDER,
            $orderId
        );
    }

    /**
     * Release reserved stock
     * 
     * @return array<string, mixed>
     */
    public function releaseStock(int $productId, int $quantity, ?int $orderId = null, ?string $notes = null): array
    {
        return $this->updateStock(
            $productId,
            $quantity,
            StockMovement::TYPE_RELEASED,
            $notes ?? 'Released stock',
            null,
            StockMovement::REF_ORDER,
            $orderId
        );
    }

    /**
     * Process stock for order (stock out)
     * 
     * @return array<string, mixed>
     */
    public function processOrderStock(int $productId, int $quantity, int $orderId): array
    {
        return $this->updateStock(
            $productId,
            $quantity,
            StockMovement::TYPE_OUT,
            'Order placed',
            null,
            StockMovement::REF_ORDER,
            $orderId
        );
    }

    /**
     * Restore stock for cancelled/returned order
     * 
     * @return array<string, mixed>
     */
    public function restoreOrderStock(int $productId, int $quantity, int $orderId, string $reason = 'Order cancelled'): array
    {
        return $this->updateStock(
            $productId,
            $quantity,
            StockMovement::TYPE_IN,
            $reason,
            null,
            StockMovement::REF_RETURN,
            $orderId
        );
    }

    /**
     * Get stock movement history for a product
     * 
     * @return array<StockMovement>
     */
    public function getStockHistory(int $productId, int $limit = 50): array
    {
        return StockMovement::forProduct($productId, $limit);
    }

    /**
     * Get recent stock movements across all products
     * 
     * @return array<StockMovement>
     */
    public function getRecentMovements(int $limit = 50): array
    {
        return StockMovement::recent($limit);
    }

    /**
     * Check and send low stock alert
     */
    protected function checkAndSendLowStockAlert(Product $product): void
    {
        $notifyLowStock = $product->attributes['notify_low_stock'] ?? true;
        
        if (!$notifyLowStock) {
            return;
        }
        
        if (!$product->isLowStock() && $product->isInStock()) {
            return;
        }
        
        // Send alert (in a real app, this might queue the email)
        $this->sendLowStockAlert([$product]);
    }

    /**
     * Send low stock alert to admin
     * 
     * @param array<Product> $products
     */
    public function sendLowStockAlert(array $products): bool
    {
        if (empty($products) || $this->emailService === null) {
            return false;
        }
        
        $adminEmail = config('app.admin_email', 'admin@khairawangdairy.com');
        
        $productList = [];
        foreach ($products as $product) {
            $productList[] = [
                'name' => $product->getName(),
                'stock' => $product->attributes['stock'] ?? 0,
                'threshold' => $product->attributes['low_stock_threshold'] ?? 10,
            ];
        }
        
        // In a real implementation, this would send an email
        // For now, we'll just log the alert
        error_log(sprintf(
            'Low stock alert: %d products are low on stock',
            count($products)
        ));
        
        return true;
    }

    /**
     * Get inventory summary statistics
     * 
     * @return array<string, mixed>
     */
    public function getInventorySummary(): array
    {
        $totalProducts = Product::count();
        $lowStockProducts = count($this->checkLowStock());
        $outOfStockProducts = count($this->getOutOfStock());
        
        // Calculate total inventory value
        $products = Product::all();
        $totalValue = 0.0;
        $totalUnits = 0;
        
        foreach ($products as $product) {
            $stock = $product->attributes['stock'] ?? 0;
            $price = $product->getCurrentPrice();
            $totalValue += $stock * $price;
            $totalUnits += $stock;
        }
        
        return [
            'total_products' => $totalProducts,
            'total_units' => $totalUnits,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockProducts,
            'out_of_stock_count' => $outOfStockProducts,
            'in_stock_count' => $totalProducts - $outOfStockProducts,
        ];
    }

    /**
     * Bulk update stock
     * 
     * @param array<array{product_id: int, quantity: int, type?: string}> $items
     * @return array<string, mixed>
     */
    public function bulkUpdateStock(array $items, ?int $userId = null, ?string $notes = null): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? 0;
            $quantity = $item['quantity'] ?? 0;
            $type = $item['type'] ?? StockMovement::TYPE_ADJUSTMENT;
            
            if ($productId <= 0) {
                $results[] = [
                    'product_id' => $productId,
                    'success' => false,
                    'message' => 'Invalid product ID',
                ];
                $errorCount++;
                continue;
            }
            
            $result = $this->updateStock($productId, $quantity, $type, $notes, $userId);
            $result['product_id'] = $productId;
            $results[] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        return [
            'success' => $errorCount === 0,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results,
        ];
    }
}
