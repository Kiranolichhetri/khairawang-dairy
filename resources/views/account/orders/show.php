<?php
/**
 * Order Details Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $order
 * @var array $items
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Order Details') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <a href="/account/orders" class="text-gray-500 hover:text-dark-brown transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-heading font-bold text-dark-brown">Order #<?= $view->e($order['order_number']) ?></h1>
                <p class="text-sm text-gray-500">Placed on <?= $view->date($order['created_at'], 'F d, Y \a\t h:i A') ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-sm font-medium rounded-full" 
                  style="background-color: <?= $view->e($order['status_color']) ?>20; color: <?= $view->e($order['status_color']) ?>">
                <?= $view->e($order['status_label']) ?>
            </span>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-dark-brown">Order Items</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($items as $item): ?>
                        <div class="flex gap-4 p-4">
                            <div class="w-20 h-20 bg-light-gray rounded-lg overflow-hidden flex-shrink-0">
                                <img src="<?= $view->e($item['image']) ?>" 
                                     alt="<?= $view->e($item['product_name']) ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <?php if (!empty($item['slug'])): ?>
                                    <a href="/products/<?= $view->e($item['slug']) ?>" class="font-medium text-dark-brown hover:text-accent-orange">
                                        <?= $view->e($item['product_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <p class="font-medium text-dark-brown"><?= $view->e($item['product_name']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <p class="text-sm text-gray-500"><?= $view->e($item['variant_name']) ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-dark-brown"><?= $view->currency($item['total']) ?></p>
                                <p class="text-sm text-gray-500"><?= $view->currency($item['price']) ?> each</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-dark-brown">Shipping Address</h2>
                </div>
                <div class="p-4">
                    <p class="font-medium text-dark-brown"><?= $view->e($order['shipping']['name']) ?></p>
                    <p class="text-gray-600"><?= $view->e($order['shipping']['address']) ?></p>
                    <?php if (!empty($order['shipping']['city'])): ?>
                        <p class="text-gray-600"><?= $view->e($order['shipping']['city']) ?></p>
                    <?php endif; ?>
                    <p class="text-gray-600 mt-2">
                        <span class="font-medium">Phone:</span> <?= $view->e($order['shipping']['phone']) ?>
                    </p>
                    <p class="text-gray-600">
                        <span class="font-medium">Email:</span> <?= $view->e($order['shipping']['email']) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="space-y-6">
            <!-- Summary -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-dark-brown">Order Summary</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span><?= $view->currency($order['subtotal']) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span><?= $order['shipping_cost'] > 0 ? $view->currency($order['shipping_cost']) : 'Free' ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span>-<?= $view->currency($order['discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <hr class="border-gray-100">
                    <div class="flex justify-between font-semibold text-dark-brown text-lg">
                        <span>Total</span>
                        <span class="text-accent-orange"><?= $view->currency($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-dark-brown">Payment Information</h2>
                </div>
                <div class="p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Method</span>
                        <span class="font-medium text-dark-brown"><?= $view->e(ucfirst($order['payment_method'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status</span>
                        <span class="font-medium" style="color: <?= $order['payment_status'] === 'paid' ? '#22c55e' : '#f59e0b' ?>">
                            <?= $view->e($order['payment_status_label']) ?>
                        </span>
                    </div>
                    <?php if (!empty($order['transaction_id'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID</span>
                            <span class="font-mono text-sm text-dark-brown"><?= $view->e($order['transaction_id']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                <a href="/account/orders/<?= $view->e($order['order_number']) ?>/track" 
                   class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Track Order
                </a>
                
                <form action="/account/orders/<?= $view->e($order['order_number']) ?>/reorder" method="POST">
                    <?= $view->csrf() ?>
                    <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-3 border border-accent-orange text-accent-orange rounded-lg hover:bg-orange-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reorder
                    </button>
                </form>
                
                <a href="/invoice/<?= $view->e($order['order_number']) ?>/download" 
                   class="flex items-center justify-center gap-2 w-full px-4 py-3 border border-gray-300 text-gray-600 rounded-lg hover:bg-light-gray transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Invoice
                </a>
                
                <?php if ($order['can_cancel']): ?>
                    <form action="/account/orders/<?= $view->e($order['order_number']) ?>/cancel" method="POST" 
                          onsubmit="return confirm('Are you sure you want to cancel this order?')">
                        <?= $view->csrf() ?>
                        <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-3 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel Order
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Notes -->
    <?php if (!empty($order['notes'])): ?>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-dark-brown">Order Notes</h2>
            </div>
            <div class="p-4">
                <p class="text-gray-600"><?= $view->e($order['notes']) ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $view->endSection(); ?>
