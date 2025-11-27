<?php 
/**
 * Checkout Page
 * 
 * Checkout form with shipping info, payment method selection.
 */
$view->extends('app');
$view->section('title');
echo 'Checkout';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8" x-data="checkoutPage">
    <div class="container-dairy">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2">Checkout</h1>
            <p class="text-gray-600">Complete your order</p>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Empty Cart Redirect -->
        <div x-show="!loading && (!cart.items || cart.items.length === 0)" class="text-center py-16 bg-white rounded-xl shadow-soft">
            <h2 class="text-2xl font-heading font-semibold text-dark-brown mb-4">Your cart is empty</h2>
            <p class="text-gray-500 mb-8">Add some products before checkout.</p>
            <a href="/products" class="btn btn-primary">Browse Products</a>
        </div>

        <!-- Checkout Form -->
        <form x-show="!loading && cart.items && cart.items.length > 0" 
              @submit.prevent="submitOrder" 
              class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Shipping Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-xl font-heading font-semibold text-dark-brown mb-6">Shipping Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" x-model="form.name" required
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                   placeholder="Enter your full name">
                            <p x-show="errors.name" class="mt-1 text-sm text-error-red" x-text="errors.name"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                            <input type="email" x-model="form.email" required
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                   placeholder="your@email.com">
                            <p x-show="errors.email" class="mt-1 text-sm text-error-red" x-text="errors.email"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" x-model="form.phone" required
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                   placeholder="98XXXXXXXX">
                            <p x-show="errors.phone" class="mt-1 text-sm text-error-red" x-text="errors.phone"></p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                            <textarea x-model="form.address" required rows="3"
                                      class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                      placeholder="Enter your full delivery address"></textarea>
                            <p x-show="errors.address" class="mt-1 text-sm text-error-red" x-text="errors.address"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" x-model="form.city"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                   placeholder="Kathmandu">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Order Notes</label>
                            <input type="text" x-model="form.notes"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent"
                                   placeholder="Special instructions (optional)">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-xl font-heading font-semibold text-dark-brown mb-6">Payment Method</h2>
                    
                    <div class="space-y-3">
                        <template x-for="method in paymentMethods" :key="method.key">
                            <label :class="form.payment_method === method.key ? 'border-accent-orange bg-accent-orange bg-opacity-5' : 'border-gray-200 hover:border-accent-orange'"
                                   class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer transition-colors">
                                <input type="radio" :value="method.key" x-model="form.payment_method" 
                                       class="w-5 h-5 text-accent-orange focus:ring-accent-orange">
                                <div class="flex-1">
                                    <span class="font-medium text-dark-brown" x-text="method.name"></span>
                                    <p x-show="method.key === 'esewa'" class="text-sm text-gray-500">Pay securely with eSewa digital wallet</p>
                                    <p x-show="method.key === 'cod'" class="text-sm text-gray-500">Pay when you receive your order</p>
                                </div>
                                <span x-show="method.key === 'esewa'" class="text-green-600 font-semibold">eSewa</span>
                            </label>
                        </template>
                    </div>
                    <p x-show="errors.payment_method" class="mt-2 text-sm text-error-red" x-text="errors.payment_method"></p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-soft p-6 sticky top-24">
                    <h2 class="text-xl font-heading font-semibold text-dark-brown mb-6">Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div class="space-y-4 max-h-64 overflow-y-auto mb-6">
                        <template x-for="item in cart.items" :key="item.id">
                            <div class="flex gap-3">
                                <img :src="item.image" :alt="item.name" class="w-16 h-16 rounded-lg object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-dark-brown truncate text-sm" x-text="item.name"></h4>
                                    <p class="text-sm text-gray-500">Qty: <span x-text="item.quantity"></span></p>
                                </div>
                                <span class="font-medium text-dark-brown text-sm">Rs. <span x-text="formatPrice(item.total)"></span></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
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

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-dark-brown">Total</span>
                                <span class="text-accent-orange">Rs. <span x-text="formatPrice(cart.total)"></span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="submitting"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-opacity-90'"
                            class="btn btn-primary w-full justify-center mt-6">
                        <svg x-show="submitting" class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Processing...' : 'Place Order'"></span>
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-4">
                        By placing your order, you agree to our Terms & Conditions and Privacy Policy.
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- eSewa Payment Form (Hidden, auto-submit) -->
<form x-ref="esewaForm" :action="esewaData.payment_url" method="POST" style="display:none;">
    <template x-for="(value, key) in esewaData.params" :key="key">
        <input type="hidden" :name="key" :value="value">
    </template>
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutPage', () => ({
        cart: { items: [], subtotal: 0, shipping: 0, total: 0 },
        paymentMethods: [],
        form: {
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            notes: '',
            payment_method: 'cod'
        },
        errors: {},
        loading: true,
        submitting: false,
        esewaData: { payment_url: '', params: {} },
        
        init() {
            this.loadCheckoutData();
        },
        
        async loadCheckoutData() {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/checkout');
                const data = await response.json();
                
                if (data.success) {
                    this.cart = data.data.cart;
                    this.paymentMethods = data.data.payment_methods;
                    
                    // Pre-fill user data if logged in
                    if (data.data.user) {
                        this.form.name = data.data.user.name || '';
                        this.form.email = data.data.user.email || '';
                        this.form.phone = data.data.user.phone || '';
                    }
                }
            } catch (error) {
                console.error('Failed to load checkout:', error);
            } finally {
                this.loading = false;
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
        },
        
        validateForm() {
            this.errors = {};
            
            if (!this.form.name.trim()) this.errors.name = 'Name is required';
            if (!this.form.email.trim()) this.errors.email = 'Email is required';
            else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(this.form.email)) this.errors.email = 'Invalid email format';
            if (!this.form.phone.trim()) this.errors.phone = 'Phone is required';
            else {
                const phoneDigits = this.form.phone.replace(/[^0-9]/g, '');
                // Validate Nepal mobile numbers (start with 98, 97, or 96)
                if (phoneDigits.length === 10) {
                    if (!/^(98|97|96)[0-9]{8}$/.test(phoneDigits)) {
                        this.errors.phone = 'Invalid Nepal mobile number (must start with 98, 97, or 96)';
                    }
                } else if (phoneDigits.length < 10 || phoneDigits.length > 15) {
                    this.errors.phone = 'Phone number must be 10-15 digits';
                }
            }
            if (!this.form.address.trim()) this.errors.address = 'Address is required';
            if (!this.form.payment_method) this.errors.payment_method = 'Select payment method';
            
            return Object.keys(this.errors).length === 0;
        },
        
        async submitOrder() {
            if (!this.validateForm()) return;
            
            this.submitting = true;
            try {
                const response = await fetch('/api/v1/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                
                if (data.success) {
                    // Handle eSewa redirect
                    if (data.redirect && data.method === 'esewa') {
                        this.esewaData = {
                            payment_url: data.payment_url,
                            params: data.params
                        };
                        this.$nextTick(() => {
                            this.$refs.esewaForm.submit();
                        });
                        return;
                    }
                    
                    // For COD, redirect to confirmation
                    this.$store.cart.refresh();
                    window.location.href = data.redirect_url || '/checkout/success/' + data.order.order_number;
                } else {
                    this.$store.toast.error(data.message || 'Failed to place order');
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                }
            } catch (error) {
                this.$store.toast.error('Failed to place order');
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>
