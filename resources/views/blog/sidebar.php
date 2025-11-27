<?php
/**
 * Blog Sidebar
 * 
 * @var \Core\View $view
 * @var array $sidebar
 */
$recentPosts = $sidebar['recent_posts'] ?? [];
$categories = $sidebar['categories'] ?? [];
$popularTags = $sidebar['popular_tags'] ?? [];
?>

<div class="space-y-6">
    <!-- Search -->
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <h3 class="font-semibold text-dark-brown mb-4">Search</h3>
        <form action="/blog/search" method="GET">
            <div class="flex gap-2">
                <input type="text" name="q" placeholder="Search articles..." required
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <button type="submit" class="p-2 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Categories -->
    <?php if (!empty($categories)): ?>
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-dark-brown mb-4">Categories</h3>
            <ul class="space-y-2">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="/blog/category/<?= $view->e($category->attributes['slug']) ?>" 
                           class="flex items-center justify-between text-gray-600 hover:text-accent-orange">
                            <span><?= $view->e($category->attributes['name']) ?></span>
                            <span class="text-sm text-gray-400">(<?= $category->getPostCount() ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Recent Posts -->
    <?php if (!empty($recentPosts)): ?>
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-dark-brown mb-4">Recent Posts</h3>
            <ul class="space-y-4">
                <?php foreach ($recentPosts as $post): ?>
                    <li>
                        <a href="/blog/<?= $view->e($post->attributes['slug']) ?>" class="group flex gap-3">
                            <img src="<?= $view->e($post->getFeaturedImageUrl()) ?>" 
                                 alt="" 
                                 class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                            <div>
                                <h4 class="text-sm font-medium text-dark-brown group-hover:text-accent-orange line-clamp-2">
                                    <?= $view->e($post->getTitle()) ?>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= date('M d, Y', strtotime($post->attributes['published_at'] ?? $post->attributes['created_at'])) ?>
                                </p>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Popular Tags -->
    <?php if (!empty($popularTags)): ?>
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-dark-brown mb-4">Popular Tags</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($popularTags as $tag): ?>
                    <a href="/blog/tag/<?= $view->e($tag->attributes['slug']) ?>" 
                       class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm hover:bg-accent-orange hover:text-white transition-colors">
                        #<?= $view->e($tag->attributes['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Newsletter -->
    <div class="bg-accent-orange rounded-xl p-6 text-white">
        <h3 class="font-semibold mb-2">Subscribe to Newsletter</h3>
        <p class="text-sm text-cream-light mb-4">Get the latest updates delivered to your inbox.</p>
        <form action="/newsletter/subscribe" method="POST">
            <?= $view->csrf() ?>
            <input type="email" name="email" placeholder="Your email address" required
                   class="w-full px-4 py-2 rounded-lg text-dark-brown mb-2 focus:outline-none">
            <button type="submit" class="w-full px-4 py-2 bg-dark-brown text-white rounded-lg hover:bg-dark-brown/90">
                Subscribe
            </button>
        </form>
    </div>
</div>
