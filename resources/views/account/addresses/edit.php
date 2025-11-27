<?php
/**
 * Edit Address Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $address
 * @var array $districts
 * @var array $labels
 * @var array $types
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'Edit Address') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="/account/addresses" class="text-gray-500 hover:text-dark-brown transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-heading font-bold text-dark-brown">Edit Address</h1>
    </div>

    <!-- Address Form -->
    <form action="/account/addresses/<?= $address['id'] ?>" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
        <?= $view->csrf() ?>
        <?= $view->method('PUT') ?>
        
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Label -->
            <div>
                <label for="label" class="block text-sm font-medium text-dark-brown mb-1">Address Label</label>
                <select id="label" 
                        name="label" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <?php foreach ($labels as $label): ?>
                        <option value="<?= $view->e($label) ?>" <?= ($view->old('label', $address['label'] ?? '') === $label) ? 'selected' : '' ?>>
                            <?= $view->e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Address Type -->
            <div>
                <label for="address_type" class="block text-sm font-medium text-dark-brown mb-1">Address Type</label>
                <select id="address_type" 
                        name="address_type" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <?php foreach ($types as $value => $typeLabel): ?>
                        <option value="<?= $view->e($value) ?>" <?= ($view->old('address_type', $address['address_type'] ?? '') === $value) ? 'selected' : '' ?>>
                            <?= $view->e($typeLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-sm font-medium text-dark-brown mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       value="<?= $view->e($view->old('full_name', $address['full_name'] ?? '')) ?>"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('full_name') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('full_name')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('full_name')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-dark-brown mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       value="<?= $view->e($view->old('phone', $address['phone'] ?? '')) ?>"
                       placeholder="98XXXXXXXX"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('phone') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('phone')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('phone')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Address Line 1 -->
            <div class="md:col-span-2">
                <label for="address_line_1" class="block text-sm font-medium text-dark-brown mb-1">Street Address <span class="text-red-500">*</span></label>
                <input type="text" 
                       id="address_line_1" 
                       name="address_line_1" 
                       value="<?= $view->e($view->old('address_line_1', $address['address_line_1'] ?? '')) ?>"
                       placeholder="House number, Street name, Area"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('address_line_1') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('address_line_1')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('address_line_1')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Address Line 2 -->
            <div class="md:col-span-2">
                <label for="address_line_2" class="block text-sm font-medium text-dark-brown mb-1">Address Line 2 (Optional)</label>
                <input type="text" 
                       id="address_line_2" 
                       name="address_line_2" 
                       value="<?= $view->e($view->old('address_line_2', $address['address_line_2'] ?? '')) ?>"
                       placeholder="Apartment, Suite, Building, etc."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            </div>
            
            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-medium text-dark-brown mb-1">City/Town <span class="text-red-500">*</span></label>
                <input type="text" 
                       id="city" 
                       name="city" 
                       value="<?= $view->e($view->old('city', $address['city'] ?? '')) ?>"
                       required
                       class="w-full px-4 py-3 border <?= $view->hasError('city') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <?php if ($view->hasError('city')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('city')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- District -->
            <div>
                <label for="district" class="block text-sm font-medium text-dark-brown mb-1">District <span class="text-red-500">*</span></label>
                <select id="district" 
                        name="district" 
                        required
                        class="w-full px-4 py-3 border <?= $view->hasError('district') ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <option value="">Select District</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?= $view->e($district) ?>" <?= ($view->old('district', $address['district'] ?? '') === $district) ? 'selected' : '' ?>>
                            <?= $view->e($district) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($view->hasError('district')): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $view->e($view->error('district')) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Postal Code -->
            <div>
                <label for="postal_code" class="block text-sm font-medium text-dark-brown mb-1">Postal Code (Optional)</label>
                <input type="text" 
                       id="postal_code" 
                       name="postal_code" 
                       value="<?= $view->e($view->old('postal_code', $address['postal_code'] ?? '')) ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            </div>
            
            <!-- Set as Default -->
            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_default" 
                       name="is_default" 
                       value="1"
                       <?= ($view->old('is_default', $address['is_default'] ?? false)) ? 'checked' : '' ?>
                       class="w-4 h-4 text-accent-orange border-gray-300 rounded focus:ring-accent-orange">
                <label for="is_default" class="ml-2 text-sm text-gray-600">Set as default address</label>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
            <a href="/account/addresses" class="px-6 py-2 text-gray-600 hover:text-dark-brown transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Update Address
            </button>
        </div>
    </form>
</div>
<?php $view->endSection(); ?>
