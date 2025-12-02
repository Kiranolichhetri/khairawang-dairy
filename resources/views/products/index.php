<?php 
/**
 * Product Listing Page
 * 
 * Displays all products with filters, search and pagination.
 * Products are loaded from MongoDB via PHP, with Alpine.js for interactivity.
 */
$view->extends('app');
$view->section('title');
echo 'Products';
$view->endSection();

// Get products and categories passed from controller
$serverProducts = $products ?? [];
$serverCategories = $categories ?? [];

// Safely encode data for Alpine.js - handle encoding failures gracefully
// Using JSON_HEX_* flags to escape special HTML characters in a JSON-safe way
$initialDataJson = json_encode(
    ['products' => $serverProducts, 'categories' => $serverCategories],
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
);
// Fallback to empty data if encoding fails
if ($initialDataJson === false) {
    $initialDataJson = '{"products":[],"categories":[]}';
}
?>

<?php $view->section('content'); ?>
<style>
/* Enhanced Product Card Styles */
.product-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.product-card .product-image-container {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-card .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.08);
}

.product-card .badge-container {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    right: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    z-index: 10;
}

.product-card .discount-badge {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.product-card .stock-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
}

.product-card .stock-badge.in-stock {
    background: rgba(34, 197, 94, 0.9);
    color: white;
}

.product-card .stock-badge.low-stock {
    background: rgba(245, 158, 11, 0.9);
    color: white;
}

.product-card .stock-badge.out-of-stock {
    background: rgba(239, 68, 68, 0.9);
    color: white;
}

.product-card .quick-add {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    padding: 2rem 1rem 1rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.product-card:hover .quick-add {
    transform: translateY(0);
}

.product-card .product-info {
    padding: 1rem;
}

.product-card .product-name {
    font-weight: 600;
    color: #4a3728;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.8rem;
}

.product-card .product-name:hover {
    color: #f97316;
}

.product-card .price-container {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}

.product-card .current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #f97316;
}

.product-card .original-price {
    font-size: 0.875rem;
    color: #9ca3af;
    text-decoration: line-through;
}

.product-card .add-to-cart-btn {
    width: 100%;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    font-weight: 600;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.product-card .add-to-cart-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    transform: scale(1.02);
}

.product-card .add-to-cart-btn:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    transform: none;
}

.product-card .add-to-cart-btn:active:not(:disabled) {
    transform: scale(0.98);
}

/* Filter Section Styles */
.filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.filter-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.filter-input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.filter-select {
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    background-color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
}

.filter-select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

/* Loading Skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.empty-state-icon {
    width: 6rem;
    height: 6rem;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Pagination */
.pagination-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    background: white;
}

