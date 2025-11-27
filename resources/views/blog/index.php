<?php
/**
 * Blog Listing Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $posts
 * @var array $pagination
 * @var array $sidebar
 * @var array $seo
 */
$view->extends('main');
?>

<?php $view->section('content'); ?>
<div class="min-h-screen bg-cream-light">
    <!-- Hero Section -->
    <div class="bg-dark-brown text-white py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-center">Our Blog</h1>
            <p class="text-center text-cream-light mt-2 max-w-2xl mx-auto">
                Discover recipes, health tips, and the latest news from Khairawang Dairy
            </p>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <?php if (empty($posts)): ?>
                    <div class="bg-white rounded-xl p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <h3 class="text-lg font-medium text-dark-brown">No posts yet</h3>
                        <p class="text-gray-500 mt-2">Check back soon for new articles!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-8">
                        <?php foreach ($posts as $post): ?>
                            <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                <a href="/blog/<?= $view->e($post->attributes['slug']) ?>" class="block">
                                    <img src="<?= $view->e($post->getFeaturedImageUrl()) ?>" 
                                         alt="<?= $view->e($post->getTitle()) ?>"
                                         class="w-full h-56 object-cover">
                                </a>
                                <div class="p-6">
                                    <?php if ($category = $post->category()): ?>
                                        <a href="/blog/category/<?= $view->e($category['slug'] ?? '') ?>" 
                                           class="text-sm text-accent-orange hover:underline">
                                            <?= $view->e($category['name'] ?? '') ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <h2 class="text-xl font-semibold text-dark-brown mt-2">
                                        <a href="/blog/<?= $view->e($post->attributes['slug']) ?>" class="hover:text-accent-orange">
                                            <?= $view->e($post->getTitle()) ?>
                                        </a>
                                    </h2>
                                    
                                    <p class="text-gray-600 mt-3 line-clamp-3">
                                        <?= $view->e($post->getExcerpt()) ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        <div class="flex items-center gap-3 text-sm text-gray-500">
                                            <?php if ($author = $post->author()): ?>
                                                <span><?= $view->e($author['name'] ?? 'Admin') ?></span>
                                                <span>•</span>
                                            <?php endif; ?>
                                            <span><?= date('M d, Y', strtotime($post->attributes['published_at'] ?? $post->attributes['created_at'])) ?></span>
                                        </div>
                                        <a href="/blog/<?= $view->e($post->attributes['slug']) ?>" 
                                           class="text-accent-orange font-medium hover:underline">
                                            Read More →
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                        <div class="mt-8 flex justify-center gap-2">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                                   class="px-4 py-2 bg-white rounded-lg shadow-sm hover:shadow-md">
                                    ← Previous
                                </a>
                            <?php endif; ?>
                            
                            <span class="px-4 py-2 text-gray-500">
                                Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?>
                            </span>
                            
                            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                                   class="px-4 py-2 bg-white rounded-lg shadow-sm hover:shadow-md">
                                    Next →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <?php $view->include('blog/sidebar', ['sidebar' => $sidebar]); ?>
            </aside>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
