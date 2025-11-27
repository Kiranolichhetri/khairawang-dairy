<?php
/**
 * Admin Sidebar Navigation
 * 
 * @var \Core\View $view
 */

$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// Define menu items
$menuItems = [
    [
        'label' => 'Dashboard',
        'href' => '/admin',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
        'active' => $currentPath === '/admin',
    ],
    [
        'label' => 'Products',
        'href' => '/admin/products',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
        'active' => str_starts_with($currentPath, '/admin/products'),
    ],
    [
        'label' => 'Categories',
        'href' => '/admin/categories',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
        'active' => str_starts_with($currentPath, '/admin/categories'),
    ],
    [
        'label' => 'Orders',
        'href' => '/admin/orders',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />',
        'active' => str_starts_with($currentPath, '/admin/orders'),
    ],
    [
        'label' => 'Customers',
        'href' => '/admin/users',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
        'active' => str_starts_with($currentPath, '/admin/users'),
    ],
    [
        'label' => 'Reports',
        'href' => '/admin/reports/sales',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
        'active' => str_starts_with($currentPath, '/admin/reports'),
    ],
    [
        'label' => 'Settings',
        'href' => '/admin/settings',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        'active' => str_starts_with($currentPath, '/admin/settings'),
    ],
];
?>

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false" 
     class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
     x-cloak></div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-brown transform transition-transform duration-300 ease-in-out lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="h-full flex flex-col">
        <!-- Logo -->
        <div class="p-4 border-b border-white/10">
            <a href="/admin" class="flex items-center gap-2 text-white">
                <span class="text-3xl">🥛</span>
                <span class="font-heading font-bold text-lg">KHAIRAWANG</span>
            </a>
            <p class="text-xs text-gray-400 mt-1">Admin Dashboard</p>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-1">
                <?php foreach ($menuItems as $item): ?>
                    <li>
                        <a href="<?= $view->e($item['href']) ?>" 
                           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?= $item['active'] ? 'bg-accent-orange text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?= $item['icon'] ?>
                            </svg>
                            <span class="font-medium"><?= $view->e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <!-- Quick Stats -->
        <div class="p-4 border-t border-white/10">
            <div class="bg-white/5 rounded-lg p-3">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Today's Sales</p>
                <p class="text-xl font-bold text-white mt-1">Rs. --</p>
            </div>
        </div>
        
        <!-- Close button (mobile) -->
        <button @click="sidebarOpen = false" 
                class="lg:hidden absolute top-4 right-4 text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</aside>
