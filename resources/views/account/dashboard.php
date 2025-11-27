<?php
/**
 * Account Dashboard Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $profile
 * @var array $stats
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Dashboard') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-accent-orange to-orange-400 rounded-xl p-6 text-white">
        <h1 class="text-2xl font-heading font-bold mb-2">
            Welcome back, <?= $view->e($profile['name'] ?? 'Customer') ?>! 👋
        </h1>
        <p class="opacity-90">
            Manage your account, view orders, and more from your personal dashboard.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $view->e($stats['total_orders'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Orders</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $view->currency($stats['total_spent'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Total Spent</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $view->e($stats['wishlist_count'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Wishlist</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-dark-brown"><?= $view->e($stats['review_count'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Reviews</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-heading font-semibold text-dark-brown mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="/account/orders" class="flex flex-col items-center p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-8 h-8 text-accent-orange mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="text-sm font-medium text-dark-brown">View Orders</span>
            </a>
            
            <a href="/account/wishlist" class="flex flex-col items-center p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-8 h-8 text-accent-orange mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="text-sm font-medium text-dark-brown">Wishlist</span>
            </a>
            
            <a href="/account/addresses" class="flex flex-col items-center p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-8 h-8 text-accent-orange mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm font-medium text-dark-brown">Addresses</span>
            </a>
            
            <a href="/account/profile/edit" class="flex flex-col items-center p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-8 h-8 text-accent-orange mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm font-medium text-dark-brown">Settings</span>
            </a>
        </div>
    </div>

    <!-- Account Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-heading font-semibold text-dark-brown">Account Information</h2>
            <a href="/account/profile/edit" class="text-accent-orange hover:text-accent-orange-dark text-sm font-medium">Edit</a>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 mb-1">Full Name</p>
                <p class="font-medium text-dark-brown"><?= $view->e($profile['name'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Email Address</p>
                <p class="font-medium text-dark-brown"><?= $view->e($profile['email'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Phone Number</p>
                <p class="font-medium text-dark-brown"><?= $view->e($profile['phone'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Member Since</p>
                <p class="font-medium text-dark-brown">
                    <?php if (!empty($profile['created_at'])): ?>
                        <?= $view->date($profile['created_at'], 'M d, Y') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
