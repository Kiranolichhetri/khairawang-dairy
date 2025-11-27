<?php 
/**
 * Order Confirmation Page
 * 
 * Displays order confirmation details after successful checkout.
 */
$view->extends('app');
$view->section('title');
echo 'Order Confirmed';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8" x-data="orderConfirmation">
    <div class="container-dairy max-w-4xl">
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Order Not Found -->
        <div x-show="!loading && !order" class="text-center py-16 bg-white rounded-xl shadow-soft">
            <div class="w-24 h-24 mx-auto mb-6 bg-error-red bg-opacity-10 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-error-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h2 class="text-2xl font-heading font-semibold text-dark-brown mb-2">Order Not Found</h2>
            <p class="text-gray-500 mb-8">The order you're looking for doesn't exist or has been removed.</p>
            <a href="/products" class="btn btn-primary">Continue Shopping</a>
        </div>

        <!-- Order Confirmation -->
        <div x-show="!loading && order" class="space-y-8">
            <!-- Success Header -->
            <div class="text-center bg-white rounded-xl shadow-soft p-8">
                <div class="w-20 h-20 mx-auto mb-6 bg-success-green bg-opacity-10 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-success-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2">Order Confirmed!</h1>
                <p class="text-gray-600 mb-4">Thank you for your order. We'll send you a confirmation email shortly.</p>
                <p class="text-lg">
                    Order Number: <span class="font-bold text-accent-orange" x-text="order.order_number"></span>
                </p>
            </div>

            <!-- Order Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Shipping Details -->
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-lg font-semibold text-dark-brown mb-4">Shipping Details</h2>
                    <div class="space-y-2 text-gray-600">
                        <p class="font-medium text-dark-brown" x-text="order.shipping_name"></p>
                        <p x-text="order.shipping_email"></p>
                        <p x-text="order.shipping_phone"></p>
                        <p x-text="order.shipping_address"></p>
                        <p x-show="order.shipping_city" x-text="order.shipping_city"></p>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-lg font-semibold text-dark-brown mb-4">Order Status</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status</span>
                            <span class="font-medium" :class="'text-' + getStatusColor(order.status)" x-text="order.status_label"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Status</span>
                            <span class="font-medium" :class="'text-' + getPaymentColor(order.payment_status)" x-text="order.payment_status_label"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="font-medium text-dark-brown" x-text="formatPaymentMethod(order.payment_method)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Date</span>
                            <span class="font-medium text-dark-brown" x-text="formatDate(order.created_at)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-soft p-6">
                <h2 class="text-lg font-semibold text-dark-brown mb-4">Order Items</h2>
                <div class="space-y-4">
                    <template x-for="item in items" :key="item.id">
                        <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-0">
                            <img :src="item.image" :alt="item.product_name" class="w-20 h-20 rounded-lg object-cover">
                            <div class="flex-1">
                                <h3 class="font-medium text-dark-brown" x-text="item.product_name"></h3>
                                <p x-show="item.variant_name" class="text-sm text-gray-500" x-text="item.variant_name"></p>
                                <p class="text-sm text-gray-500">Qty: <span x-text="item.quantity"></span></p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-dark-brown">Rs. <span x-text="formatPrice(item.total)"></span></p>
                                <p class="text-sm text-gray-500">@ Rs. <span x-text="formatPrice(item.price)"></span></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Totals -->
                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>Rs. <span x-text="formatPrice(order.subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span :class="order.shipping_cost === 0 ? 'text-success-green' : ''">
                            <span x-show="order.shipping_cost === 0">FREE</span>
                            <span x-show="order.shipping_cost > 0">Rs. <span x-text="formatPrice(order.shipping_cost)"></span></span>
                        </span>
                    </div>
                    <div x-show="order.discount > 0" class="flex justify-between text-success-green">
                        <span>Discount</span>
                        <span>-Rs. <span x-text="formatPrice(order.discount)"></span></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                        <span class="text-dark-brown">Total</span>
                        <span class="text-accent-orange">Rs. <span x-text="formatPrice(order.total)"></span></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/products" class="btn btn-secondary justify-center">Continue Shopping</a>
                <a :href="'/invoice/' + order.order_number" target="_blank" class="btn btn-primary justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    View Invoice
                </a>
                <a :href="'/account/orders/' + order.order_number + '/track'" class="btn btn-outline justify-center">
                    Track Order
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderConfirmation', () => ({
        order: null,
        items: [],
        loading: true,
        
        init() {
            const orderNumber = window.location.pathname.split('/').pop();
            this.loadOrder(orderNumber);
        },
        
        async loadOrder(orderNumber) {
            this.loading = true;
            try {
                const response = await fetch('/checkout/success/' + orderNumber);
                const data = await response.json();
                
                if (data.success) {
                    this.order = data.data.order;
                    this.items = data.data.items;
                }
            } catch (error) {
                console.error('Failed to load order:', error);
            } finally {
                this.loading = false;
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        formatPaymentMethod(method) {
            const methods = {
                'esewa': 'eSewa',
                'khalti': 'Khalti',
                'cod': 'Cash on Delivery'
            };
            return methods[method] || method;
        },
        
        getStatusColor(status) {
            const colors = {
                'pending': 'warning-yellow',
                'processing': 'info-blue',
                'shipped': 'info-blue',
                'delivered': 'success-green',
                'cancelled': 'error-red'
            };
            return colors[status] || 'gray-600';
        },
        
        getPaymentColor(status) {
            const colors = {
                'pending': 'warning-yellow',
                'paid': 'success-green',
                'failed': 'error-red',
                'refunded': 'info-blue'
            };
            return colors[status] || 'gray-600';
        }
    }));
});
</script>
