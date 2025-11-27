<?php
/**
 * Admin Create Coupon Form
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $coupon_types
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
        <h2 class="text-lg font-semibold text-dark-brown">Create Coupon</h2>
    </div>
    
    <!-- Form -->
    <form action="/admin/coupons" method="POST" class="bg-white rounded-xl shadow-sm">
        <?= $view->csrf() ?>
        
        <div class="p-6 space-y-6">
            <!-- Coupon Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                <div class="flex gap-2">
                    <input type="text" id="code" name="code" value="<?= $view->e($view->old('code', '')) ?>"
                           placeholder="Leave empty to auto-generate"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent uppercase">
                    <button type="button" onclick="generateCode()" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Generate
                    </button>
                </div>
                <?php if ($error = $view->error('code')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $error ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" id="name" name="name" value="<?= $view->e($view->old('name', '')) ?>" required
                       placeholder="e.g., Summer Sale 20% Off"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($error = $view->error('name')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $error ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Optional description for internal use"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent"><?= $view->e($view->old('description', '')) ?></textarea>
            </div>
            
            <!-- Type & Value -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                    <select id="type" name="type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                        <?php foreach ($coupon_types as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $view->old('type') === $value ? 'selected' : '' ?>>
                                <?= $view->e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Value *</label>
                    <input type="number" id="value" name="value" value="<?= $view->e($view->old('value', '')) ?>" required
                           step="0.01" min="0"
                           placeholder="e.g., 10 for 10% or NPR 100"
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
                           value="<?= $view->e($view->old('min_order_amount', '0')) ?>"
                           step="0.01" min="0"
                           placeholder="0 for no minimum"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="maximum_discount" class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (NPR)</label>
                    <input type="number" id="maximum_discount" name="maximum_discount" 
                           value="<?= $view->e($view->old('maximum_discount', '')) ?>"
                           step="0.01" min="0"
                           placeholder="Leave empty for no cap"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Usage Limits -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-2">Total Usage Limit</label>
                    <input type="number" id="max_uses" name="max_uses" 
                           value="<?= $view->e($view->old('max_uses', '')) ?>"
                           min="1"
                           placeholder="Leave empty for unlimited"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="per_user_limit" class="block text-sm font-medium text-gray-700 mb-2">Per User Limit</label>
                    <input type="number" id="per_user_limit" name="per_user_limit" 
                           value="<?= $view->e($view->old('per_user_limit', '1')) ?>"
                           min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Valid Period -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" 
                           value="<?= $view->e($view->old('starts_at', '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" 
                           value="<?= $view->e($view->old('expires_at', '')) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                </div>
            </div>
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <option value="active" <?= $view->old('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $view->old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 rounded-b-xl">
            <a href="/admin/coupons" class="px-4 py-2 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                Create Coupon
            </button>
        </div>
    </form>
</div>

<script>
async function generateCode() {
    try {
        const response = await fetch('/admin/coupons/generate-code');
        const data = await response.json();
        if (data.success) {
            document.getElementById('code').value = data.code;
        }
    } catch (error) {
        console.error('Failed to generate code:', error);
    }
}
</script>
<?php $view->endSection(); ?>
