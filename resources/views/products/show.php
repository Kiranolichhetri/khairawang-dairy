<?php 
/**
 * Product Detail Page
 * 
 * Displays single product with images, variants, and add to cart functionality.
 */
$view->extends('app');
$view->section('title');
echo $view->e($product['name'] ?? 'Product');
$view->endSection();
?>

<style>
/* Product Detail Page Styles */
.product-detail-container {
    background: linear-gradient(180deg, #fef7f0 0%, #ffffff 100%);
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    flex-wrap: wrap;
}

.breadcrumb a {
    transition: color 0.2s ease;
}

.breadcrumb a:hover {
    color: #f97316;
}

.breadcrumb-separator {
    color: #d1d5db;
}

/* Image Gallery */
.main-image-container {
    position: relative;
    aspect-ratio: 1;
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    margin: 0 auto;
}

@media (min-width: 1024px) {
    .main-image-container {
        max-width: 100%;
    }
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.main-image-container:hover .main-image {
    transform: scale(1.05);
}

.image-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.thumbnail-gallery {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 0.5rem 0;
    scrollbar-width: thin;
    scrollbar-color: #f97316 #f3f4f6;
}

.thumbnail-gallery::-webkit-scrollbar {
    height: 6px;
}

.thumbnail-gallery::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.thumbnail-gallery::-webkit-scrollbar-thumb {
    background: #f97316;
    border-radius: 3px;
}

.thumbnail-btn {
    flex-shrink: 0;
    width: 5rem;
    height: 5rem;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 3px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
}

.thumbnail-btn:hover {
    border-color: #fdba74;
}

.thumbnail-btn.active {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
}

.thumbnail-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Product Info Section */
.product-title {
    font-size: 2rem;
    font-weight: 700;
    color: #4a3728;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
    .product-title {
        font-size: 2.5rem;
    }
}

.product-subtitle {
    font-size: 1.1rem;
    color: #6b7280;
    line-height: 1.6;
}

/* Price Display */
.price-section {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    padding: 1.25rem 1.5rem;
    border-radius: 1rem;
    border: 1px solid #fed7aa;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    color: #f97316;
}

.original-price {
    font-size: 1.25rem;
    color: #9ca3af;
    text-decoration: line-through;
}

.discount-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
}

/* Stock Status */
.stock-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    font-weight: 500;
}

.stock-indicator.in-stock {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.stock-indicator.low-stock {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

.stock-indicator.out-of-stock {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.stock-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.stock-dot.in-stock {
    background: #22c55e;
}

.stock-dot.low-stock {
    background: #f59e0b;
}

.stock-dot.out-of-stock {
    background: #ef4444;
    animation: none;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Variant Selector */
.variant-section {
    padding: 1.25rem;
    background: #f9fafb;
    border-radius: 1rem;
}

.variant-btn {
    padding: 0.75rem 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
    background: white;
}

.variant-btn:hover {
    border-color: #f97316;
}

.variant-btn.selected {
    border-color: #f97316;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    color: #ea580c;
}

/* Quantity Selector */
.quantity-selector {
    display: flex;
    align-items: center;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    background: white;
}

.quantity-btn {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.2s ease;
    background: transparent;
    border: none;
    cursor: pointer;
}

.quantity-btn:hover {
    background: #f3f4f6;
    color: #f97316;
}

.quantity-input {
    width: 4rem;
    height: 3.5rem;
    text-align: center;
    border: none;
    border-left: 2px solid #e5e7eb;
    border-right: 2px solid #e5e7eb;
    font-weight: 600;
    font-size: 1.1rem;
}

.quantity-input:focus {
    outline: none;
}

/* Add to Cart Button */
.add-to-cart-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    padding: 1.25rem 2rem;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    font-size: 1.125rem;
    font-weight: 600;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.3);
}

.add-to-cart-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(249, 115, 22, 0.4);
}

.add-to-cart-btn:active:not(:disabled) {
    transform: translateY(0);
}

.add-to-cart-btn:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    box-shadow: none;
}

.wishlist-btn {
    width: 3.5rem;
    height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    color: #6b7280;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.wishlist-btn:hover {
    border-color: #fca5a5;
    color: #ef4444;
    background: #fef2f2;
}

/* Description Section */
.description-section {
    padding: 1.5rem;
    background: white;
    border-radius: 1rem;
    border: 1px solid #f3f4f6;
}

.description-section h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #4a3728;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.description-content {
    color: #4b5563;
    line-height: 1.8;
}

/* Product Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.detail-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.75rem;
}

.detail-item dt {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.detail-item dd {
    font-weight: 600;
    color: #4a3728;
}

/* Related Products */
.related-section {
    margin-top: 4rem;
    padding-top: 3rem;
    border-top: 1px solid #e5e7eb;
}

