<?php
/**
 * Admin Newsletter Subscribers Page
 * KHAIRAWANG DAIRY
 */
$view->extends('admin');
$subscribers = $subscribers ?? [];
$activeCount = $activeCount ?? 0;
$totalCount = $totalCount ?? 0;
$filters = $filters ?? [];
$pagination = $pagination ?? [];
?>

<?php $view->section('content'); ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Newsletter Subscribers</h1>
            <p class="text-gray-600"><?= $activeCount ?> active of <?= $totalCount ?> total subscribers</p>
        </div>
        
        <div class="flex gap-4">
            <a 
                href="/admin/newsletter/export"
                class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition"
            >
                Export CSV
            </a>
            <a 
                href="/admin/newsletter/campaigns"
                class="bg-accent-orange text-white py-2 px-4 rounded-lg hover:bg-orange-600 transition"
            >
                Campaigns
            </a>
        </div>
    </div>
    
    <?php if ($view->flash('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            <?= $view->e($view->flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form action="/admin/newsletter" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-grow min-w-[200px]">
                <input 
                    type="text" 
                    name="q" 
                    value="<?= $view->e($filters['search'] ?? '') ?>"
                    placeholder="Search by email or name..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                >
            </div>
            
            <div class="min-w-[150px]">
                <select 
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                >
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="unsubscribed" <?= ($filters['status'] ?? '') === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                </select>
            </div>
            
            <button 
                type="submit"
                class="bg-gray-800 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition"
            >
                Filter
            </button>
        </form>
    </div>
    
    <!-- Subscribers Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($subscribers)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No subscribers found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900"><?= $view->e($subscriber['email'] ?? '') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?= $view->e($subscriber['name'] ?? '-') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($subscriber['is_active'] ?? false): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Unsubscribed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $view->date($subscriber['subscribed_at'] ?? '', 'M j, Y') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="/admin/newsletter/subscribers/<?= $subscriber['id'] ?>" method="POST" class="inline">
                                    <?= $view->csrf() ?>
                                    <?= $view->method('DELETE') ?>
                                    <button 
                                        type="submit"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Delete this subscriber?')"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing page <?= $pagination['current_page'] ?? 1 ?> of <?= $pagination['last_page'] ?? 1 ?>
            </div>
            <div class="flex gap-2">
                <?php if (($pagination['current_page'] ?? 1) > 1): ?>
                    <a 
                        href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&status=<?= $view->e($filters['status'] ?? '') ?>&q=<?= $view->e($filters['search'] ?? '') ?>"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if (($pagination['current_page'] ?? 1) < ($pagination['last_page'] ?? 1)): ?>
                    <a 
                        href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&status=<?= $view->e($filters['status'] ?? '') ?>&q=<?= $view->e($filters['search'] ?? '') ?>"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php $view->endSection(); ?>
