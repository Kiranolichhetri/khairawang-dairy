<?php
/**
 * Admin Products List
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $products
 * @var array $categories
 * @var array $filters
 * @var array $pagination
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">All Products</h2>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?? 0 ?> products found</p>
        </div>
        <a href="/admin/products/create" class="inline-flex items-center gap-2 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Product
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="/admin/products" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="<?= $view->e($filters['search'] ?? '') ?>" 
                       placeholder="Search products..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Status</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category->getKey() ?>" <?= ($filters['category_id'] ?? '') == $category->getKey() ? 'selected' : '' ?>>
                        <?= $view->e($category->attributes['name_en'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Filter
            </button>
            <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['category_id'])): ?>
                <a href="/admin/products" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($products)): ?>
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No products found</h3>
                <p class="text-gray-500 mt-1">Get started by creating your first product.</p>
                <a href="/admin/products/create" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
                    Add Product
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= $view->e($product['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                                        <div>
                                            <p class="font-medium text-dark-brown"><?= $view->e($product['name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= $view->e($product['slug']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['sale_price']): ?>
                                        <p class="font-medium text-dark-brown">Rs. <?= number_format($product['sale_price'], 2) ?></p>
                                        <p class="text-sm text-gray-400 line-through">Rs. <?= number_format($product['price'], 2) ?></p>
                                    <?php else: ?>
                                        <p class="font-medium text-dark-brown">Rs. <?= number_format($product['price'], 2) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['stock'] <= $product['low_stock_threshold']): ?>
                                        <span class="inline-flex items-center gap-1 text-red-600">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <?= $product['stock'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-600"><?= $product['stock'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $statusColors = [
                                        'published' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-yellow-100 text-yellow-800',
                                        'archived' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusClass = $statusColors[$product['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/admin/products/<?= $product['id'] ?>/edit" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="/admin/products/<?= $product['id'] ?>" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <?= $view->csrf() ?>
                                            <?= $view->method('DELETE') ?>
                                            <button type="submit" 
                                                    class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100"
                                                    title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
                        to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                        of <?= $pagination['total'] ?> results
                    </p>
                    <div class="flex gap-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php $view->endSection(); ?>
