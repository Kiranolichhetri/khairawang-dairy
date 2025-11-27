<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $view->e($pageDescription ?? 'KHAIRAWANG DAIRY - Premium fresh dairy products delivered from our farm to your table.') ?>">
    <meta name="keywords" content="dairy, milk, cheese, yogurt, fresh, organic, farm, Nepal, Khairawang">
    <meta name="author" content="KHAIRAWANG DAIRY">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $view->e($title ?? 'KHAIRAWANG DAIRY') ?> - Fresh From Farm To Table">
    <meta property="og:description" content="<?= $view->e($pageDescription ?? 'Premium fresh dairy products delivered from our farm to your table.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    
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
                        'warm-cream': '#FFF8F0',
                    },
                    fontFamily: {
                        'heading': ['Poppins', 'sans-serif'],
                        'body': ['DM Sans', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                        'soft-lg': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05)',
                    },
                }
            }
        }
    </script>
    
    <title><?= $view->e($title ?? 'Home') ?> - KHAIRAWANG DAIRY</title>
    
    <style>
        [x-cloak] { display: none !important; }
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
                
                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="/" class="text-gray-600 hover:text-accent-orange transition-colors">Home</a>
                    <a href="/products" class="text-gray-600 hover:text-accent-orange transition-colors">Products</a>
                    <a href="/about" class="text-gray-600 hover:text-accent-orange transition-colors">About</a>
                    <a href="/blog" class="text-gray-600 hover:text-accent-orange transition-colors">Blog</a>
                    <a href="/contact" class="text-gray-600 hover:text-accent-orange transition-colors">Contact</a>
                </nav>
                
                <!-- Right side -->
                <div class="flex items-center gap-4">
                    <!-- Cart -->
                    <a href="/cart" class="p-2 text-gray-600 hover:text-accent-orange transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                    
                    <!-- User Menu -->
                    <?php if ($view->auth()): ?>
                        <?php $user = $view->user(); ?>
                        <a href="/account" class="flex items-center gap-2 p-2 text-gray-600 hover:text-accent-orange transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="hidden md:inline text-sm font-medium"><?= $view->e($user['name'] ?? 'Account') ?></span>
                        </a>
                    <?php else: ?>
                        <a href="/login" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-accent-orange transition-colors">
                            Login
                        </a>
                        <a href="/register" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-accent-orange text-white rounded-lg hover:bg-orange-600 transition-colors">
                            Sign Up
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile menu button -->
                    <button type="button" class="md:hidden p-2 text-gray-600 hover:text-accent-orange" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <nav id="mobile-menu" class="md:hidden hidden pb-4 space-y-2">
                <a href="/" class="block py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">Home</a>
                <a href="/products" class="block py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">Products</a>
                <a href="/about" class="block py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">About</a>
                <a href="/blog" class="block py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">Blog</a>
                <a href="/contact" class="block py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">Contact</a>
                <?php if (!$view->auth()): ?>
                    <div class="pt-2 border-t border-gray-100 flex gap-2">
                        <a href="/login" class="flex-1 text-center py-2 px-4 text-gray-600 hover:bg-light-gray rounded-lg">Login</a>
                        <a href="/register" class="flex-1 text-center py-2 px-4 bg-accent-orange text-white rounded-lg hover:bg-orange-600">Sign Up</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if ($view->flash('success')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <?= $view->e($view->flash('success')) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($view->flash('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?= $view->e($view->flash('error')) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1">
        <?= $view->yield('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark-brown text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand Column -->
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center gap-2 text-xl font-heading font-bold text-white mb-4">
                        <span class="text-2xl">🥛</span>
                        KHAIRAWANG DAIRY
                    </a>
                    <p class="text-gray-400 text-sm mb-4">
                        Delivering fresh, premium dairy products from our family farm to your table since 1999.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-accent-orange transition-colors" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-accent-orange transition-colors" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63z"></path></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-accent-orange transition-colors" aria-label="Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Home</a></li>
                        <li><a href="/products" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Products</a></li>
                        <li><a href="/about" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">About Us</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Products -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Products</h3>
                    <ul class="space-y-2">
                        <li><a href="/products?category=milk" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Fresh Milk</a></li>
                        <li><a href="/products?category=yogurt" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Yogurt &amp; Curd</a></li>
                        <li><a href="/products?category=cheese" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Cheese</a></li>
                        <li><a href="/products?category=butter" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Butter &amp; Ghee</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-accent-orange flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-400 text-sm">Khairawang, Rupandehi<br>Lumbini Province, Nepal</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-accent-orange flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-400 text-sm">+977 9812345678</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-accent-orange flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-400 text-sm">info@khairawangdairy.com</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-gray-400 text-sm">&copy; <?= date('Y') ?> KHAIRAWANG DAIRY. All rights reserved.</p>
                    <nav class="flex gap-6">
                        <a href="/privacy" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Privacy Policy</a>
                        <a href="/terms" class="text-gray-400 hover:text-accent-orange transition-colors text-sm">Terms of Service</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
