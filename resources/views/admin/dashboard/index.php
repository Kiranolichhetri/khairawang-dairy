<?php
/**
 * Admin Dashboard
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $stats
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                    <p class="text-2xl font-bold text-dark-brown mt-1">
                        Rs. <?= number_format($stats['revenue']['today'] ?? 0, 2) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                <?= (int)($stats['orders']['today'] ?? 0) ?> orders today
            </p>
        </div>
        
        <!-- Monthly Revenue -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Monthly Revenue</p>
                    <p class="text-2xl font-bold text-dark-brown mt-1">
                        Rs. <?= number_format($stats['revenue']['month'] ?? 0, 2) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Orders</p>
                    <p class="text-2xl font-bold text-dark-brown mt-1">
                        <?= number_format($stats['orders']['total'] ?? 0) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-accent-orange/10 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                <?= (int)($stats['orders']['pending'] ?? 0) ?> pending
            </p>
        </div>
        
        <!-- Total Customers -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Customers</p>
                    <p class="text-2xl font-bold text-dark-brown mt-1">
                        <?= number_format($stats['customers']['total'] ?? 0) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Status Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600"><?= (int)($stats['orders']['pending'] ?? 0) ?></p>
            <p class="text-sm text-yellow-700">Pending</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-600"><?= (int)($stats['orders']['processing'] ?? 0) ?></p>
            <p class="text-sm text-blue-700">Processing</p>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600"><?= (int)($stats['orders']['shipped'] ?? 0) ?></p>
            <p class="text-sm text-indigo-700">Shipped</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-green-600"><?= (int)($stats['orders']['delivered'] ?? 0) ?></p>
            <p class="text-sm text-green-700">Delivered</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-red-600"><?= (int)($stats['orders']['cancelled'] ?? 0) ?></p>
            <p class="text-sm text-red-700">Cancelled</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-600"><?= (int)($stats['orders']['total'] ?? 0) ?></p>
            <p class="text-sm text-gray-700">Total</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-dark-brown">Recent Orders</h2>
                <a href="/admin/orders" class="text-sm text-accent-orange hover:text-accent-orange-dark">View All →</a>
            </div>
            <div class="p-6">
                <?php if (empty($stats['recent_orders'])): ?>
                    <p class="text-gray-500 text-center py-4">No orders yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($stats['recent_orders'], 0, 5) as $order): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-dark-brown"><?= $view->e($order['order_number']) ?></p>
                                    <p class="text-sm text-gray-500"><?= $view->e($order['customer_name']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-dark-brown">Rs. <?= number_format($order['total'], 2) ?></p>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" 
                                          style="background-color: <?= $view->e($order['status_color']) ?>20; color: <?= $view->e($order['status_color']) ?>;">
                                        <?= $view->e($order['status_label']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-dark-brown">Low Stock Alert</h2>
                <a href="/admin/products?filter=low_stock" class="text-sm text-accent-orange hover:text-accent-orange-dark">View All →</a>
            </div>
            <div class="p-6">
                <?php if (empty($stats['products']['low_stock'])): ?>
                    <p class="text-gray-500 text-center py-4">All products are well stocked!</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($stats['products']['low_stock'] as $product): ?>
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                <div>
                                    <p class="font-medium text-dark-brown"><?= $view->e($product['name']) ?></p>
                                    <p class="text-xs text-gray-500">Threshold: <?= (int)$product['low_stock_threshold'] ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-red-600"><?= (int)$product['stock'] ?></p>
                                    <p class="text-xs text-red-500">units left</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Selling Products -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-dark-brown">Top Selling Products</h2>
            <a href="/admin/reports/products" class="text-sm text-accent-orange hover:text-accent-orange-dark">View Report →</a>
        </div>
        <div class="p-6">
            <?php if (empty($stats['products']['top_selling'])): ?>
                <p class="text-gray-500 text-center py-4">No sales data yet</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-3">Product</th>
                                <th class="pb-3 text-right">Price</th>
                                <th class="pb-3 text-right">Units Sold</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($stats['products']['top_selling'] as $product): ?>
                                <tr>
                                    <td class="py-3">
                                        <p class="font-medium text-dark-brown"><?= $view->e($product['name']) ?></p>
                                    </td>
                                    <td class="py-3 text-right text-gray-600">Rs. <?= number_format($product['price'], 2) ?></td>
                                    <td class="py-3 text-right font-semibold text-dark-brown"><?= number_format($product['total_sold']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
