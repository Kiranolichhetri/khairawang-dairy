<?php
/**
 * Admin Blog Posts List
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $posts
 * @var array $pagination
 * @var string|null $status_filter
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">Blog Posts</h2>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?? 0 ?> posts found</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/blog/categories" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Categories
            </a>
            <a href="/admin/blog/create" class="inline-flex items-center gap-2 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Post
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="/admin/blog" method="GET" class="flex flex-wrap gap-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Status</option>
                <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Filter
            </button>
            <?php if ($status_filter): ?>
                <a href="/admin/blog" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Posts Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($posts)): ?>
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No posts found</h3>
                <p class="text-gray-500 mt-1">Start writing your first blog post.</p>
                <a href="/admin/blog/create" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
                    Create Post
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($posts as $post): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= $view->e($post['featured_image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                                        <div>
                                            <p class="font-medium text-dark-brown"><?= $view->e($post['title']) ?></p>
                                            <p class="text-sm text-gray-500"><?= $view->e($post['slug']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $view->e($post['author']['name'] ?? 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $view->e($post['category']['name'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= number_format($post['views_count']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $statusColors = [
                                        'published' => 'bg-green-100 text-green-800',
                                        'draft' => 'bg-yellow-100 text-yellow-800',
                                        'archived' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusClass = $statusColors[$post['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                        <?= ucfirst($post['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($post['is_published']): ?>
                                            <a href="/blog/<?= $post['slug'] ?>" target="_blank"
                                               class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100"
                                               title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/admin/blog/<?= $post['id'] ?>/edit" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="/admin/blog/<?= $post['id'] ?>/toggle-publish" method="POST">
                                            <?= $view->csrf() ?>
                                            <button type="submit" 
                                                    class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100"
                                                    title="<?= $post['is_published'] ? 'Unpublish' : 'Publish' ?>">
                                                <?php if ($post['is_published']): ?>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                <?php else: ?>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        <form action="/admin/blog/<?= $post['id'] ?>" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this post?');">
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
                            <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" 
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
