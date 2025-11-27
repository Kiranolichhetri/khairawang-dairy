<?php
/**
 * Order Tracking Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $order
 * @var array $timeline
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Track Order') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="/account/orders/<?= $view->e($order['order_number']) ?>" class="text-gray-500 hover:text-dark-brown transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-heading font-bold text-dark-brown">Track Order</h1>
            <p class="text-sm text-gray-500">Order #<?= $view->e($order['order_number']) ?></p>
        </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-gray-500">Current Status</p>
                <h2 class="text-xl font-semibold" style="color: <?= $view->e($order['status_color']) ?>">
                    <?= $view->e($order['status_label']) ?>
                </h2>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Last Updated</p>
                <p class="font-medium text-dark-brown"><?= $view->date($order['updated_at'], 'M d, Y h:i A') ?></p>
            </div>
        </div>

        <!-- Timeline -->
        <div class="relative">
            <?php foreach ($timeline as $index => $step): ?>
                <div class="flex gap-4 <?= $index < count($timeline) - 1 ? 'pb-8' : '' ?>">
                    <!-- Status Indicator -->
                    <div class="relative flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center 
                                    <?= $step['completed'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' ?>
                                    <?= $step['current'] ? 'ring-4 ring-green-100' : '' ?>">
                            <?php if ($step['completed']): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <span class="text-sm font-medium"><?= $index + 1 ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($index < count($timeline) - 1): ?>
                            <div class="w-0.5 h-full <?= $step['completed'] && isset($timeline[$index + 1]) && $timeline[$index + 1]['completed'] ? 'bg-green-500' : 'bg-gray-200' ?> absolute top-10"></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Status Info -->
                    <div class="flex-1 pb-2">
                        <h3 class="font-semibold <?= $step['completed'] ? 'text-dark-brown' : 'text-gray-400' ?>">
                            <?= $view->e($step['label']) ?>
                        </h3>
                        <p class="text-sm text-gray-500">
                            <?php
                            $descriptions = [
                                'pending' => 'Your order has been placed and is awaiting confirmation.',
                                'processing' => 'We are preparing your order for shipment.',
                                'packed' => 'Your order has been packed and is ready for pickup.',
                                'shipped' => 'Your order is on its way to you.',
                                'out_for_delivery' => 'Your order is out for delivery today.',
                                'delivered' => 'Your order has been delivered.',
                                'cancelled' => 'Your order has been cancelled.',
                                'returned' => 'Your order has been returned.',
                            ];
                            echo $descriptions[$step['key']] ?? '';
                            ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Info -->
    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-dark-brown mb-4">Order Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Order Number</span>
                    <span class="font-medium text-dark-brown"><?= $view->e($order['order_number']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Order Date</span>
                    <span class="font-medium text-dark-brown"><?= $view->date($order['created_at'], 'M d, Y') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Payment Status</span>
                    <span class="font-medium" style="color: <?= $order['payment_status'] === 'paid' ? '#22c55e' : '#f59e0b' ?>">
                        <?= $view->e($order['payment_status_label']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-dark-brown mb-4">Need Help?</h3>
            <p class="text-gray-600 mb-4">If you have any questions about your order, please contact us.</p>
            <!-- Contact info - In production, these should come from site settings/config -->
            <?php 
            $supportPhone = '+977 9812345678';
            $supportEmail = 'support@khairawangdairy.com';
            ?>
            <div class="space-y-2">
                <a href="tel:<?= str_replace(' ', '', $supportPhone) ?>" class="flex items-center gap-2 text-accent-orange hover:text-accent-orange-dark">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <?= $view->e($supportPhone) ?>
                </a>
                <a href="mailto:<?= $view->e($supportEmail) ?>" class="flex items-center gap-2 text-accent-orange hover:text-accent-orange-dark">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <?= $view->e($supportEmail) ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-4">
        <a href="/account/orders/<?= $view->e($order['order_number']) ?>" 
           class="px-6 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
            View Order Details
        </a>
        <?php if ($order['can_cancel']): ?>
            <form action="/account/orders/<?= $view->e($order['order_number']) ?>/cancel" method="POST" 
                  onsubmit="return confirm('Are you sure you want to cancel this order?')">
                <?= $view->csrf() ?>
                <button type="submit" class="px-6 py-3 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    Cancel Order
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $view->endSection(); ?>