.related-section h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #4a3728;
    margin-bottom: 2rem;
    text-align: center;
}

.related-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.related-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1);
}

.related-card .card-image {
    aspect-ratio: 1;
    overflow: hidden;
}

.related-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.related-card:hover .card-image img {
    transform: scale(1.08);
}

.related-card .card-content {
    padding: 1rem;
}

.related-card .card-title {
    font-weight: 600;
    color: #4a3728;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-card .card-price {
    font-weight: 700;
    color: #f97316;
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

/* Free Shipping Banner */
.shipping-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border-radius: 0.75rem;
    border: 1px solid #a7f3d0;
}

.shipping-banner svg {
    flex-shrink: 0;
    color: #059669;
}

.shipping-banner span {
    font-size: 0.875rem;
    color: #065f46;
}
</style>

<div class="product-detail-container min-h-screen py-8 md:py-12" x-data="productDetail">
    <div class="container-dairy">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-8">
            <a href="/" class="hover:text-accent-orange">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="/products" class="hover:text-accent-orange">Products</a>
            <template x-if="category">
                <span class="flex items-center gap-2">
                    <span class="breadcrumb-separator">/</span>
                    <a :href="'/categories/' + category.slug" class="hover:text-accent-orange" x-text="category?.name"></a>
                </span>
            </template>
            <span class="breadcrumb-separator">/</span>
            <span class="text-dark-brown font-medium" x-text="product.name"></span>
        </nav>

        <!-- Loading State -->
        <div x-show="loading" class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12">
            <div class="lg:col-span-2">
                <div class="main-image-container skeleton"></div>
                <div class="flex gap-3 mt-4">
                    <div class="w-20 h-20 rounded-lg skeleton"></div>
                    <div class="w-20 h-20 rounded-lg skeleton"></div>
                    <div class="w-20 h-20 rounded-lg skeleton"></div>
                </div>
            </div>
            <div class="lg:col-span-3 space-y-6">
                <div class="h-10 skeleton rounded-lg w-3/4"></div>
                <div class="h-6 skeleton rounded-lg w-1/2"></div>
                <div class="h-24 skeleton rounded-lg"></div>
                <div class="h-16 skeleton rounded-lg"></div>
                <div class="h-14 skeleton rounded-lg"></div>
            </div>
        </div>

        <!-- Product Detail -->
        <div x-show="!loading" x-cloak class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12">
            <!-- Product Images - takes 2 columns -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Main Image -->
                <div class="main-image-container">
                    <img :src="selectedImage" :alt="product.name" 
                         class="main-image"
                         onerror="this.src='/assets/images/product-placeholder.png'">
                    <span x-show="product.is_on_sale" class="image-badge">
                        -<span x-text="product.discount_percentage"></span>% OFF
                    </span>
                </div>
                
                <!-- Thumbnail Gallery -->
                <div x-show="product.images && product.images.length > 1" class="thumbnail-gallery">
                    <template x-for="(image, index) in product.images" :key="index">
                        <button @click="selectedImage = image"
                                :class="{'active': selectedImage === image}"
                                class="thumbnail-btn">
                            <img :src="image" :alt="'Image ' + (index + 1)"
                                 onerror="this.src='/assets/images/product-placeholder.png'">
                        </button>
                    </template>
                </div>

                <!-- Free Shipping Banner -->
                <div class="shipping-banner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <span><strong>Free Delivery</strong> on orders over Rs. 1,000</span>
                </div>
            </div>

            <!-- Product Info - takes 3 columns -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Title & Description -->
                <div>
                    <h1 class="product-title" x-text="product.name"></h1>
                    <p class="product-subtitle" x-text="product.short_description"></p>
                </div>

                <!-- Price Section -->
                <div class="price-section">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="current-price">
                            Rs. <span x-text="formatPrice(product.current_price)"></span>
                        </span>
                        <span x-show="product.is_on_sale" class="original-price">
                            Rs. <span x-text="formatPrice(product.price)"></span>
                        </span>
                        <span x-show="product.is_on_sale" class="discount-pill">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Save <span x-text="product.discount_percentage"></span>%
                        </span>
                    </div>
                </div>

                <!-- Stock Status -->
                <div :class="{
                    'stock-indicator in-stock': product.in_stock && product.stock > 10,
                    'stock-indicator low-stock': product.in_stock && product.stock <= 10,
                    'stock-indicator out-of-stock': !product.in_stock
                }">
                    <span :class="{
                        'stock-dot in-stock': product.in_stock && product.stock > 10,
                        'stock-dot low-stock': product.in_stock && product.stock <= 10,
                        'stock-dot out-of-stock': !product.in_stock
                    }"></span>
                    <span x-text="product.stock_status"></span>
                    <span x-show="product.in_stock && product.stock <= 10" class="font-normal">
                        — Only <strong x-text="product.stock"></strong> left in stock!
                    </span>
                </div>

                <!-- Variants (if available) -->
                <div x-show="variants.length > 0" class="variant-section">
                    <h3 class="font-semibold text-dark-brown mb-3">Select Option</h3>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="variant in variants" :key="variant.id">
                            <button @click="selectedVariant = variant"
                                    :class="{'selected': selectedVariant?.id === variant.id}"
                                    class="variant-btn">
                                <span x-text="variant.name"></span>
                                <span x-show="variant.price" class="text-sm text-gray-500 ml-1">
                                    (+Rs. <span x-text="formatPrice(variant.price)"></span>)
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="space-y-3">
                    <label class="font-semibold text-dark-brown block">Quantity</label>
                    <div class="flex items-center gap-4">
                        <div class="quantity-selector">
                            <button @click="quantity = Math.max(1, quantity - 1)"
                                    class="quantity-btn"
                                    :disabled="quantity <= 1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <input type="number" x-model.number="quantity" min="1" :max="product.stock || 1"
                                   class="quantity-input">
                            <button @click="quantity = Math.min(product.stock || 1, quantity + 1)"
                                    class="quantity-btn"
                                    :disabled="!product.stock || quantity >= product.stock">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500">
                            <span x-text="product.stock || 0"></span> available
                        </span>
                    </div>
                </div>

                <!-- Add to Cart & Wishlist Buttons -->
                <div class="flex gap-4">
                    <button @click="addToCart"
                            :disabled="!product.in_stock || adding"
                            class="add-to-cart-btn flex-1">
                        <svg x-show="!adding" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <svg x-show="adding" x-cloak class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="adding ? 'Adding to Cart...' : (product.in_stock ? 'Add to Cart' : 'Out of Stock')"></span>
                    </button>
                    <button class="wishlist-btn" title="Add to Wishlist">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Product Description -->
                <div class="description-section">
                    <h3>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Description
                    </h3>
                    <div class="description-content" x-html="product.description || 'No description available.'"></div>
                </div>

                <!-- Product Details -->
                <div class="description-section">
                    <h3>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Product Details
                    </h3>
                    <dl class="details-grid">
                        <div x-show="product.sku" class="detail-item">
                            <dt>SKU</dt>
                            <dd x-text="product.sku"></dd>
                        </div>
                        <div x-show="product.weight" class="detail-item">
                            <dt>Weight</dt>
                            <dd><span x-text="product.weight"></span> kg</dd>
                        </div>
                        <div x-show="category" class="detail-item">
                            <dt>Category</dt>
                            <dd x-text="category?.name"></dd>
                        </div>
                        <div class="detail-item">
                            <dt>Availability</dt>
                            <dd x-text="product.in_stock ? 'In Stock' : 'Out of Stock'"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div x-show="!loading && relatedProducts.length > 0" x-cloak class="related-section">
            <h2>You May Also Like</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <template x-for="related in relatedProducts" :key="related.id">
                    <a :href="'/products/' + related.slug" class="related-card">
                        <div class="card-image">
                            <img :src="related.image" :alt="related.name"
                                 onerror="this.src='/assets/images/product-placeholder.png'">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title" x-text="related.name"></h3>
                            <span class="card-price">
                                Rs. <span x-text="formatPrice(related.current_price)"></span>
                            </span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productDetail', () => ({
        product: {},
        category: null,
        variants: [],
        relatedProducts: [],
        selectedImage: '',
        selectedVariant: null,
        quantity: 1,
        adding: false,
        loading: true,
        
        init() {
            const slug = window.location.pathname.split('/').pop();
            this.loadProduct(slug);
        },
        
        async loadProduct(slug) {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/products/' + slug);
                const data = await response.json();
                
                if (data.success) {
                    this.product = data.data.product;
                    this.category = data.data.category;
                    this.variants = data.data.variants || [];
                    this.relatedProducts = data.data.related_products || [];
                    this.selectedImage = this.product.images?.[0] || this.product.image;
                }
            } catch (error) {
                console.error('Failed to load product:', error);
            } finally {
                this.loading = false;
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price || 0);
        },
        
        async addToCart() {
            this.adding = true;
            try {
                const response = await fetch('/api/v1/cart/items', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: this.product.id,
                        quantity: this.quantity,
                        variant_id: this.selectedVariant?.id || null
                    })
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
            } finally {
                this.adding = false;
            }
        }
    }));
});
</script>
