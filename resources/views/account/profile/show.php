<?php
/**
 * Profile View Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $profile
 * @var array $stats
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'My Profile') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-heading font-bold text-dark-brown">My Profile</h1>
        <a href="/account/profile/edit" class="btn bg-accent-orange text-white px-4 py-2 rounded-lg hover:bg-accent-orange-dark transition-colors">
            Edit Profile
        </a>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Avatar Section -->
        <div class="bg-gradient-to-r from-accent-orange to-orange-400 p-6">
            <div class="flex items-center gap-4">
                <img src="<?= $view->e($profile['avatar'] ?? '') ?>" 
                     alt="Profile Avatar" 
                     class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                <div class="text-white">
                    <h2 class="text-xl font-semibold"><?= $view->e($profile['name'] ?? '') ?></h2>
                    <p class="opacity-90"><?= $view->e($profile['email'] ?? '') ?></p>
                    <?php if (!empty($profile['email_verified'])): ?>
                        <span class="inline-flex items-center gap-1 mt-1 text-xs bg-white/20 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Profile Details -->
        <div class="p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Personal Information</h3>
            <div class="grid md:grid-cols-2 gap-6">
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
                            <?= $view->date($profile['created_at'], 'F d, Y') ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-accent-orange"><?= $view->e($stats['total_orders'] ?? 0) ?></p>
            <p class="text-sm text-gray-500">Total Orders</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-accent-orange"><?= $view->currency($stats['total_spent'] ?? 0) ?></p>
            <p class="text-sm text-gray-500">Total Spent</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-accent-orange"><?= $view->e($stats['wishlist_count'] ?? 0) ?></p>
            <p class="text-sm text-gray-500">Wishlist Items</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm text-center">
            <p class="text-2xl font-bold text-accent-orange"><?= $view->e($stats['address_count'] ?? 0) ?></p>
            <p class="text-sm text-gray-500">Saved Addresses</p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-dark-brown mb-4">Account Settings</h3>
        <div class="space-y-2">
            <a href="/account/profile/edit" class="flex items-center justify-between p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="font-medium text-dark-brown">Edit Profile</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            
            <a href="/account/password" class="flex items-center justify-between p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="font-medium text-dark-brown">Change Password</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            
            <a href="/account/addresses" class="flex items-center justify-between p-4 bg-light-gray rounded-lg hover:bg-gray-200 transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium text-dark-brown">Manage Addresses</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
