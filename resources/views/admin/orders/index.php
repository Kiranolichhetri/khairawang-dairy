<?php
/**
 * Admin Orders List
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $orders
 * @var array $statuses
 * @var array $paymentStatuses
 * @var array $filters
 * @var array $pagination
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">All Orders</h2>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?? 0 ?> orders found</p>
        </div>
        <a href="/admin/orders/export?<?= http_build_query($filters) ?>" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="/admin/orders" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="<?= $view->e($filters['search'] ?? '') ?>" 
                       placeholder="Search by order #, name, email..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Status</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status->value ?>" <?= ($filters['status'] ?? '') === $status->value ? 'selected' : '' ?>>
                        <?= $status->label() ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Payment</option>
                <?php foreach ($paymentStatuses as $status): ?>
                    <option value="<?= $status->value ?>" <?= ($filters['payment_status'] ?? '') === $status->value ? 'selected' : '' ?>>
                        <?= $status->label() ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= $view->e($filters['date_from'] ?? '') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
            <input type="date" name="date_to" value="<?= $view->e($filters['date_to'] ?? '') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Filter
            </button>
            <?php if (!empty(array_filter($filters))): ?>
                <a href="/admin/orders" class="px-4 py-2 text-gray-500 hover:text-gray-700">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($orders)): ?>
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No orders found</h3>
                <p class="text-gray-500 mt-1">Orders will appear here when customers place them.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="/admin/orders/<?= $order['id'] ?>" class="font-medium text-accent-orange hover:text-accent-orange-dark">
                                        #<?= $view->e($order['order_number']) ?>
                                    </a>
                                    <p class="text-xs text-gray-500"><?= $order['item_count'] ?> items</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-dark-brown"><?= $view->e($order['customer_name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= $view->e($order['shipping_email']) ?></p>
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
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                          style="background-color: <?= $view->e($order['payment_status_color']) ?>20; color: <?= $view->e($order['payment_status_color']) ?>;">
                                        <?= $view->e($order['payment_status_label']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                    <br>
                                    <span class="text-xs"><?= date('g:i A', strtotime($order['created_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/admin/orders/<?= $order['id'] ?>" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="/admin/orders/<?= $order['id'] ?>/invoice" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="Print Invoice">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
                        to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                        of <?= $pagination['total'] ?> results
                    </p>
                    <div class="flex gap-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>" 
                               class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php $view->endSection(); ?>
