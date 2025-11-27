<?php
/**
 * Admin Sales Report
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $data
 * @var array $filters
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-dark-brown">Sales Report</h2>
            <p class="text-sm text-gray-500">Overview of your sales performance</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/reports/export/sales?<?= http_build_query($filters) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="/admin/reports/sales" method="GET" class="flex flex-wrap gap-4">
            <select name="period" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="day" <?= ($filters['period'] ?? '') === 'day' ? 'selected' : '' ?>>Daily</option>
                <option value="week" <?= ($filters['period'] ?? '') === 'week' ? 'selected' : '' ?>>Weekly</option>
                <option value="month" <?= ($filters['period'] ?? '') === 'month' ? 'selected' : '' ?>>Monthly</option>
            </select>
            <input type="date" name="date_from" value="<?= $view->e($filters['date_from'] ?? '') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
            <input type="date" name="date_to" value="<?= $view->e($filters['date_to'] ?? '') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Apply
            </button>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
            <p class="text-3xl font-bold text-dark-brown mt-2">Rs. <?= number_format($data['total_revenue'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Total Orders</p>
            <p class="text-3xl font-bold text-dark-brown mt-2"><?= number_format($data['total_orders'] ?? 0) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-sm font-medium text-gray-500">Average Order Value</p>
            <p class="text-3xl font-bold text-dark-brown mt-2">Rs. <?= number_format($data['average_order_value'] ?? 0, 2) ?></p>
        </div>
    </div>
    
    <!-- Sales Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-dark-brown mb-4">Sales Trend</h3>
        <canvas id="salesChart" height="100"></canvas>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales by Payment Method -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-dark-brown">Sales by Payment Method</h3>
            </div>
            <div class="p-6">
                <?php if (empty($data['sales_by_payment'])): ?>
                    <p class="text-gray-500 text-center">No data available</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($data['sales_by_payment'] as $payment): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-accent-orange"></span>
                                    <span class="font-medium text-dark-brown uppercase"><?= $view->e($payment['payment_method']) ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-dark-brown">Rs. <?= number_format($payment['revenue'], 2) ?></p>
                                    <p class="text-xs text-gray-500"><?= $payment['orders'] ?> orders</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Orders by Status -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-dark-brown">Orders by Status</h3>
            </div>
            <div class="p-6">
                <?php if (empty($data['orders_by_status'])): ?>
                    <p class="text-gray-500 text-center">No data available</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php 
                        $statusColors = [
                            'pending' => 'bg-yellow-500',
                            'processing' => 'bg-blue-500',
                            'shipped' => 'bg-indigo-500',
                            'delivered' => 'bg-green-500',
                            'cancelled' => 'bg-red-500',
                        ];
                        foreach ($data['orders_by_status'] as $status): 
                            $colorClass = $statusColors[$status['status']] ?? 'bg-gray-500';
                        ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full <?= $colorClass ?>"></span>
                                    <span class="font-medium text-dark-brown"><?= ucfirst($status['status']) ?></span>
                                </div>
                                <span class="font-semibold text-dark-brown"><?= $status['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sales by Period Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-dark-brown">Sales by Period</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach (($data['sales_by_period'] ?? []) as $period): ?>
                        <tr>
                            <td class="px-6 py-4 font-medium text-dark-brown"><?= $view->e($period['period']) ?></td>
                            <td class="px-6 py-4 text-right">Rs. <?= number_format($period['revenue'], 2) ?></td>
                            <td class="px-6 py-4 text-right"><?= $period['orders'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?= json_encode($data['sales_by_period'] ?? []) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.period),
            datasets: [{
                label: 'Revenue (Rs.)',
                data: salesData.map(d => d.revenue),
                borderColor: '#FD7C44',
                backgroundColor: 'rgba(253, 124, 68, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php $view->endSection(); ?>
