<?php
/**
 * Address List Page
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $addresses
 */
$view->extends('account');
?>

<?php $view->section('title'); ?>
<?= $view->e($title ?? 'My Addresses') ?>
<?php $view->endSection(); ?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-heading font-bold text-dark-brown">My Addresses</h1>
        <a href="/account/addresses/create" class="inline-flex items-center gap-2 px-4 py-2 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Address
        </a>
    </div>

    <?php if (empty($addresses)): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-brown mb-2">No addresses yet</h3>
            <p class="text-gray-500 mb-6">Add your first address to speed up checkout.</p>
            <a href="/account/addresses/create" class="inline-flex items-center gap-2 px-6 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors">
                Add New Address
            </a>
        </div>
    <?php else: ?>
        <!-- Address Grid -->
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($addresses as $address): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 relative <?= $address['is_default'] ? 'ring-2 ring-accent-orange' : '' ?>">
                    <?php if ($address['is_default']): ?>
                        <span class="absolute top-4 right-4 px-2 py-1 bg-accent-orange text-white text-xs font-medium rounded-full">
                            Default
                        </span>
                    <?php endif; ?>
                    
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 bg-light-gray rounded-lg flex items-center justify-center flex-shrink-0">
                            <?php if (strtolower($address['label']) === 'home'): ?>
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            <?php elseif (strtolower($address['label']) === 'office' || strtolower($address['label']) === 'work'): ?>
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            <?php else: ?>
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-semibold text-dark-brown"><?= $view->e($address['label']) ?></h3>
                            <p class="text-sm text-gray-500"><?= $view->e($address['address_type'] === 'both' ? 'Shipping & Billing' : ucfirst($address['address_type'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-gray-600 mb-4">
                        <p class="font-medium text-dark-brown"><?= $view->e($address['full_name']) ?></p>
                        <p><?= $view->e($address['address_line_1']) ?></p>
                        <?php if (!empty($address['address_line_2'])): ?>
                            <p><?= $view->e($address['address_line_2']) ?></p>
                        <?php endif; ?>
                        <p><?= $view->e($address['city']) ?>, <?= $view->e($address['district']) ?></p>
                        <?php if (!empty($address['postal_code'])): ?>
                            <p>Postal Code: <?= $view->e($address['postal_code']) ?></p>
                        <?php endif; ?>
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <?= $view->e($address['phone']) ?>
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <a href="/account/addresses/<?= $address['id'] ?>/edit" class="text-accent-orange hover:text-accent-orange-dark text-sm font-medium">
                            Edit
                        </a>
                        <?php if (!$address['is_default']): ?>
                            <form action="/account/addresses/<?= $address['id'] ?>/default" method="POST" class="inline">
                                <?= $view->csrf() ?>
                                <button type="submit" class="text-gray-500 hover:text-dark-brown text-sm font-medium">
                                    Set as Default
                                </button>
                            </form>
                        <?php endif; ?>
                        <form action="/account/addresses/<?= $address['id'] ?>" method="POST" class="inline ml-auto" 
                              onsubmit="return confirm('Are you sure you want to delete this address?')">
                            <?= $view->csrf() ?>
                            <?= $view->method('DELETE') ?>
                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $view->endSection(); ?>
