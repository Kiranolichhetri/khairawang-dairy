<?php
/**
 * Change Password Page
 * 
 * @var \Core\View $view
 * @var string $title
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Change Password') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="/account/profile" class="text-gray-500 hover:text-dark-brown transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-heading font-bold text-dark-brown">Change Password</h1>
    </div>

    <!-- Password Form -->
    <form action="/account/password" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-6 max-w-lg">
        <?= $view->csrf() ?>
        
        <p class="text-gray-600">
            Ensure your account is using a strong password to stay secure.
        </p>
        
        <!-- Current Password -->
        <div>
            <label for="current_password" class="block text-sm font-medium text-dark-brown mb-1">
                Current Password
            </label>
            <input type="password" 
                   id="current_password" 
                   name="current_password" 
                   required
                   autocomplete="current-password"
                   class="w-full px-4 py-3 border <?= $view->hasError('current_password') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            <?php if ($view->hasError('current_password')): ?>
                <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('current_password')) ?></p>
            <?php endif; ?>
        </div>
        
        <!-- New Password -->
        <div>
            <label for="new_password" class="block text-sm font-medium text-dark-brown mb-1">
                New Password
            </label>
            <input type="password" 
                   id="new_password" 
                   name="new_password" 
                   required
                   autocomplete="new-password"
                   minlength="8"
                   class="w-full px-4 py-3 border <?= $view->hasError('new_password') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            <?php if ($view->hasError('new_password')): ?>
                <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('new_password')) ?></p>
            <?php endif; ?>
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
        </div>
        
        <!-- Confirm Password -->
        <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-dark-brown mb-1">
                Confirm New Password
            </label>
            <input type="password" 
                   id="new_password_confirmation" 
                   name="new_password_confirmation" 
                   required
                   autocomplete="new-password"
                   class="w-full px-4 py-3 border <?= $view->hasError('new_password_confirmation') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            <?php if ($view->hasError('new_password_confirmation')): ?>
                <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('new_password_confirmation')) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
            <a href="/account/profile" class="px-6 py-2 text-gray-600 hover:text-dark-brown transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Update Password
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
