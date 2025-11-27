<?php 
/**
 * Shopping Cart Page
 * 
 * Displays cart items with quantity controls and checkout button.
 */
$view->extends('app');
$view->section('title');
echo 'Shopping Cart';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8" x-data="cartPage">
    <div class="container-dairy">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2">Shopping Cart</h1>
            <p class="text-gray-600" x-show="cart.count > 0">
                You have <span class="font-semibold" x-text="cart.count"></span> items in your cart
            </p>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Empty Cart -->
        <div x-show="!loading && cart.items.length === 0" class="text-center py-16 bg-white rounded-xl shadow-soft">
            <div class="w-24 h-24 mx-auto mb-6 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-heading font-semibold text-dark-brown mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-8">Looks like you haven't added any products yet.</p>
            <a href="/products" class="btn btn-primary">Start Shopping</a>
        </div>

        <!-- Cart Content -->
        <div x-show="!loading && cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                <template x-for="item in cart.items" :key="item.id">
                    <div class="bg-white rounded-xl shadow-soft p-6 flex gap-6">
                        <!-- Product Image -->
                        <a :href="'/products/' + item.slug" class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                        </a>
                        
                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <a :href="'/products/' + item.slug" class="block">
                                <h3 class="font-semibold text-dark-brown truncate" x-text="item.name"></h3>
                            </a>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-accent-orange font-bold">
                                    Rs. <span x-text="formatPrice(item.price)"></span>
                                </span>
                                <span x-show="item.price < item.original_price" class="text-sm text-gray-400 line-through">
                                    Rs. <span x-text="formatPrice(item.original_price)"></span>
                                </span>
                            </div>
                            
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-4 mt-4">
                                <div class="flex items-center border border-gray-200 rounded-lg">
                                    <button @click="updateQuantity(item.id, item.quantity - 1)"
                                            class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-light-gray transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-12 text-center font-medium" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(item.id, item.quantity + 1)"
                                            :disabled="item.quantity >= item.stock"
                                            :class="item.quantity >= item.stock ? 'opacity-50 cursor-not-allowed' : 'hover:bg-light-gray'"
                                            class="w-10 h-10 flex items-center justify-center text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <button @click="removeItem(item.id)" 
                                        class="text-gray-400 hover:text-error-red transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Item Total -->
                        <div class="flex-shrink-0 text-right">
                            <span class="font-bold text-dark-brown">
                                Rs. <span x-text="formatPrice(item.total)"></span>
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Clear Cart Button -->
                <div class="flex justify-between items-center pt-4">
                    <a href="/products" class="text-accent-orange hover:underline">← Continue Shopping</a>
                    <button @click="clearCart" class="text-gray-500 hover:text-error-red transition-colors">
                        Clear Cart
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-soft p-6 sticky top-24">
                    <h2 class="text-xl font-heading font-semibold text-dark-brown mb-6">Order Summary</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-dark-brown">Rs. <span x-text="formatPrice(cart.subtotal)"></span></span>
                        </div>
                        
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span :class="cart.free_shipping ? 'text-success-green' : 'text-dark-brown'" class="font-medium">
                                <span x-show="cart.free_shipping">FREE</span>
                                <span x-show="!cart.free_shipping">Rs. <span x-text="formatPrice(cart.shipping)"></span></span>
                            </span>
                        </div>

                        <!-- Free Shipping Progress -->
                        <div x-show="!cart.free_shipping" class="bg-light-gray rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-2">
                                Add <span class="font-semibold text-accent-orange">Rs. <span x-text="formatPrice(cart.free_shipping_threshold - cart.subtotal)"></span></span> more for free shipping!
                            </p>
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-orange rounded-full transition-all duration-300" 
                                     :style="'width: ' + Math.min((cart.subtotal / cart.free_shipping_threshold) * 100, 100) + '%'"></div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-dark-brown">Total</span>
                                <span class="text-accent-orange">Rs. <span x-text="formatPrice(cart.total)"></span></span>
                            </div>
                        </div>
                    </div>

                    <a href="/checkout" class="btn btn-primary w-full justify-center mt-6">
                        Proceed to Checkout
                    </a>

                    <!-- Payment Methods -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500 text-center mb-3">We accept</p>
                        <div class="flex justify-center gap-4">
                            <span class="text-sm font-medium text-green-600">eSewa</span>
                            <span class="text-sm font-medium text-gray-600">Cash on Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cartPage', () => ({
        cart: {
            items: [],
            count: 0,
            subtotal: 0,
            shipping: 0,
            total: 0,
            free_shipping: false,
            free_shipping_threshold: 1000
        },
        loading: true,
        
        init() {
            this.loadCart();
        },
        
        async loadCart() {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/cart');
                const data = await response.json();
                
                if (data.success) {
                    this.cart = data.data;
                }
            } catch (error) {
                console.error('Failed to load cart:', error);
            } finally {
                this.loading = false;
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
        },
        
        async updateQuantity(itemId, quantity) {
            if (quantity < 1) {
                return this.removeItem(itemId);
            }
            
            try {
                const response = await fetch('/api/v1/cart/items/' + itemId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ quantity })
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cart = data.cart;
                    this.$store.cart.refresh();
                } else {
                    this.$store.toast.error(data.message || 'Failed to update');
                }
            } catch (error) {
                this.$store.toast.error('Failed to update cart');
            }
        },
        
        async removeItem(itemId) {
            try {
                const response = await fetch('/api/v1/cart/items/' + itemId, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cart = data.cart;
                    this.$store.cart.refresh();
                    this.$store.toast.success('Item removed');
                }
            } catch (error) {
                this.$store.toast.error('Failed to remove item');
            }
        },
        
        async clearCart() {
            if (!confirm('Are you sure you want to clear your cart?')) return;
            
            try {
                const response = await fetch('/api/v1/cart/clear', {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cart = data.cart;
                    this.$store.cart.refresh();
                    this.$store.toast.success('Cart cleared');
                }
            } catch (error) {
                this.$store.toast.error('Failed to clear cart');
            }
        }
    }));
});
</script>
