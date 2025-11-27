<?php
/**
 * Admin Edit Coupon Form
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $coupon
 * @var array $coupon_types
 * @var array $stats
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="/admin/coupons" class="p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-lg font-semibold text-dark-brown">Edit Coupon: <?= $view->e($coupon['code']) ?></h2>
    </div>
    
    <!-- Usage Stats -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-4">Usage Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-dark-brown"><?= $stats['usage_count'] ?? 0 ?></p>
                <p class="text-sm text-gray-500">Times Used</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-dark-brown">रू <?= number_format($stats['total_discount'] ?? 0, 2) ?></p>
                <p class="text-sm text-gray-500">Total Discount Given</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-dark-brown"><?= $coupon['max_uses'] ? ($coupon['max_uses'] - $coupon['uses_count']) : '∞' ?></p>
                <p class="text-sm text-gray-500">Remaining Uses</p>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <form action="/admin/coupons/<?= $coupon['id'] ?>" method="POST" class="bg-white rounded-xl shadow-sm">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <div class="p-6 space-y-6">
            <!-- Coupon Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                <input type="text" id="code" name="code" value="<?= $view->e($view->old('code', $coupon['code'])) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent uppercase">
                <?php if ($error = $view->error('code')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $error ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" id="name" name="name" value="<?= $view->e($view->old('name', $coupon['name'])) ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($error = $view->error('name')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $error ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent"><?= $view->e($view->old('description', $coupon['description'])) ?></textarea>
            </div>
            
            <!-- Type & Value -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                    <select id="type" name="type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                        <?php foreach ($coupon_types as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $view->old('type', $coupon['type']) === $value ? 'selected' : '' ?>>
                                <?= $view->e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Value *</label>
                    <input type="number" id="value" name="value" value="<?= $view->e($view->old('value', $coupon['value'])) ?>" required
                           step="0.01" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <?php if ($error = $view->error('value')): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $error ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Minimum Order & Maximum Discount -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="min_order_amount" class="block text-sm font-medium text-gray-700 mb-2">Minimum Order (NPR)</label>
                    <input type="number" id="min_order_amount" name="min_order_amount" 
                           value="<?= $view->e($view->old('min_order_amount', $coupon['min_order_amount'])) ?>"
                           step="0.01" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="maximum_discount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (NPR)</label>
                    <input type="number" id="maximum_discount" name="maximum_discount" 
                           value="<?= $view->e($view->old('maximum_discount', $coupon['maximum_discount'])) ?>"
                           step="0.01" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Usage Limits -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-2">Total Usage Limit</label>
                    <input type="number" id="max_uses" name="max_uses" 
                           value="<?= $view->e($view->old('max_uses', $coupon['max_uses'])) ?>"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="per_user_limit" class="block text-sm font-medium text-gray-700 mb-2">Per User Limit</label>
                    <input type="number" id="per_user_limit" name="per_user_limit" 
                           value="<?= $view->e($view->old('per_user_limit', $coupon['per_user_limit'])) ?>"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Valid Period -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" 
                           value="<?= $view->e($view->old('starts_at', $coupon['starts_at'] ? date('Y-m-d\TH:i', strtotime($coupon['starts_at'])) : '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" 
                           value="<?= $view->e($view->old('expires_at', $coupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <option value="active" <?= $view->old('status', $coupon['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $view->old('status', $coupon['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-xl">
            <a href="/admin/coupons" class="px-4 py-2 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Update Coupon
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
