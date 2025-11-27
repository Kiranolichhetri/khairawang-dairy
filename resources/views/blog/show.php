<?php
/**
 * Single Blog Post
 * 
 * @var \Core\View $view
 * @var string $title
 * @var \App\Models\BlogPost $post
 * @var array $related_posts
 * @var array $sidebar
 * @var array $seo
 * @var array $breadcrumbs
 * @var string $structured_data
 */
$view->extends('main');
?>

<?php $view->section('content'); ?>
<div class="min-h-screen bg-cream-light">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?>
                        <span class="text-gray-400">/</span>
                    <?php endif; ?>
                    <?php if ($i === count($breadcrumbs) - 1): ?>
                        <span class="text-gray-600 truncate max-w-xs"><?= $view->e($crumb['name']) ?></span>
                    <?php else: ?>
                        <a href="<?= $crumb['url'] ?>" class="text-accent-orange hover:underline"><?= $view->e($crumb['name']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    
    <!-- Featured Image -->
    <div class="relative h-64 md:h-96 bg-dark-brown">
        <img src="<?= $view->e($post->getFeaturedImageUrl()) ?>" 
             alt="<?= $view->e($post->getTitle()) ?>"
             class="w-full h-full object-cover opacity-80">
        <div class="absolute inset-0 bg-gradient-to-t from-dark-brown/80 to-transparent"></div>
    </div>
    
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <article class="bg-white rounded-xl shadow-sm overflow-hidden -mt-24 relative">
                    <div class="p-6 md:p-8">
                        <!-- Category & Date -->
                        <div class="flex items-center gap-4 text-sm mb-4">
                            <?php if ($category = $post->category()): ?>
                                <a href="/blog/category/<?= $view->e($category['slug'] ?? '') ?>" 
                                   class="px-3 py-1 bg-accent-orange text-white rounded-full text-xs font-medium">
                                    <?= $view->e($category['name'] ?? '') ?>
                                </a>
                            <?php endif; ?>
                            <span class="text-gray-500">
                                <?= date('F d, Y', strtotime($post->attributes['published_at'] ?? $post->attributes['created_at'])) ?>
                            </span>
                            <span class="text-gray-500">
                                <?= $post->attributes['views_count'] ?? 0 ?> views
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="text-2xl md:text-3xl font-bold text-dark-brown mb-6">
                            <?= $view->e($post->getTitle()) ?>
                        </h1>
                        
                        <!-- Author -->
                        <?php if ($author = $post->author()): ?>
                            <div class="flex items-center gap-3 pb-6 border-b border-gray-100">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-dark-brown"><?= $view->e($author['name'] ?? 'Admin') ?></p>
                                    <p class="text-sm text-gray-500">Author</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Content -->
                        <div class="prose prose-lg max-w-none mt-6">
                            <?= $post->getContent() ?>
                        </div>
                        
                        <!-- Tags -->
                        <?php if ($tags = $post->tags()): ?>
                            <div class="mt-8 pt-6 border-t border-gray-100">
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($tags as $tag): ?>
                                        <a href="/blog/tag/<?= $view->e($tag['slug']) ?>" 
                                           class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm hover:bg-gray-200">
                                            #<?= $view->e($tag['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Social Sharing -->
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-500 mb-3">Share this article</p>
                            <div class="flex gap-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($seo['canonical'] ?? '') ?>" 
                                   target="_blank" rel="noopener"
                                   class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                    </svg>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($seo['canonical'] ?? '') ?>&text=<?= urlencode($post->getTitle()) ?>" 
                                   target="_blank" rel="noopener"
                                   class="p-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                                    </svg>
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?= urlencode($post->getTitle() . ' ' . ($seo['canonical'] ?? '')) ?>" 
                                   target="_blank" rel="noopener"
                                   class="p-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
                
                <!-- Related Posts -->
                <?php if (!empty($related_posts)): ?>
                    <div class="mt-12">
                        <h2 class="text-xl font-bold text-dark-brown mb-6">Related Articles</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($related_posts as $related): ?>
                                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <a href="/blog/<?= $view->e($related->attributes['slug']) ?>" class="block">
                                        <img src="<?= $view->e($related->getFeaturedImageUrl()) ?>" 
                                             alt="<?= $view->e($related->getTitle()) ?>"
                                             class="w-full h-40 object-cover">
                                    </a>
                                    <div class="p-4">
                                        <h3 class="font-semibold text-dark-brown">
                                            <a href="/blog/<?= $view->e($related->attributes['slug']) ?>" class="hover:text-accent-orange">
                                                <?= $view->e($related->getTitle()) ?>
                                            </a>
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-2">
                                            <?= date('M d, Y', strtotime($related->attributes['published_at'] ?? $related->attributes['created_at'])) ?>
                                        </p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <?php $view->include('blog/sidebar', ['sidebar' => $sidebar]); ?>
            </aside>
        </div>
    </div>
</div>

<!-- Structured Data -->
<?= $structured_data ?>
<?php $view->endSection(); ?>
