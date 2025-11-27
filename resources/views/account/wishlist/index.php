<?php
/**
 * Wishlist Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $items
 * @var int $count
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'My Wishlist') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-heading font-bold text-dark-brown">
            My Wishlist
            <?php if ($count > 0): ?>
                <span class="text-lg font-normal text-gray-500">(<?= $count ?> items)</span>
            <?php endif; ?>
        </h1>
        <?php if (!empty($items)): ?>
            <form action="/account/wishlist/clear" method="POST" onsubmit="return confirm('Clear all items from wishlist?')">
                <?= $view->csrf() ?>
                <?= $view->method('DELETE') ?>
                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                    Clear All
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-brown mb-2">Your wishlist is empty</h3>
            <p class="text-gray-500 mb-6">Save items you love to your wishlist!</p>
            <a href="/products" class="inline-flex items-center gap-2 px-6 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <!-- Wishlist Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($items as $item): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden group">
                    <!-- Product Image -->
                    <a href="/products/<?= $view->e($item['slug']) ?>" class="block relative aspect-square overflow-hidden">
                        <img src="<?= $view->e($item['image']) ?>" 
                             alt="<?= $view->e($item['name']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        
                        <?php if ($item['on_sale']): ?>
                            <span class="absolute top-2 left-2 px-2 py-1 bg-red-500 text-white text-xs font-medium rounded">
                                Sale
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!$item['in_stock']): ?>
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <span class="px-3 py-1 bg-gray-800 text-white text-sm font-medium rounded">Out of Stock</span>
                            </div>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Product Info -->
                    <div class="p-4">
                        <a href="/products/<?= $view->e($item['slug']) ?>" class="block">
                            <h3 class="font-medium text-dark-brown group-hover:text-accent-orange transition-colors line-clamp-2">
                                <?= $view->e($item['name']) ?>
                            </h3>
                        </a>
                        
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-lg font-bold text-accent-orange">
                                <?= $view->currency($item['price']) ?>
                            </span>
                            <?php if ($item['on_sale']): ?>
                                <span class="text-sm text-gray-400 line-through">
                                    <?= $view->currency($item['original_price']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-4">
                            <?php if ($item['available']): ?>
                                <form action="/account/wishlist/<?= $item['product_id'] ?>/move-to-cart" method="POST" class="flex-1">
                                    <?= $view->csrf() ?>
                                    <button type="submit" class="w-full px-4 py-2 bg-accent-orange text-white text-sm font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
                                        Move to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="flex-1 px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                    Unavailable
                                </button>
                            <?php endif; ?>
                            
                            <form action="/account/wishlist/<?= $item['product_id'] ?>" method="POST">
                                <?= $view->csrf() ?>
                                <?= $view->method('DELETE') ?>
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Remove from wishlist">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $view->endSection(); ?>
