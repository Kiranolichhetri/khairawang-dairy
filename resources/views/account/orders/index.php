<?php
/**
 * Order History Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $orders
 * @var array $pagination
 * @var array $filter
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Order History') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <h1 class="text-2xl font-heading font-bold text-dark-brown">Order History</h1>
        
        <!-- Filter -->
        <form method="GET" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <option value="">All Orders</option>
                <option value="pending" <?= ($filter['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= ($filter['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="shipped" <?= ($filter['status'] ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= ($filter['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= ($filter['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </form>
    </div>

    <?php if (empty($orders)): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-brown mb-2">No orders yet</h3>
            <p class="text-gray-500 mb-6">Your order history will appear here once you place an order.</p>
            <a href="/products" class="inline-flex items-center gap-2 px-6 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <!-- Orders List -->
        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Order Header -->
                    <div class="flex items-center justify-between flex-wrap gap-4 p-4 bg-light-gray border-b border-gray-100">
                        <div class="flex items-center gap-6 flex-wrap">
                            <div>
                                <p class="text-xs text-gray-500">Order Number</p>
                                <p class="font-semibold text-dark-brown"><?= $view->e($order['order_number']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Date</p>
                                <p class="font-medium text-dark-brown"><?= $view->date($order['created_at'], 'M d, Y') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total</p>
                                <p class="font-semibold text-accent-orange"><?= $view->currency($order['total']) ?></p>
                            </div>
                        </div>
                        <div>
                            <span class="px-3 py-1 text-sm font-medium rounded-full" 
                                  style="background-color: <?= $view->e($order['status_color']) ?>20; color: <?= $view->e($order['status_color']) ?>">
                                <?= $view->e($order['status_label']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Order Actions -->
                    <div class="flex items-center justify-between p-4">
                        <p class="text-sm text-gray-500">
                            <?= $order['item_count'] ?> item(s) • <?= $view->e($order['payment_status_label']) ?>
                        </p>
                        <div class="flex items-center gap-3">
                            <a href="/account/orders/<?= $view->e($order['order_number']) ?>" 
                               class="text-accent-orange hover:text-accent-orange-dark text-sm font-medium">
                                View Details
                            </a>
                            <a href="/account/orders/<?= $view->e($order['order_number']) ?>/track" 
                               class="text-gray-500 hover:text-dark-brown text-sm font-medium">
                                Track Order
                            </a>
                            <?php if ($order['can_cancel']): ?>
                                <form action="/account/orders/<?= $view->e($order['order_number']) ?>/cancel" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    <?= $view->csrf() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                        Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (($pagination['last_page'] ?? 1) > 1): ?>
            <div class="flex items-center justify-center gap-2 pt-6">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?= $pagination['current_page'] - 1 ?><?= !empty($filter['status']) ? '&status=' . $filter['status'] : '' ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-light-gray transition-colors">
                        Previous
                    </a>
                <?php endif; ?>
                
                <span class="px-4 py-2 text-gray-600">
                    Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?>
                </span>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?= $pagination['current_page'] + 1 ?><?= !empty($filter['status']) ? '&status=' . $filter['status'] : '' ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-light-gray transition-colors">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php $view->endSection(); ?>
