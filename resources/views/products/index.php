<?php 
/**
 * Product Listing Page
 * 
 * Displays all products with filters, search and pagination.
 * Uses Alpine.js for interactivity.
 */
$view->extends('app');
$view->section('title');
echo 'Products';
$view->endSection();
?>

<?php $view->section('content'); ?>
<div class="min-h-screen bg-cream py-8" x-data="productListing">
    <div class="container-dairy">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2">Our Products</h1>
            <p class="text-gray-600">Fresh dairy products from our family farm</p>
        </div>

        <!-- Filters & Search -->
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <input type="search" 
                           x-model="filters.search"
                           @input.debounce.300ms="loadProducts"
                           placeholder="Search products..."
                           class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Category Filter -->
            <select x-model="filters.category" @change="loadProducts" 
                    class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.slug">
                    <option :value="cat.slug" x-text="cat.name"></option>
                </template>
            </select>

            <!-- Sort -->
            <select x-model="filters.sort" @change="loadProducts"
                    class="px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="name">Name A-Z</option>
            </select>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Products Grid -->
        <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="product in products" :key="product.id">
                <div class="bg-white rounded-xl shadow-soft overflow-hidden group hover:shadow-soft-lg transition-shadow">
                    <!-- Product Image -->
                    <a :href="'/products/' + product.slug" class="block aspect-square overflow-hidden">
                        <img :src="product.image" :alt="product.name" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </a>
                    
                    <!-- Product Info -->
                    <div class="p-4">
                        <a :href="'/products/' + product.slug" class="block">
                            <h3 class="font-semibold text-dark-brown mb-1 truncate" x-text="product.name"></h3>
                        </a>
                        
                        <!-- Price -->
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-lg font-bold text-accent-orange">
                                Rs. <span x-text="formatPrice(product.current_price)"></span>
                            </span>
                            <span x-show="product.is_on_sale" class="text-sm text-gray-400 line-through">
                                Rs. <span x-text="formatPrice(product.price)"></span>
                            </span>
                            <span x-show="product.is_on_sale" class="text-xs text-white bg-error-red px-2 py-0.5 rounded-full">
                                -<span x-text="product.discount_percentage"></span>%
                            </span>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="flex items-center justify-between">
                            <span :class="product.in_stock ? 'text-success-green' : 'text-error-red'" 
                                  class="text-sm" x-text="product.stock_status"></span>
                            
                            <!-- Add to Cart -->
                            <button @click="addToCart(product)" 
                                    :disabled="!product.in_stock"
                                    :class="product.in_stock ? 'bg-accent-orange hover:bg-opacity-90' : 'bg-gray-300 cursor-not-allowed'"
                                    class="p-2 rounded-lg text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && products.length === 0" class="text-center py-12">
            <div class="w-20 h-20 mx-auto mb-4 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-brown mb-2">No products found</h3>
            <p class="text-gray-500">Try adjusting your search or filters</p>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && meta.last_page > 1" class="flex justify-center gap-2 mt-8">
            <button @click="changePage(meta.current_page - 1)" 
                    :disabled="meta.current_page === 1"
                    :class="meta.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-accent-orange hover:text-white'"
                    class="px-4 py-2 border border-gray-200 rounded-lg transition-colors">
                Previous
            </button>
            
            <template x-for="page in paginationPages" :key="page">
                <button @click="changePage(page)"
                        :class="page === meta.current_page ? 'bg-accent-orange text-white' : 'hover:bg-light-gray'"
                        class="w-10 h-10 rounded-lg border border-gray-200 transition-colors"
                        x-text="page"></button>
            </template>
            
            <button @click="changePage(meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    :class="meta.current_page === meta.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-accent-orange hover:text-white'"
                    class="px-4 py-2 border border-gray-200 rounded-lg transition-colors">
                Next
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productListing', () => ({
        products: [],
        categories: [],
        loading: true,
        filters: {
            search: '',
            category: '',
            sort: 'newest'
        },
        meta: {
            total: 0,
            per_page: 12,
            current_page: 1,
            last_page: 1
        },
        
        init() {
            this.loadProducts();
            this.loadCategories();
        },
        
        async loadProducts() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.meta.current_page,
                    per_page: this.meta.per_page,
                    sort: this.filters.sort
                });
                
                if (this.filters.search) params.append('q', this.filters.search);
                if (this.filters.category) params.append('category', this.filters.category);
                
                const response = await fetch('/api/v1/products?' + params);
                const data = await response.json();
                
                if (data.success) {
                    this.products = data.data.products;
                    this.meta = data.meta;
                }
            } catch (error) {
                console.error('Failed to load products:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadCategories() {
            try {
                const response = await fetch('/api/v1/categories');
                const data = await response.json();
                if (data.success) {
                    this.categories = data.data;
                }
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },
        
        changePage(page) {
            if (page < 1 || page > this.meta.last_page) return;
            this.meta.current_page = page;
            this.loadProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        get paginationPages() {
            const pages = [];
            const total = this.meta.last_page;
            const current = this.meta.current_page;
            
            for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
                pages.push(i);
            }
            return pages;
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
        },
        
        async addToCart(product) {
            try {
                const response = await fetch('/api/v1/cart/items', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: product.id, quantity: 1 })
                });
                const data = await response.json();
                
                if (data.success) {
                    this.$store.toast.success('Added to cart!');
                    this.$store.cart.refresh();
                } else {
                    this.$store.toast.error(data.message || 'Failed to add to cart');
                }
            } catch (error) {
                this.$store.toast.error('Failed to add to cart');
            }
        }
    }));
});
</script>
<?php $view->endSection(); ?>
