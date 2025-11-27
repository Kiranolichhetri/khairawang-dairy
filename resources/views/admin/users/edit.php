<?php
/**
 * Admin Edit User
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $user
 * @var array $roles
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/admin/users" class="text-sm text-gray-500 hover:text-accent-orange">
            ← Back to Users
        </a>
    </div>
    
    <form action="/admin/users/<?= $view->e($user['id']) ?>" method="POST" class="space-y-6">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">User Information</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= $view->e($view->old('name', $user['name'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('name') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('name')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('name')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= $view->e($view->old('email', $user['email'] ?? '')) ?>"
                           class="w-full px-4 py-2 border <?= $view->hasError('email') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                    <?php if ($view->hasError('email')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('email')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= $view->e($view->old('phone', $user['phone'] ?? '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="role_id" name="role_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= ($view->old('role_id', $user['role_id'] ?? '')) == $role['id'] ? 'selected' : '' ?>>
                                    <?= $view->e(ucfirst($role['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                            <option value="active" <?= ($view->old('status', $user['status'] ?? '')) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($view->old('status', $user['status'] ?? '')) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="banned" <?= ($view->old('status', $user['status'] ?? '')) === 'banned' ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-dark-brown mb-4">Change Password</h3>
            <p class="text-sm text-gray-500 mb-4">Leave blank to keep current password</p>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Enter new password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-4">
            <a href="/admin/users" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Update User
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
