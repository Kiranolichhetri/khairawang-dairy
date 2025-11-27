<?php
/**
 * Admin Order Details
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $order
 * @var array $statuses
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="/admin/orders" class="text-sm text-gray-500 hover:text-accent-orange">← Back to Orders</a>
            <h2 class="text-lg font-semibold text-dark-brown mt-2">Order #<?= $view->e($order['order_number']) ?></h2>
            <p class="text-sm text-gray-500">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/orders/<?= $order['id'] ?>/invoice" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Invoice
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown">Order Items</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="flex items-center gap-4">
                                <?php 
                                $images = $item['images'] ?? [];
                                if (is_string($images)) {
                                    $images = json_decode($images, true) ?? [];
                                }
                                $image = !empty($images) ? $images[0] : '/assets/images/placeholder.png';
                                ?>
                                <img src="<?= $view->e($image) ?>" alt="" class="w-16 h-16 rounded-lg object-cover bg-gray-100">
                                <div class="flex-1">
                                    <p class="font-medium text-dark-brown"><?= $view->e($item['product_name']) ?></p>
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <p class="text-sm text-gray-500"><?= $view->e($item['variant_name']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-dark-brown">Rs. <?= number_format($item['total'], 2) ?></p>
                                    <p class="text-xs text-gray-500">Rs. <?= number_format($item['price'], 2) ?> each</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="border-t border-gray-100 mt-6 pt-6 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-dark-brown">Rs. <?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Shipping</span>
                            <span class="text-dark-brown">Rs. <?= number_format($order['shipping_cost'], 2) ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-green-600">-Rs. <?= number_format($order['discount'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-lg font-semibold border-t border-gray-100 pt-2">
                            <span class="text-dark-brown">Total</span>
                            <span class="text-accent-orange">Rs. <?= number_format($order['total'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Info -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown">Shipping Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Customer</p>
                            <p class="font-medium text-dark-brown"><?= $view->e($order['shipping_name']) ?></p>
                            <p class="text-gray-600"><?= $view->e($order['shipping_email']) ?></p>
                            <p class="text-gray-600"><?= $view->e($order['shipping_phone']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Shipping Address</p>
                            <p class="text-gray-600"><?= nl2br($view->e($order['shipping_address'])) ?></p>
                            <?php if (!empty($order['shipping_city'])): ?>
                                <p class="text-gray-600"><?= $view->e($order['shipping_city']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            <?php if (!empty($order['notes'])): ?>
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-dark-brown">Order Notes</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 whitespace-pre-line"><?= $view->e($order['notes']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown">Order Status</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full"
                              style="background-color: <?= $view->e($order['status_color']) ?>20; color: <?= $view->e($order['status_color']) ?>;">
                            <?= $view->e($order['status_label']) ?>
                        </span>
                    </div>
                    
                    <form action="/admin/orders/<?= $order['id'] ?>/status" method="POST" class="space-y-4">
                        <?= $view->csrf() ?>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                            <select id="status" name="status" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status->value ?>" <?= $order['status'] === $status->value ? 'selected' : '' ?>>
                                        <?= $status->label() ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Payment Card -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown">Payment</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method</span>
                        <span class="font-medium text-dark-brown uppercase"><?= $view->e($order['payment_method']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                              style="background-color: <?= $view->e($order['payment_status_color']) ?>20; color: <?= $view->e($order['payment_status_color']) ?>;">
                            <?= $view->e($order['payment_status_label']) ?>
                        </span>
                    </div>
                    <?php if (!empty($order['transaction_id'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction ID</span>
                            <span class="font-mono text-sm text-dark-brown"><?= $view->e($order['transaction_id']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Customer Card -->
            <?php if (!empty($order['customer'])): ?>
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-dark-brown">Customer</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-dark-brown"><?= $view->e($order['customer']['name'] ?? '') ?></p>
                                <p class="text-sm text-gray-500"><?= $view->e($order['customer']['email'] ?? '') ?></p>
                            </div>
                        </div>
                        <a href="/admin/users/<?= $order['customer']['id'] ?? '' ?>" class="block mt-4 text-sm text-accent-orange hover:text-accent-orange-dark">
                            View Customer Profile →
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
