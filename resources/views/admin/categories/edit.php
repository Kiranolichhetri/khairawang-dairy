<?php
/**
 * Admin Edit Category
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $category
 * @var array $parentCategories
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/admin/categories" class="text-sm text-gray-500 hover:text-accent-orange">
            ← Back to Categories
        </a>
    </div>
    
    <form action="/admin/categories/<?= $view->e($category['id']) ?>" method="POST" class="space-y-6">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Category Information</h3>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name_en" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                        <input type="text" id="name_en" name="name_en" required 
                               value="<?= $view->e($view->old('name_en', $category['name_en'] ?? '')) ?>"
                               class="w-full px-4 py-2 border <?= $view->hasError('name_en') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <?php if ($view->hasError('name_en')): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name_en')) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="name_ne" class="block text-sm font-medium text-gray-700 mb-1">Name (Nepali)</label>
                        <input type="text" id="name_ne" name="name_ne" 
                               value="<?= $view->e($view->old('name_ne', $category['name_ne'] ?? '')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    </div>
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug *</label>
                    <input type="text" id="slug" name="slug" required 
                           value="<?= $view->e($view->old('slug', $category['slug'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('slug') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('slug')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('slug')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                    <select id="parent_id" name="parent_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                        <option value="">None (Top Level)</option>
                        <?php foreach ($parentCategories as $cat): ?>
                            <option value="<?= $cat->getKey() ?>" <?= ($view->old('parent_id', $category['parent_id'] ?? '')) == $cat->getKey() ? 'selected' : '' ?>>
                                <?= $view->e($cat->attributes['name_en'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($view->hasError('parent_id')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('parent_id')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange"><?= $view->e($view->old('description', $category['description'] ?? '')) ?></textarea>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" id="image" name="image" 
                           value="<?= $view->e($view->old('image', $category['image'] ?? '')) ?>"
                           placeholder="https://example.com/image.jpg"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                        <input type="number" id="display_order" name="display_order" min="0"
                               value="<?= $view->e($view->old('display_order', $category['display_order'] ?? '0')) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                            <option value="active" <?= ($view->old('status', $category['status'] ?? '')) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($view->old('status', $category['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-4">
            <a href="/admin/categories" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Update Category
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
