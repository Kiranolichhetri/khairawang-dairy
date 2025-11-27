<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KHAIRAWANG DAIRY - Your Account">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥛</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN (for development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'accent-orange': '#FD7C44',
                        'accent-orange-dark': '#e56a35',
                        'dark-brown': '#201916',
                        'cream': '#F7EFDF',
                        'light-gray': '#F5F5F5',
                    },
                    fontFamily: {
                        'heading': ['Poppins', 'sans-serif'],
                        'body': ['DM Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft-lg': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05)',
                    },
                }
            }
        }
    </script>
    
    <title><?= $view->yield('title', 'My Account') ?> - KHAIRAWANG DAIRY</title>
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active {
            background-color: #FD7C44;
            color: white;
        }
        .sidebar-link.active svg {
            color: white;
        }
    </style>
</head>
<body class="font-body text-dark-brown bg-cream antialiased min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 text-xl font-heading font-bold text-dark-brown">
                    <span class="text-2xl">🥛</span>
                    <span class="hidden sm:inline">KHAIRAWANG DAIRY</span>
                </a>
                
                <!-- Right side -->
                <div class="flex items-center gap-4">
                    <!-- Cart -->
                    <a href="/cart" class="p-2 text-gray-600 hover:text-accent-orange transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-2 text-gray-600 hover:text-accent-orange transition-colors">
                            <?php $user = $view->user(); ?>
                            <?php if ($user): ?>
                                <img src="<?= $view->e($user['avatar'] ?? '') ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                <span class="hidden md:inline text-sm font-medium"><?= $view->e($user['name'] ?? '') ?></span>
                            <?php else: ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            <?php endif; ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2">
                            <a href="/account" class="block px-4 py-2 text-sm text-gray-700 hover:bg-light-gray">Dashboard</a>
                            <a href="/account/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-light-gray">Profile</a>
                            <a href="/account/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-light-gray">Orders</a>
                            <hr class="my-2 border-gray-100">
                            <form action="/logout" method="POST" class="block">
                                <?= $view->csrf() ?>
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-light-gray">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <!-- Sidebar -->
                <aside class="lg:col-span-3 mb-8 lg:mb-0">
                    <div class="bg-white rounded-xl shadow-sm p-4 sticky top-24">
                        <?php $user = $view->user(); ?>
                        <?php if ($user): ?>
                            <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-100">
                                <img src="<?= $view->e($user['avatar'] ?? '') ?>" alt="Avatar" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <h3 class="font-semibold text-dark-brown"><?= $view->e($user['name'] ?? '') ?></h3>
                                    <p class="text-sm text-gray-500"><?= $view->e($user['email'] ?? '') ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <nav class="space-y-1">
                            <?php
                            $currentPath = $_SERVER['REQUEST_URI'] ?? '/account';
                            $menuItems = [
                                ['href' => '/account', 'label' => 'Dashboard', 'icon' => 'home'],
                                ['href' => '/account/profile', 'label' => 'Profile', 'icon' => 'user'],
                                ['href' => '/account/addresses', 'label' => 'Addresses', 'icon' => 'map-pin'],
                                ['href' => '/account/wishlist', 'label' => 'Wishlist', 'icon' => 'heart'],
                                ['href' => '/account/orders', 'label' => 'Orders', 'icon' => 'shopping-bag'],
                            ];
                            ?>
                            
                            <?php foreach ($menuItems as $item): ?>
                                <?php $isActive = $currentPath === $item['href'] || str_starts_with($currentPath, $item['href'] . '/'); ?>
                                <a href="<?= $item['href'] ?>" 
                                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-light-gray transition-colors <?= $isActive ? 'active' : '' ?>">
                                    <?php if ($item['icon'] === 'home'): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    <?php elseif ($item['icon'] === 'user'): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    <?php elseif ($item['icon'] === 'map-pin'): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    <?php elseif ($item['icon'] === 'heart'): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    <?php elseif ($item['icon'] === 'shopping-bag'): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    <?php endif; ?>
                                    <span><?= $item['label'] ?></span>
                                </a>
                            <?php endforeach; ?>
                            
                            <hr class="my-4 border-gray-100">
                            
                            <form action="/logout" method="POST">
                                <?= $view->csrf() ?>
                                <button type="submit" class="sidebar-link w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </nav>
                    </div>
                </aside>
                
                <!-- Main Content Area -->
                <div class="lg:col-span-9">
                    <!-- Flash Messages -->
                    <?php if ($view->flash('success')): ?>
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                            <?= $view->e($view->flash('success')) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($view->flash('error')): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <?= $view->e($view->flash('error')) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?= $view->yield('content') ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?= date('Y') ?> KHAIRAWANG DAIRY. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
