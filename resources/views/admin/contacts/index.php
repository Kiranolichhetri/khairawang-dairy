<?php
/**
 * Admin Contact Inquiries Page
 * KHAIRAWANG DAIRY
 */
$view->extends('admin');
$inquiries = $inquiries ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
?>

<?php $view->section('content'); ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Contact Inquiries</h1>
            <p class="text-gray-600">Manage customer inquiries and messages</p>
        </div>
    </div>
    
    <?php if ($view->flash('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            <?= $view->e($view->flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-3xl font-bold text-gray-800"><?= $stats['total'] ?? 0 ?></div>
            <div class="text-gray-600 text-sm">Total Inquiries</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow-sm p-6 border-l-4 border-yellow-400">
            <div class="text-3xl font-bold text-yellow-600"><?= $stats['new'] ?? 0 ?></div>
            <div class="text-gray-600 text-sm">New</div>
        </div>
        <div class="bg-blue-50 rounded-lg shadow-sm p-6 border-l-4 border-blue-400">
            <div class="text-3xl font-bold text-blue-600"><?= $stats['in_progress'] ?? 0 ?></div>
            <div class="text-gray-600 text-sm">In Progress</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm p-6 border-l-4 border-green-400">
            <div class="text-3xl font-bold text-green-600"><?= $stats['resolved'] ?? 0 ?></div>
            <div class="text-gray-600 text-sm">Resolved</div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form action="/admin/contacts" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-grow min-w-[200px]">
                <input 
                    type="text" 
                    name="q" 
                    value="<?= $view->e($filters['search'] ?? '') ?>"
                    placeholder="Search by name, email, or subject..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                >
            </div>
            
            <div class="min-w-[150px]">
                <select 
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                >
                    <option value="">All Status</option>
                    <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
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
    
    <!-- Inquiries List -->
    <div class="bg-white rounded-lg shadow-sm">
        <?php if (empty($inquiries)): ?>
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <span class="text-3xl">📬</span>
                </div>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">No Inquiries</h2>
                <p class="text-gray-600">Customer inquiries will appear here.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($inquiries as $inquiry): ?>
                    <a href="/admin/contacts/<?= $inquiry->getKey() ?>" class="block p-6 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-grow">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-semibold text-gray-800"><?= $view->e($inquiry->name ?? '') ?></span>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                        echo match($inquiry->status ?? 'new') {
                                            'new' => 'bg-yellow-100 text-yellow-700',
                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                            'resolved' => 'bg-green-100 text-green-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    ?>">
                                        <?= $inquiry->getStatusLabel() ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-lg text-gray-800 mb-1"><?= $view->e($inquiry->subject ?? '') ?></h3>
                                <p class="text-gray-600 text-sm line-clamp-2"><?= $view->truncate($inquiry->message ?? '', 150) ?></p>
                                
                                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                    <span><?= $view->e($inquiry->email ?? '') ?></span>
                                    <?php if (!empty($inquiry->phone)): ?>
                                        <span>•</span>
                                        <span><?= $view->e($inquiry->phone) ?></span>
                                    <?php endif; ?>
                                    <span>•</span>
                                    <span><?= $view->date($inquiry->created_at ?? '', 'M j, Y g:i A') ?></span>
                                </div>
                            </div>
                            
                            <div class="text-gray-400 ml-4">
                                →
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
