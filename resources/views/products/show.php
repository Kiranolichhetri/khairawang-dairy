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

<div class="min-h-screen bg-cream py-8" x-data="productDetail">
    <div class="container-dairy">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="/" class="hover:text-accent-orange">Home</a>
            <span>/</span>
            <a href="/products" class="hover:text-accent-orange">Products</a>
            <span x-show="category">/</span>
            <a x-show="category" :href="'/categories/' + category.slug" class="hover:text-accent-orange" x-text="category?.name"></a>
            <span>/</span>
            <span class="text-dark-brown" x-text="product.name"></span>
        </nav>

        <!-- Product Detail -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Images -->
            <div class="space-y-4">
                <!-- Main Image -->
                <div class="aspect-square bg-white rounded-xl overflow-hidden shadow-soft">
                    <img :src="selectedImage" :alt="product.name" 
                         class="w-full h-full object-cover">
                </div>
                
                <!-- Thumbnail Gallery -->
                <div x-show="product.images && product.images.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                    <template x-for="(image, index) in product.images" :key="index">
                        <button @click="selectedImage = image"
                                :class="selectedImage === image ? 'ring-2 ring-accent-orange' : 'opacity-70 hover:opacity-100'"
                                class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden transition-opacity">
                            <img :src="image" :alt="'Image ' + (index + 1)" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2" x-text="product.name"></h1>
                    <p class="text-gray-500" x-text="product.short_description"></p>
                </div>

                <!-- Price -->
                <div class="flex items-center gap-4">
                    <span class="text-3xl font-bold text-accent-orange">
                        Rs. <span x-text="formatPrice(product.current_price)"></span>
                    </span>
                    <span x-show="product.is_on_sale" class="text-xl text-gray-400 line-through">
                        Rs. <span x-text="formatPrice(product.price)"></span>
                    </span>
                    <span x-show="product.is_on_sale" 
                          class="text-sm text-white bg-error-red px-3 py-1 rounded-full">
                        Save <span x-text="product.discount_percentage"></span>%
                    </span>
                </div>

                <!-- Stock Status -->
                <div class="flex items-center gap-2">
                    <span :class="product.in_stock ? 'bg-success-green' : 'bg-error-red'"
                          class="w-3 h-3 rounded-full"></span>
                    <span :class="product.in_stock ? 'text-success-green' : 'text-error-red'"
                          x-text="product.stock_status"></span>
                    <span x-show="product.low_stock" class="text-sm text-warning-yellow">
                        - Only <span x-text="product.stock"></span> left!
                    </span>
                </div>

                <!-- Variants (if available) -->
                <div x-show="variants.length > 0" class="space-y-4">
                    <h3 class="font-semibold text-dark-brown">Select Option</h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="variant in variants" :key="variant.id">
                            <button @click="selectedVariant = variant"
                                    :class="selectedVariant?.id === variant.id ? 'border-accent-orange bg-accent-orange bg-opacity-10' : 'border-gray-200 hover:border-accent-orange'"
                                    class="px-4 py-2 border rounded-lg transition-colors">
                                <span x-text="variant.name"></span>
                                <span x-show="variant.price" class="text-sm text-gray-500">
                                    (+Rs. <span x-text="formatPrice(variant.price)"></span>)
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="space-y-2">
                    <label class="font-semibold text-dark-brown">Quantity</label>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center border border-gray-200 rounded-lg">
                            <button @click="quantity = Math.max(1, quantity - 1)"
                                    class="w-12 h-12 flex items-center justify-center text-gray-600 hover:bg-light-gray transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <input type="number" x-model.number="quantity" min="1" :max="product.stock"
                                   class="w-16 h-12 text-center border-x border-gray-200 focus:outline-none">
                            <button @click="quantity = Math.min(product.stock, quantity + 1)"
                                    class="w-12 h-12 flex items-center justify-center text-gray-600 hover:bg-light-gray transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                        <span class="text-sm text-gray-500" x-text="product.stock + ' available'"></span>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <div class="flex gap-4">
                    <button @click="addToCart"
                            :disabled="!product.in_stock || adding"
                            :class="product.in_stock ? 'bg-accent-orange hover:bg-opacity-90' : 'bg-gray-300 cursor-not-allowed'"
                            class="flex-1 py-4 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg x-show="!adding" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <svg x-show="adding" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="adding ? 'Adding...' : 'Add to Cart'"></span>
                    </button>
                    <button class="w-14 h-14 border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:bg-light-gray transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Product Description -->
                <div class="pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-dark-brown mb-4">Description</h3>
                    <div class="prose prose-sm text-gray-600" x-html="product.description"></div>
                </div>

                <!-- Product Details -->
                <div class="pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-dark-brown mb-4">Product Details</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div x-show="product.sku">
                            <dt class="text-gray-500">SKU</dt>
                            <dd class="text-dark-brown" x-text="product.sku"></dd>
                        </div>
                        <div x-show="product.weight">
                            <dt class="text-gray-500">Weight</dt>
                            <dd class="text-dark-brown" x-text="product.weight + ' kg'"></dd>
                        </div>
                        <div x-show="category">
                            <dt class="text-gray-500">Category</dt>
                            <dd class="text-dark-brown" x-text="category?.name"></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div x-show="relatedProducts.length > 0" class="mt-16">
            <h2 class="text-2xl font-heading font-bold text-dark-brown mb-8">Related Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <template x-for="related in relatedProducts" :key="related.id">
                    <a :href="'/products/' + related.slug" 
                       class="bg-white rounded-xl shadow-soft overflow-hidden group hover:shadow-soft-lg transition-shadow">
                        <div class="aspect-square overflow-hidden">
                            <img :src="related.image" :alt="related.name" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-dark-brown truncate" x-text="related.name"></h3>
                            <span class="text-accent-orange font-bold">
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
        
        init() {
            const slug = window.location.pathname.split('/').pop();
            this.loadProduct(slug);
        },
        
        async loadProduct(slug) {
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
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
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
