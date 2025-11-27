<?php
/**
 * Admin User Details
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $user
 * @var array $orders
 * @var array $stats
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="/admin/users" class="text-sm text-gray-500 hover:text-accent-orange">← Back to Users</a>
            <h2 class="text-lg font-semibold text-dark-brown mt-2"><?= $view->e($user['name']) ?></h2>
            <p class="text-sm text-gray-500">Customer since <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/users/<?= $user['id'] ?>/edit" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit User
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= $view->e($user['avatar']) ?>" alt="" class="w-24 h-24 mx-auto rounded-full object-cover bg-gray-200">
                    <?php else: ?>
                        <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="mt-4 text-lg font-semibold text-dark-brown"><?= $view->e($user['name']) ?></h3>
                    
                    <span class="inline-flex mt-2 px-3 py-1 text-sm font-medium rounded-full <?= $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= ucfirst($user['status']) ?>
                    </span>
                </div>
                
                <div class="mt-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-gray-600"><?= $view->e($user['email']) ?></span>
                    </div>
                    
                    <?php if (!empty($user['phone'])): ?>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-gray-600"><?= $view->e($user['phone']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-gray-600"><?= $view->e($user['role_label']) ?></span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-600">
                            Email <?= $user['email_verified'] ? 'Verified' : 'Not Verified' ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h4 class="font-semibold text-dark-brown mb-4">Customer Stats</h4>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Orders</span>
                        <span class="font-semibold text-dark-brown"><?= $stats['total_orders'] ?? 0 ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Spent</span>
                        <span class="font-semibold text-dark-brown">Rs. <?= number_format($stats['total_spent'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown">Order History</h3>
                </div>
                
                <?php if (empty($orders)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500">No orders yet</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <a href="/admin/orders/<?= $order['id'] ?>" class="font-medium text-accent-orange hover:text-accent-orange-dark">
                                                #<?= $view->e($order['order_number']) ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-dark-brown">
                                            Rs. <?= number_format($order['total'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                                  style="background-color: <?= $view->e($order['status_color']) ?>20; color: <?= $view->e($order['status_color']) ?>;">
                                                <?= $view->e($order['status_label']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $view->endSection(); ?>