.pagination-btn:hover:not(:disabled) {
    border-color: #f97316;
    color: #f97316;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    border-color: #f97316;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<div class="min-h-screen bg-gradient-to-b from-orange-50 to-white py-8 md:py-12" x-data="productListing(<?php echo $initialDataJson; ?>)">
    <div class="container-dairy">
        <!-- Page Header -->
        <div class="text-center mb-8 md:mb-12">
            <h1 class="text-3xl md:text-4xl font-heading font-bold text-dark-brown mb-3">Our Products</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">Discover our range of fresh, premium dairy products sourced directly from our family farm. Quality you can taste in every bite.</p>
        </div>

        <!-- Filters & Search -->
        <div class="filter-section">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="search" 
                               x-model="filters.search"
                               @input.debounce.300ms="loadProducts"
                               placeholder="Search products..."
                               class="filter-input">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Category Filter -->
                    <select x-model="filters.category" @change="loadProducts" class="filter-select">
                        <option value="">All Categories</option>
                        <template x-for="cat in categories" :key="cat.slug">
                            <option :value="cat.slug" x-text="cat.name"></option>
                        </template>
                    </select>

                    <!-- Sort -->
                    <select x-model="filters.sort" @change="loadProducts" class="filter-select">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name">Name A-Z</option>
                    </select>
                </div>
            </div>
            
            <!-- Results count -->
            <div x-show="!loading && products.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-semibold text-gray-700" x-text="products.length"></span> products
                    <span x-show="filters.search">for "<span class="font-semibold" x-text="filters.search"></span>"</span>
                </p>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="i in 8" :key="i">
                <div class="product-card">
                    <div class="product-image-container skeleton"></div>
                    <div class="product-info">
                        <div class="h-5 skeleton rounded mb-2"></div>
                        <div class="h-4 skeleton rounded w-2/3 mb-3"></div>
                        <div class="h-10 skeleton rounded"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Products Grid -->
        <div x-show="!loading" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="product in products" :key="product.id">
                <div class="product-card">
                    <!-- Product Image Container -->
                    <div class="product-image-container">
                        <a :href="'/products/' + product.slug">
                            <img :src="product.image" :alt="product.name" 
                                 class="product-image"
                                 loading="lazy"
                                 onerror="this.src='/assets/images/product-placeholder.png'">
                        </a>
                        
                        <!-- Badges -->
                        <div class="badge-container">
                            <span x-show="product.is_on_sale" class="discount-badge">
                                -<span x-text="product.discount_percentage"></span>%
                            </span>
                            <span x-show="!product.is_on_sale"></span>
                            <span :class="{
                                'stock-badge in-stock': product.in_stock && product.stock > 10,
                                'stock-badge low-stock': product.in_stock && product.stock <= 10,
                                'stock-badge out-of-stock': !product.in_stock
                            }" x-text="product.stock_status"></span>
                        </div>
                        
                        <!-- Quick Add Overlay (Desktop) -->
                        <div class="quick-add hidden md:block">
                            <button @click="addToCart(product)" 
                                    :disabled="!product.in_stock"
                                    class="w-full py-2.5 px-4 bg-white text-dark-brown font-semibold rounded-lg hover:bg-orange-500 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="product.in_stock">Quick Add</span>
                                <span x-show="!product.in_stock">Out of Stock</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="product-info">
                        <a :href="'/products/' + product.slug" class="block">
                            <h3 class="product-name" x-text="product.name"></h3>
                        </a>
                        
                        <!-- Price -->
                        <div class="price-container">
                            <span class="current-price">
                                Rs. <span x-text="formatPrice(product.current_price)"></span>
                            </span>
                            <span x-show="product.is_on_sale" class="original-price">
                                Rs. <span x-text="formatPrice(product.price)"></span>
                            </span>
                        </div>
                        
                        <!-- Add to Cart Button (Always visible on mobile) -->
                        <button @click="addToCart(product)" 
                                :disabled="!product.in_stock"
                                class="add-to-cart-btn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-show="product.in_stock">Add to Cart</span>
                            <span x-show="!product.in_stock">Out of Stock</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && products.length === 0" x-cloak class="empty-state">
            <div class="empty-state-icon">
                <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-dark-brown mb-2">No products found</h3>
            <p class="text-gray-500 mb-6">We couldn't find any products matching your criteria.</p>
            <button @click="filters.search = ''; filters.category = ''; loadProducts()" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Clear Filters
            </button>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && meta.last_page > 1" x-cloak class="flex flex-wrap justify-center gap-2 mt-10">
            <button @click="changePage(meta.current_page - 1)" 
                    :disabled="meta.current_page === 1"
                    class="pagination-btn flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>
            
            <template x-for="page in paginationPages" :key="page">
                <button @click="changePage(page)"
                        :class="page === meta.current_page ? 'pagination-btn active' : 'pagination-btn'"
                        class="w-10 h-10"
                        x-text="page"></button>
            </template>
            
            <button @click="changePage(meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    class="pagination-btn flex items-center gap-1">
                Next
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productListing', (initialData) => ({
        // Initialize with server-side data or empty arrays
        products: initialData?.products || [],
        categories: initialData?.categories || [],
        loading: false, // No loading needed if we have server data
        filters: {
            search: '',
            category: '',
            sort: 'newest'
        },
        meta: {
            total: initialData?.products?.length || 0,
            per_page: 12,
            current_page: 1,
            last_page: 1
        },
        
        init() {
            // Only load from API if we don't have server-side products
            if (!this.products || this.products.length === 0) {
                this.loading = true;
                this.loadProducts();
            }
            // Load categories if not provided by server
            if (!this.categories || this.categories.length === 0) {
                this.loadCategories();
            }
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
