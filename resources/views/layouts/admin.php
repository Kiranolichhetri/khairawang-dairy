<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥛</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN -->
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
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <title><?= $view->yield('title', 'Admin') ?> - KHAIRAWANG DAIRY Admin</title>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-body text-dark-brown bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?= $view->include('admin.layouts.sidebar') ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <!-- Page Title -->
                    <h1 class="text-xl font-semibold text-dark-brown"><?= $view->e($title ?? 'Dashboard') ?></h1>
                    
                    <!-- User Menu -->
                    <div class="flex items-center gap-4">
                        <a href="/" target="_blank" class="text-sm text-gray-500 hover:text-accent-orange">
                            View Site →
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                                <img src="<?= $view->e($view->user()['avatar'] ?? '') ?>" alt="Avatar" class="w-8 h-8 rounded-full bg-gray-200">
                                <span class="hidden sm:block text-sm font-medium"><?= $view->e($view->user()['name'] ?? 'Admin') ?></span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1">
                                <a href="/account/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="/admin/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                <hr class="my-1">
                                <form action="/logout" method="POST" class="block">
                                    <?= $view->csrf() ?>
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Flash Messages -->
            <?php if ($view->flash('success')): ?>
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><?= $view->e($view->flash('success')) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if ($view->flash('error')): ?>
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><?= $view->e($view->flash('error')) ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <?= $view->yield('content') ?>
            </main>
            
            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-sm text-gray-500 text-center">
                    &copy; <?= date('Y') ?> KHAIRAWANG DAIRY. All rights reserved.
                </p>
            </footer>
        </div>
    </div>
</body>
</html>
