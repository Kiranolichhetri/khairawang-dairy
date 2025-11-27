<?php
/**
 * Home Page
 * KHAIRAWANG DAIRY - Premium Dairy Products from Nepal
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<!-- Hero Section -->
<section class="relative bg-dark-brown overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1628088062854-d1870b4553da?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" 
             alt="Fresh dairy products" 
             class="w-full h-full object-cover opacity-40">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-4 py-2 bg-accent-orange/20 backdrop-blur-sm rounded-full text-sm font-medium text-white mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                100% Fresh &amp; Natural
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-heading font-bold text-white mb-6">
                Fresh From Our Farm<br>
                <span class="text-accent-orange">To Your Table</span>
            </h1>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl">
                Experience the pure taste of premium dairy products, crafted with care and delivered fresh from our family farm in the heart of Nepal.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="/products" class="inline-flex items-center px-8 py-4 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Shop Now
                </a>
                <a href="/about" class="inline-flex items-center px-8 py-4 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white hover:text-dark-brown transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Product Categories -->
<section class="py-16 md:py-24 bg-light-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                Categories
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                Explore Our Products
            </h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                Discover our wide range of fresh dairy products, made with love and delivered to your doorstep.
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <!-- Milk -->
            <a href="/products?category=milk" class="group text-center">
                <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-soft group-hover:shadow-soft-lg group-hover:bg-accent-orange transition-all duration-300">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🥛</span>
                </div>
                <h3 class="font-semibold text-dark-brown group-hover:text-accent-orange transition-colors">Fresh Milk</h3>
                <p class="text-sm text-gray-500">Pure &amp; pasteurized</p>
            </a>
            
            <!-- Yogurt -->
            <a href="/products?category=yogurt" class="group text-center">
                <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-soft group-hover:shadow-soft-lg group-hover:bg-accent-orange transition-all duration-300">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🥣</span>
                </div>
                <h3 class="font-semibold text-dark-brown group-hover:text-accent-orange transition-colors">Yogurt</h3>
                <p class="text-sm text-gray-500">Creamy &amp; delicious</p>
            </a>
            
            <!-- Cheese -->
            <a href="/products?category=cheese" class="group text-center">
                <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-soft group-hover:shadow-soft-lg group-hover:bg-accent-orange transition-all duration-300">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🧀</span>
                </div>
                <h3 class="font-semibold text-dark-brown group-hover:text-accent-orange transition-colors">Cheese</h3>
                <p class="text-sm text-gray-500">Artisan varieties</p>
            </a>
            
            <!-- Butter -->
            <a href="/products?category=butter" class="group text-center">
                <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-soft group-hover:shadow-soft-lg group-hover:bg-accent-orange transition-all duration-300">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🧈</span>
                </div>
                <h3 class="font-semibold text-dark-brown group-hover:text-accent-orange transition-colors">Butter</h3>
                <p class="text-sm text-gray-500">Rich &amp; creamy</p>
            </a>
            
            <!-- Ghee -->
            <a href="/products?category=ghee" class="group text-center">
                <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-soft group-hover:shadow-soft-lg group-hover:bg-accent-orange transition-all duration-300">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🫙</span>
                </div>
                <h3 class="font-semibold text-dark-brown group-hover:text-accent-orange transition-colors">Ghee</h3>
                <p class="text-sm text-gray-500">Pure clarified butter</p>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                Our Products
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                Featured Products
            </h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                Discover our selection of premium dairy products, made with the finest ingredients.
            </p>
        </div>
        
        <?php if (!empty($featuredProducts)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($featuredProducts as $product): ?>
                    <article class="bg-white rounded-xl shadow-soft overflow-hidden group hover:shadow-soft-lg transition-shadow">
                        <div class="relative aspect-square overflow-hidden">
                            <img src="<?= $view->e($product['image']) ?>" 
                                 alt="<?= $view->e($product['name']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php if ($product['is_on_sale']): ?>
                                <span class="absolute top-4 left-4 px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">
                                    -<?= $view->e($product['discount_percentage']) ?>%
                                </span>
                            <?php endif; ?>
                            <?php if (!$product['in_stock']): ?>
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                    <span class="px-4 py-2 bg-white text-dark-brown font-semibold rounded-lg">Out of Stock</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <?php if ($product['category']): ?>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1"><?= $view->e($product['category']) ?></p>
                            <?php endif; ?>
                            <h3 class="font-semibold text-dark-brown mb-2">
                                <a href="/products/<?= $view->e($product['slug']) ?>" class="hover:text-accent-orange transition-colors">
                                    <?= $view->e($product['name']) ?>
                                </a>
                            </h3>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-accent-orange font-bold text-lg">
                                    <?= $view->currency($product['current_price']) ?>
                                </span>
                                <?php if ($product['sale_price']): ?>
                                    <span class="text-sm text-gray-400 line-through">
                                        <?= $view->currency($product['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($product['in_stock']): ?>
                                <a href="/products/<?= $view->e($product['slug']) ?>" 
                                   class="block w-full text-center py-3 bg-accent-orange text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                                    View Product
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-6 bg-light-gray rounded-full flex items-center justify-center">
                    <span class="text-4xl">🥛</span>
                </div>
                <h3 class="text-xl font-semibold text-dark-brown mb-2">Products Coming Soon</h3>
                <p class="text-gray-600">We're preparing our freshest products for you. Check back soon!</p>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-12">
            <a href="/products" class="inline-flex items-center px-8 py-4 border-2 border-dark-brown text-dark-brown font-semibold rounded-xl hover:bg-dark-brown hover:text-white transition-colors">
                View All Products
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                Why Choose Us
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                The KHAIRAWANG Difference
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">100% Fresh</h3>
                <p class="text-gray-600 text-sm">No preservatives or additives. Pure, natural dairy goodness.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Daily Delivery</h3>
                <p class="text-gray-600 text-sm">Fresh products delivered to your doorstep every morning.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Farm to Table</h3>
                <p class="text-gray-600 text-sm">Direct from our family farm with complete traceability.</p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-accent-orange/10 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-dark-brown mb-2">Quality Certified</h3>
                <p class="text-gray-600 text-sm">Meeting the highest quality and safety standards.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Preview -->
<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1594489573732-5cd4bcc68a71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Our dairy farm" 
                     class="w-full rounded-2xl shadow-soft-lg">
                <div class="absolute -bottom-6 -right-6 bg-accent-orange text-white p-6 rounded-2xl shadow-lg hidden md:block">
                    <span class="block text-4xl font-bold">25+</span>
                    <span class="text-sm opacity-90">Years of Excellence</span>
                </div>
            </div>
            
            <div>
                <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                    About Us
                </span>
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown mb-6">
                    Our Story of Pure<br>
                    <span class="text-accent-orange">Dairy Excellence</span>
                </h2>
                <p class="text-gray-600 mb-4">
                    For over two decades, KHAIRAWANG DAIRY has been committed to delivering the freshest and finest dairy products to families across Nepal.
                </p>
                <p class="text-gray-600 mb-6">
                    Every product we create is a testament to our dedication to quality, sustainability, and the well-being of our community. From our happy cows to your happy family.
                </p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-dark-brown font-medium">100% Natural</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-dark-brown font-medium">Farm Fresh</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-dark-brown font-medium">No Preservatives</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-dark-brown font-medium">Sustainable</span>
                    </div>
                </div>
                
                <a href="/about" class="inline-flex items-center px-6 py-3 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                    Learn More About Us
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 bg-accent-orange/10 text-accent-orange text-sm font-medium rounded-full mb-4">
                Testimonials
            </span>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown">
                What Our Customers Say
            </h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-light-gray rounded-2xl p-8">
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 mb-6">
                    "The freshest milk I've ever tasted! My family has been ordering from KHAIRAWANG DAIRY for years now. The quality is consistently excellent."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-accent-orange/10 rounded-full flex items-center justify-center">
                        <span class="text-xl">👤</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark-brown">Sita Sharma</h4>
                        <p class="text-sm text-gray-500">Kathmandu</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-light-gray rounded-2xl p-8">
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 mb-6">
                    "Their ghee reminds me of my grandmother's homemade ghee. Pure, aromatic, and incredibly delicious. Highly recommend to everyone!"
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-accent-orange/10 rounded-full flex items-center justify-center">
                        <span class="text-xl">👤</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark-brown">Ram Prasad</h4>
                        <p class="text-sm text-gray-500">Pokhara</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-light-gray rounded-2xl p-8">
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 mb-6">
                    "I've tried many dairy brands, but KHAIRAWANG DAIRY stands out. Their yogurt is creamy, their cheese is delicious, and delivery is always on time."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-accent-orange/10 rounded-full flex items-center justify-center">
                        <span class="text-xl">👤</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark-brown">Anita Rai</h4>
                        <p class="text-sm text-gray-500">Bhaktapur</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-16 md:py-24 bg-dark-brown">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <div class="w-16 h-16 mx-auto mb-6 bg-accent-orange/20 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-accent-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-3xl md:text-4xl font-heading font-bold text-white mb-4">
                Subscribe to Our Newsletter
            </h2>
            <p class="text-gray-400 text-lg mb-8 max-w-xl mx-auto">
                Stay updated with our latest products, exclusive offers, and dairy recipes delivered to your inbox.
            </p>
            
            <form action="/newsletter/subscribe" method="POST" class="max-w-md mx-auto">
                <?= $view->csrf() ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="email" 
                           name="email" 
                           placeholder="Enter your email" 
                           required
                           class="flex-1 px-5 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-accent-orange">
                    <button type="submit" 
                            class="px-8 py-4 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors whitespace-nowrap">
                        Subscribe
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-16 md:py-20 bg-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">25+</div>
                <div class="text-gray-600">Years Experience</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">50K+</div>
                <div class="text-gray-600">Happy Customers</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">100%</div>
                <div class="text-gray-600">Natural Products</div>
            </div>
            <div>
                <div class="text-4xl md:text-5xl font-heading font-bold text-accent-orange mb-2">20+</div>
                <div class="text-gray-600">Product Varieties</div>
            </div>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
