<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Services\InventoryService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Admin Inventory Controller
 * 
 * Handles inventory management in the admin panel.
 */
class InventoryController
{
    private InventoryService $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
    }

    /**
     * Stock overview dashboard
     */
    public function index(Request $request): Response
    {
        $summary = $this->inventoryService->getInventorySummary();
        $lowStockProducts = $this->inventoryService->checkLowStock();
        $outOfStockProducts = $this->inventoryService->getOutOfStock();
        $recentMovements = $this->inventoryService->getRecentMovements(10);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'low_stock' => array_map(fn($p) => $this->formatProduct($p), $lowStockProducts),
                    'out_of_stock' => array_map(fn($p) => $this->formatProduct($p), $outOfStockProducts),
                    'recent_movements' => array_map(fn($m) => $this->formatMovement($m), $recentMovements),
                ],
            ]);
        }
        
        return Response::view('admin.inventory.index', [
            'title' => 'Inventory Overview',
            'summary' => $summary,
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'recent_movements' => $recentMovements,
        ]);
    }

    /**
     * Low stock products listing
     */
    public function lowStock(Request $request): Response
    {
        $products = $this->inventoryService->checkLowStock();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'products' => array_map(fn($p) => $this->formatProduct($p), $products),
                    'count' => count($products),
                ],
            ]);
        }
        
        return Response::view('admin.inventory.low-stock', [
            'title' => 'Low Stock Products',
            'products' => $products,
        ]);
    }

    /**
     * Stock movements history
     */
    public function movements(Request $request): Response
    {
        $productId = $request->query('product_id');
        $limit = min(100, max(10, (int) $request->query('limit', 50)));
        
        if ($productId) {
            $movements = $this->inventoryService->getStockHistory((int) $productId, $limit);
            $product = Product::find((int) $productId);
        } else {
            $movements = $this->inventoryService->getRecentMovements($limit);
            $product = null;
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'movements' => array_map(fn($m) => $this->formatMovement($m), $movements),
                    'product' => $product ? $this->formatProduct($product) : null,
                ],
            ]);
        }
        
        return Response::view('admin.inventory.movements', [
            'title' => $product ? 'Stock History: ' . $product->getName() : 'Stock Movements',
            'movements' => $movements,
            'product' => $product,
        ]);
    }

    /**
     * Adjust stock for a product
     */
    public function adjust(Request $request, string $productId): Response
    {
        $product = Product::find((int) $productId);
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Product not found.');
            
            return Response::redirect('/admin/inventory');
        }
        
        $quantity = (int) $request->input('quantity', 0);
        $type = $request->input('type', 'adjustment');
        $notes = $request->input('notes');
        
        if ($quantity === 0) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Quantity cannot be zero',
                ], 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Quantity cannot be zero.');
            
            return Response::redirect('/admin/inventory/movements?product_id=' . $productId);
        }
        
        // Get current user ID
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        $result = $this->inventoryService->updateStock(
            (int) $productId,
            $quantity,
            $type,
            $notes,
            $userId ? (int) $userId : null
        );
        
        if ($request->expectsJson()) {
            return Response::json($result);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success('Stock updated successfully!');
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/admin/inventory/movements?product_id=' . $productId);
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkAdjust(Request $request): Response
    {
        $items = $request->input('items', []);
        $notes = $request->input('notes');
        
        if (empty($items) || !is_array($items)) {
            return Response::json([
                'success' => false,
                'message' => 'No items provided',
            ], 400);
        }
        
        // Get current user ID
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        
        $result = $this->inventoryService->bulkUpdateStock(
            $items,
            $userId ? (int) $userId : null,
            $notes
        );
        
        return Response::json($result);
    }

    /**
     * Format product for API response
     * 
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->getKey(),
            'name' => $product->getName(),
            'sku' => $product->attributes['sku'] ?? '',
            'stock' => (int) ($product->attributes['stock'] ?? 0),
            'low_stock_threshold' => (int) ($product->attributes['low_stock_threshold'] ?? 10),
            'is_low_stock' => $product->isLowStock(),
            'is_in_stock' => $product->isInStock(),
            'stock_status' => $product->getStockStatus(),
            'price' => $product->getCurrentPrice(),
            'image' => $product->getPrimaryImage(),
        ];
    }

    /**
     * Format stock movement for API response
     * 
     * @return array<string, mixed>
     */
    private function formatMovement($movement): array
    {
        return [
            'id' => $movement->getKey(),
            'product_id' => $movement->attributes['product_id'] ?? null,
            'type' => $movement->attributes['type'] ?? '',
            'type_label' => $movement->getTypeLabel(),
            'type_badge_class' => $movement->getTypeBadgeClass(),
            'quantity' => (int) ($movement->attributes['quantity'] ?? 0),
            'stock_before' => (int) ($movement->attributes['stock_before'] ?? 0),
            'stock_after' => (int) ($movement->attributes['stock_after'] ?? 0),
            'reference_type' => $movement->attributes['reference_type'] ?? null,
            'reference_id' => $movement->attributes['reference_id'] ?? null,
            'notes' => $movement->attributes['notes'] ?? null,
            'created_by' => $movement->createdBy(),
            'created_at' => $movement->attributes['created_at'] ?? null,
        ];
    }
}
