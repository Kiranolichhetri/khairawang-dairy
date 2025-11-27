<?php
/**
 * Admin Edit Product
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $product
 * @var array $categories
 * @var array $statuses
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="/admin/products" class="text-sm text-gray-500 hover:text-accent-orange">
            ← Back to Products
        </a>
    </div>
    
    <form action="/admin/products/<?= $view->e($product['id']) ?>" method="POST" class="space-y-6">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                    <input type="text" id="name_en" name="name_en" required 
                           value="<?= $view->e($view->old('name_en', $product['name_en'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('name_en') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('name_en')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name_en')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="name_ne" class="block text-sm font-medium text-gray-700 mb-1">Name (Nepali)</label>
                    <input type="text" id="name_ne" name="name_ne" 
                           value="<?= $view->e($view->old('name_ne', $product['name_ne'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug *</label>
                    <input type="text" id="slug" name="slug" required 
                           value="<?= $view->e($view->old('slug', $product['slug'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('slug') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('slug')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('slug')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select id="category_id" name="category_id" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->getKey() ?>" <?= ($view->old('category_id', $product['category_id'] ?? '')) == $category->getKey() ? 'selected' : '' ?>>
                                <?= $view->e($category->attributes['name_en'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <input type="text" id="short_description" name="short_description" 
                           value="<?= $view->e($view->old('short_description', $product['short_description'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div class="md:col-span-2">
                    <label for="description_en" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea id="description_en" name="description_en" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange"><?= $view->e($view->old('description_en', $product['description_en'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Pricing & Inventory</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (NPR) *</label>
                    <input type="number" id="price" name="price" required step="0.01" min="0"
                           value="<?= $view->e($view->old('price', $product['price'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('price') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price (NPR)</label>
                    <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                           value="<?= $view->e($view->old('sale_price', $product['sale_price'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" id="sku" name="sku"
                           value="<?= $view->e($view->old('sku', $product['sku'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" min="0"
                           value="<?= $view->e($view->old('stock', $product['stock'] ?? '0')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert</label>
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0"
                           value="<?= $view->e($view->old('low_stock_threshold', $product['low_stock_threshold'] ?? '10')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0"
                           value="<?= $view->e($view->old('weight', $product['weight'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
            </div>
        </div>
        
        <!-- Status & Options -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Status & Options</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select id="status" name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="draft" <?= ($view->old('status', $product['status'] ?? '')) === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($view->old('status', $product['status'] ?? '')) === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= ($view->old('status', $product['status'] ?? '')) === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <div class="flex items-center pt-6">
                    <input type="checkbox" id="featured" name="featured" value="1"
                           <?= $view->old('featured', $product['featured'] ?? false) ? 'checked' : '' ?>
                           class="h-4 w-4 text-accent-orange focus:ring-accent-orange border-gray-300 rounded">
                    <label for="featured" class="ml-2 text-sm text-gray-700">Featured Product</label>
                </div>
            </div>
        </div>
        
        <!-- SEO -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">SEO</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                    <input type="text" id="seo_title" name="seo_title"
                           value="<?= $view->e($view->old('seo_title', $product['seo_title'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div>
                    <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                    <textarea id="seo_description" name="seo_description" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange"><?= $view->e($view->old('seo_description', $product['seo_description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="/admin/products" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Update Product
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
