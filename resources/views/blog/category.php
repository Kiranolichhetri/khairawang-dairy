<?php
/**
 * Blog Category Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var \App\Models\BlogCategory $category
 * @var array $posts
 * @var array $pagination
 * @var array $sidebar
 * @var array $seo
 * @var array $breadcrumbs
 */
$view->extends('main');
?>

<?php $view->section('content'); ?>
<div class="min-h-screen bg-cream-light">
    <!-- Hero Section -->
    <div class="bg-dark-brown text-white py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-center"><?= $view->e($category->attributes['name']) ?></h1>
            <?php if ($category->attributes['description']): ?>
                <p class="text-center text-cream-light mt-2 max-w-2xl mx-auto">
                    <?= $view->e($category->attributes['description']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Breadcrumbs -->
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?>
                        <span class="text-gray-400">/</span>
                    <?php endif; ?>
                    <?php if ($i === count($breadcrumbs) - 1): ?>
                        <span class="text-gray-600"><?= $view->e($crumb['name']) ?></span>
                    <?php else: ?>
                        <a href="<?= $crumb['url'] ?>" class="text-accent-orange hover:underline"><?= $view->e($crumb['name']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <?php if (empty($posts)): ?>
                    <div class="bg-white rounded-xl p-8 text-center">
                        <h3 class="text-lg font-medium text-dark-brown">No posts in this category</h3>
                        <p class="text-gray-500 mt-2">Check back soon!</p>
                        <a href="/blog" class="inline-block mt-4 text-accent-orange hover:underline">← Back to Blog</a>
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
                                    <h2 class="text-xl font-semibold text-dark-brown">
                                        <a href="/blog/<?= $view->e($post->attributes['slug']) ?>" class="hover:text-accent-orange">
                                            <?= $view->e($post->getTitle()) ?>
                                        </a>
                                    </h2>
                                    
                                    <p class="text-gray-600 mt-3 line-clamp-3">
                                        <?= $view->e($post->getExcerpt()) ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        <span class="text-sm text-gray-500">
                                            <?= date('M d, Y', strtotime($post->attributes['published_at'] ?? $post->attributes['created_at'])) ?>
                                        </span>
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
