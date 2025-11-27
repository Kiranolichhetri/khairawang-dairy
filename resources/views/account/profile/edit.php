<?php
/**
 * Edit Profile Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $profile
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Edit Profile') ?>
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
        <h1 class="text-2xl font-heading font-bold text-dark-brown">Edit Profile</h1>
    </div>

    <!-- Avatar Upload -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-dark-brown mb-4">Profile Picture</h2>
        <div class="flex items-center gap-6">
            <img src="<?= $view->e($profile['avatar'] ?? '') ?>" 
                 alt="Profile Avatar" 
                 class="w-24 h-24 rounded-full object-cover border-4 border-light-gray">
            <div class="space-y-3">
                <form action="/account/avatar" method="POST" enctype="multipart/form-data" class="inline-block">
                    <?= $view->csrf() ?>
                    <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-light-gray text-dark-brown rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Upload New Photo</span>
                        <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
                
                <?php if (!empty($profile['avatar']) && strpos($profile['avatar'], 'gravatar') === false): ?>
                    <form action="/account/avatar" method="POST" class="inline-block">
                        <?= $view->csrf() ?>
                        <?= $view->method('DELETE') ?>
                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">Remove Photo</button>
                    </form>
                <?php endif; ?>
                
                <p class="text-xs text-gray-500">JPG, PNG, GIF or WebP. Max 2MB.</p>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <form action="/account/profile" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        <?= $view->csrf() ?>
        
        <h2 class="text-lg font-semibold text-dark-brown">Personal Information</h2>
        
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-brown mb-1">Full Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= $view->e($view->old('name', $profile['name'] ?? '')) ?>"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('name') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('name')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-dark-brown mb-1">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= $view->e($view->old('email', $profile['email'] ?? '')) ?>"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('email') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('email')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('email')) ?></p>
                <?php endif; ?>
                <p class="mt-1 text-xs text-gray-500">Changing your email will require verification.</p>
            </div>
            
            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-dark-brown mb-1">Phone Number</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       value="<?= $view->e($view->old('phone', $profile['phone'] ?? '')) ?>"
                       placeholder="98XXXXXXXX"
                       class="w-full px-4 py-3 border <?= $view->hasError('phone') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('phone')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('phone')) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
            <a href="/account/profile" class="px-6 py-2 text-gray-600 hover:text-dark-brown transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Save Changes
            </button>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ showDelete: false }">
        <h2 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h2>
        <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
            <div>
                <p class="font-medium text-dark-brown">Delete Account</p>
                <p class="text-sm text-gray-500">Permanently delete your account and all data.</p>
            </div>
            <button @click="showDelete = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete Account
            </button>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.away="showDelete = false">
                <h3 class="text-lg font-semibold text-dark-brown mb-2">Delete Account</h3>
                <p class="text-gray-600 mb-4">This action cannot be undone. All your data will be permanently deleted.</p>
                
                <form action="/account/delete" method="POST">
                    <?= $view->csrf() ?>
                    <?= $view->method('DELETE') ?>
                    
                    <div class="mb-4">
                        <label for="delete_password" class="block text-sm font-medium text-dark-brown mb-1">Enter your password to confirm</label>
                        <input type="password" 
                               id="delete_password" 
                               name="password" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex items-center justify-end gap-4">
                        <button type="button" @click="showDelete = false" class="px-4 py-2 text-gray-600 hover:text-dark-brown transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
