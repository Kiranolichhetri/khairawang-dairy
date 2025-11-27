<?php 
/**
 * Order Details Page
 * 
 * Displays detailed order information with tracking.
 */
$view->extends('app');
$view->section('title');
echo 'Order Details';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8" x-data="orderDetails">
    <div class="container-dairy max-w-4xl">
        <!-- Back Button -->
        <a href="/account/orders" class="inline-flex items-center gap-2 text-gray-600 hover:text-accent-orange mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Orders
        </a>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Order Not Found -->
        <div x-show="!loading && !order" class="text-center py-16 bg-white rounded-xl shadow-soft">
            <h2 class="text-2xl font-heading font-semibold text-dark-brown mb-2">Order Not Found</h2>
            <p class="text-gray-500 mb-8">The order you're looking for doesn't exist.</p>
            <a href="/account/orders" class="btn btn-primary">View All Orders</a>
        </div>

        <!-- Order Details -->
        <div x-show="!loading && order" class="space-y-6">
            <!-- Order Header -->
            <div class="bg-white rounded-xl shadow-soft p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-heading font-bold text-dark-brown mb-2">
                            Order #<span x-text="order.order_number"></span>
                        </h1>
                        <p class="text-gray-600">Placed on <span x-text="formatDate(order.created_at)"></span></p>
                    </div>
                    <div class="flex gap-3">
                        <span :class="'bg-' + getStatusColor(order.status) + ' bg-opacity-10 text-' + getStatusColor(order.status)"
                              class="px-4 py-2 rounded-full font-medium"
                              x-text="order.status_label"></span>
                        <button x-show="order.can_cancel" 
                                @click="cancelOrder"
                                class="px-4 py-2 border border-error-red text-error-red rounded-lg hover:bg-error-red hover:text-white transition-colors">
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white rounded-xl shadow-soft p-6" x-show="timeline.length > 0">
                <h2 class="text-lg font-semibold text-dark-brown mb-6">Order Status</h2>
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    <div class="space-y-6">
                        <template x-for="(step, index) in timeline" :key="step.key">
                            <div class="relative flex items-center gap-4">
                                <div :class="step.completed ? 'bg-success-green text-white' : 'bg-gray-200 text-gray-400'"
                                     class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg x-show="step.completed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-show="!step.completed" x-text="index + 1" class="text-sm"></span>
                                </div>
                                <div>
                                    <p :class="step.completed ? 'text-dark-brown font-medium' : 'text-gray-400'" x-text="step.label"></p>
                                    <p x-show="step.current" class="text-sm text-accent-orange">Current Status</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shipping Details -->
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-lg font-semibold text-dark-brown mb-4">Shipping Details</h2>
                    <div class="space-y-2 text-gray-600">
                        <p class="font-medium text-dark-brown" x-text="order.shipping?.name"></p>
                        <p x-text="order.shipping?.email"></p>
                        <p x-text="order.shipping?.phone"></p>
                        <p x-text="order.shipping?.address"></p>
                        <p x-show="order.shipping?.city" x-text="order.shipping?.city"></p>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-xl shadow-soft p-6">
                    <h2 class="text-lg font-semibold text-dark-brown mb-4">Payment Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method</span>
                            <span class="font-medium text-dark-brown" x-text="formatPaymentMethod(order.payment_method)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status</span>
                            <span :class="'text-' + getPaymentColor(order.payment_status)" class="font-medium" x-text="order.payment_status_label"></span>
                        </div>
                        <div x-show="order.transaction_id" class="flex justify-between">
                            <span class="text-gray-600">Transaction ID</span>
                            <span class="font-medium text-dark-brown" x-text="order.transaction_id"></span>
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
                            <a :href="'/products/' + item.slug" class="flex-shrink-0">
                                <img :src="item.image" :alt="item.product_name" class="w-20 h-20 rounded-lg object-cover">
                            </a>
                            <div class="flex-1">
                                <a :href="'/products/' + item.slug" class="font-medium text-dark-brown hover:text-accent-orange" x-text="item.product_name"></a>
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
            <div class="flex flex-wrap gap-4">
                <a :href="'/invoice/' + order.order_number" target="_blank" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Invoice
                </a>
                <a href="/products" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderDetails', () => ({
        order: null,
        items: [],
        timeline: [],
        loading: true,
        
        init() {
            const orderNumber = window.location.pathname.split('/').filter(Boolean).pop();
            this.loadOrder(orderNumber);
        },
        
        async loadOrder(orderNumber) {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/orders/' + orderNumber);
                const data = await response.json();
                
                if (data.success) {
                    this.order = data.data.order;
                    this.items = data.data.items;
                    this.loadTimeline(orderNumber);
                }
            } catch (error) {
                console.error('Failed to load order:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadTimeline(orderNumber) {
            try {
                const response = await fetch('/api/v1/orders/' + orderNumber + '/track');
                const data = await response.json();
                
                if (data.success) {
                    this.timeline = data.data.timeline || [];
                }
            } catch (error) {
                console.error('Failed to load timeline:', error);
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-NP').format(price);
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        formatPaymentMethod(method) {
            const methods = { 'esewa': 'eSewa', 'khalti': 'Khalti', 'cod': 'Cash on Delivery' };
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
            const colors = { 'pending': 'warning-yellow', 'paid': 'success-green', 'failed': 'error-red' };
            return colors[status] || 'gray-600';
        },
        
        async cancelOrder() {
            if (!confirm('Are you sure you want to cancel this order?')) return;
            
            try {
                const response = await fetch('/api/v1/orders/' + this.order.order_number + '/cancel', { method: 'POST' });
                const data = await response.json();
                
                if (data.success) {
                    this.$store.toast.success('Order cancelled');
                    this.loadOrder(this.order.order_number);
                } else {
                    this.$store.toast.error(data.message || 'Failed to cancel');
                }
            } catch (error) {
                this.$store.toast.error('Failed to cancel order');
            }
        }
    }));
});
</script>
