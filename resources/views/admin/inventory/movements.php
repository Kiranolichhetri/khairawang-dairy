<?php
/**
 * Admin Stock Movements History
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $movements
 * @var \App\Models\Product|null $product
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="/admin/inventory" class="p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">
                <?= $product ? 'Stock History: ' . $view->e($product->getName()) : 'All Stock Movements' ?>
            </h2>
            <p class="text-sm text-gray-500">Track inventory changes over time</p>
        </div>
    </div>
    
    <?php if ($product): ?>
        <!-- Product Stock Adjustment Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-dark-brown mb-4">Adjust Stock</h3>
            <form action="/admin/inventory/<?= $product->getKey() ?>/adjust" method="POST" class="flex flex-wrap gap-4 items-end">
                <?= $view->csrf() ?>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" required
                           placeholder="e.g., 10 or -5"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <p class="text-xs text-gray-500 mt-1">Positive to add, negative to remove</p>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="adjustment">Adjustment</option>
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Reason for adjustment"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                <button type="submit" class="px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                    Update Stock
                </button>
            </form>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm">
                    <span class="font-medium">Current Stock:</span> 
                    <span class="text-lg font-bold text-dark-brown"><?= $product->attributes['stock'] ?></span>
                    <span class="text-gray-500 ml-2">(Threshold: <?= $product->attributes['low_stock_threshold'] ?>)</span>
                </p>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Movements Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($movements)): ?>
            <div class="p-8 text-center text-gray-500">
                No stock movements recorded.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <?php if (!$product): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($movements as $movement): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('M d, Y H:i', strtotime($movement->attributes['created_at'])) ?>
                                </td>
                                <?php if (!$product): ?>
                                    <td class="px-6 py-4">
                                        <?php $prod = $movement->product(); ?>
                                        <a href="/admin/inventory/movements?product_id=<?= $movement->attributes['product_id'] ?>" 
                                           class="text-dark-brown hover:text-accent-orange">
                                            <?= $view->e($prod['name_en'] ?? 'Unknown') ?>
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $movement->getTypeBadgeClass() ?>">
                                        <?= $movement->getTypeLabel() ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium <?= $movement->isPositive() ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $movement->isPositive() ? '+' : '' ?><?= $movement->attributes['quantity'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $movement->attributes['stock_before'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $movement->attributes['stock_after'] ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php if ($movement->attributes['reference_type']): ?>
                                        <?= ucfirst($movement->attributes['reference_type']) ?>
                                        <?php if ($movement->attributes['reference_id']): ?>
                                            #<?= $movement->attributes['reference_id'] ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    <?= $view->e($movement->attributes['notes'] ?? '-') ?>
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
