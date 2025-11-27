<?php
/**
 * Admin Coupons List
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $coupons
 * @var array $pagination
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">All Coupons</h2>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?? 0 ?> coupons found</p>
        </div>
        <a href="/admin/coupons/create" class="inline-flex items-center gap-2 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Coupon
        </a>
    </div>
    
    <!-- Coupons Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($coupons)): ?>
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No coupons found</h3>
                <p class="text-gray-500 mt-1">Create your first coupon to offer discounts.</p>
                <a href="/admin/coupons/create" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark transition-colors">
                    Create Coupon
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coupon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($coupons as $coupon): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-mono font-bold text-dark-brown"><?= $view->e($coupon['code']) ?></p>
                                        <p class="text-sm text-gray-500"><?= $view->e($coupon['name']) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-medium text-accent-orange"><?= $view->e($coupon['formatted_value']) ?></span>
                                    <p class="text-xs text-gray-500"><?= $view->e($coupon['type_label']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600">
                                        <?= $coupon['uses_count'] ?><?= $coupon['max_uses'] ? ' / ' . $coupon['max_uses'] : '' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($coupon['starts_at']): ?>
                                        <p class="text-gray-600">From: <?= date('M d, Y', strtotime($coupon['starts_at'])) ?></p>
                                    <?php endif; ?>
                                    <?php if ($coupon['expires_at']): ?>
                                        <p class="<?= $coupon['is_expired'] ? 'text-red-600' : 'text-gray-600' ?>">
                                            Until: <?= date('M d, Y', strtotime($coupon['expires_at'])) ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-gray-400">No expiry</p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($coupon['is_expired']): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                            Expired
                                        </span>
                                    <?php elseif ($coupon['is_active']): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/admin/coupons/<?= $coupon['id'] ?>/edit" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="/admin/coupons/<?= $coupon['id'] ?>/toggle-status" method="POST">
                                            <?= $view->csrf() ?>
                                            <button type="submit" 
                                                    class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-gray-100"
                                                    title="<?= $coupon['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="/admin/coupons/<?= $coupon['id'] ?>" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this coupon?');">
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
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
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
