<?php
/**
 * Admin Inventory Overview
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $summary
 * @var array $low_stock_products
 * @var array $out_of_stock_products
 * @var array $recent_movements
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">Inventory Overview</h2>
            <p class="text-sm text-gray-500">Monitor stock levels and movements</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/inventory/low-stock" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 font-medium rounded-lg hover:bg-yellow-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Low Stock (<?= $summary['low_stock_count'] ?? 0 ?>)
            </a>
            <a href="/admin/inventory/movements" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Stock History
            </a>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= number_format($summary['total_products'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Total Products</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= number_format($summary['total_units'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Total Units</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $summary['low_stock_count'] ?? 0 ?></p>
                    <p class="text-sm text-gray-500">Low Stock</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $summary['out_of_stock_count'] ?? 0 ?></p>
                    <p class="text-sm text-gray-500">Out of Stock</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Products -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-dark-brown">Low Stock Products</h3>
                <a href="/admin/inventory/low-stock" class="text-sm text-accent-orange hover:underline">View All</a>
            </div>
            <?php if (empty($low_stock_products)): ?>
                <div class="p-6 text-center text-gray-500">
                    All products have adequate stock levels.
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="<?= $view->e($product->getPrimaryImage()) ?>" alt="" class="w-10 h-10 rounded-lg object-cover">
                                <div>
                                    <p class="font-medium text-dark-brown"><?= $view->e($product->getName()) ?></p>
                                    <p class="text-sm text-gray-500">Threshold: <?= $product->attributes['low_stock_threshold'] ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                <?= $product->attributes['stock'] ?> left
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Out of Stock Products -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-dark-brown">Out of Stock Products</h3>
            </div>
            <?php if (empty($out_of_stock_products)): ?>
                <div class="p-6 text-center text-gray-500">
                    No products are out of stock.
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach (array_slice($out_of_stock_products, 0, 5) as $product): ?>
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="<?= $view->e($product->getPrimaryImage()) ?>" alt="" class="w-10 h-10 rounded-lg object-cover">
                                <p class="font-medium text-dark-brown"><?= $view->e($product->getName()) ?></p>
                            </div>
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                Out of Stock
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Stock Movements -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-semibold text-dark-brown">Recent Stock Movements</h3>
            <a href="/admin/inventory/movements" class="text-sm text-accent-orange hover:underline">View All</a>
        </div>
        <?php if (empty($recent_movements)): ?>
            <div class="p-6 text-center text-gray-500">
                No stock movements recorded yet.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recent_movements as $movement): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= date('M d, H:i', strtotime($movement->attributes['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-dark-brown">
                                    <?php $product = $movement->product(); ?>
                                    <?= $view->e($product['name_en'] ?? 'Unknown') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $movement->getTypeBadgeClass() ?>">
                                        <?= $movement->getTypeLabel() ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm <?= $movement->isPositive() ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $movement->isPositive() ? '+' : '-' ?><?= abs($movement->attributes['quantity']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= $movement->attributes['stock_after'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $view->endSection(); ?>
