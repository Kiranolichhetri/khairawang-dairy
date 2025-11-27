<?php
/**
 * Admin Users List
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $users
 * @var array $roles
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
            <h2 class="text-lg font-semibold text-dark-brown">Users & Customers</h2>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?? 0 ?> users found</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form action="/admin/users" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="<?= $view->e($filters['search'] ?? '') ?>" 
                       placeholder="Search by name, email, phone..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            </div>
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role->value ?>" <?= ($filters['role'] ?? '') === $role->value ? 'selected' : '' ?>>
                        <?= $role->label() ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                <option value="">All Status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="banned" <?= ($filters['status'] ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Filter
            </button>
            <?php if (!empty(array_filter($filters))): ?>
                <a href="/admin/users" class="px-4 py-2 text-gray-500 hover:text-gray-700">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($users)): ?>
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No users found</h3>
                <p class="text-gray-500 mt-1">Try adjusting your search filters.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?= $view->e($user['avatar']) ?>" alt="" class="w-10 h-10 rounded-full object-cover bg-gray-200">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="/admin/users/<?= $user['id'] ?>" class="font-medium text-dark-brown hover:text-accent-orange">
                                                <?= $view->e($user['name']) ?>
                                            </a>
                                            <p class="text-sm text-gray-500"><?= $view->e($user['email']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'manager' => 'bg-blue-100 text-blue-800',
                                        'staff' => 'bg-green-100 text-green-800',
                                        'customer' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $roleClass = $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $roleClass ?>">
                                        <?= $view->e($user['role_label']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= $view->e($user['phone']) ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-yellow-100 text-yellow-800',
                                        'banned' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusClass = $statusColors[$user['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/admin/users/<?= $user['id'] ?>" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="View">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="/admin/users/<?= $user['id'] ?>/edit" 
                                           class="p-2 text-gray-400 hover:text-accent-orange rounded-lg hover:bg-gray-100"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
