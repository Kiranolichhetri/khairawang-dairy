<?php 
/**
 * Order History Page
 * 
 * Displays user's order history.
 */
$view->extends('app');
$view->section('title');
echo 'My Orders';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8" x-data="ordersPage">
    <div class="container-dairy max-w-4xl">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-heading font-bold text-dark-brown mb-2">My Orders</h1>
            <p class="text-gray-600">View and track your order history</p>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-accent-orange border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- No Orders -->
        <div x-show="!loading && orders.length === 0" class="text-center py-16 bg-white rounded-xl shadow-soft">
            <div class="w-24 h-24 mx-auto mb-6 bg-light-gray rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="text-2xl font-heading font-semibold text-dark-brown mb-2">No Orders Yet</h2>
            <p class="text-gray-500 mb-8">You haven't placed any orders. Start shopping!</p>
            <a href="/products" class="btn btn-primary">Browse Products</a>
        </div>

        <!-- Orders List -->
        <div x-show="!loading && orders.length > 0" class="space-y-4">
            <template x-for="order in orders" :key="order.id">
                <div class="bg-white rounded-xl shadow-soft overflow-hidden">
                    <!-- Order Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Order Number</p>
                                <p class="font-semibold text-dark-brown" x-text="order.order_number"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date</p>
                                <p class="font-medium text-dark-brown" x-text="formatDate(order.created_at)"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="font-bold text-accent-orange">Rs. <span x-text="formatPrice(order.total)"></span></p>
                            </div>
                            <div>
                                <span :class="'bg-' + order.status_color + ' bg-opacity-10 text-' + order.status_color"
                                      class="px-3 py-1 rounded-full text-sm font-medium"
                                      x-text="order.status_label"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="p-4 bg-light-gray flex flex-wrap items-center justify-between gap-4">
                        <p class="text-sm text-gray-600">
                            <span x-text="order.item_count"></span> item(s) · 
                            Payment: <span class="font-medium" x-text="order.payment_status_label"></span>
                        </p>
                        <div class="flex gap-2">
                            <a :href="'/account/orders/' + order.order_number" 
                               class="px-4 py-2 bg-white rounded-lg text-sm font-medium text-dark-brown hover:bg-gray-100 transition-colors">
                                View Details
                            </a>
                            <a :href="'/account/orders/' + order.order_number + '/track'" 
                               class="px-4 py-2 bg-white rounded-lg text-sm font-medium text-dark-brown hover:bg-gray-100 transition-colors">
                                Track Order
                            </a>
                            <button x-show="order.can_cancel" 
                                    @click="cancelOrder(order.order_number)"
                                    class="px-4 py-2 bg-error-red bg-opacity-10 rounded-lg text-sm font-medium text-error-red hover:bg-opacity-20 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ordersPage', () => ({
        orders: [],
        loading: true,
        
        init() {
            this.loadOrders();
        },
        
        async loadOrders() {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/orders');
                const data = await response.json();
                
                if (data.success) {
                    this.orders = data.data;
                }
            } catch (error) {
                console.error('Failed to load orders:', error);
                this.$store.toast.error('Failed to load orders');
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
                month: 'short',
                day: 'numeric'
            });
        },
        
        async cancelOrder(orderNumber) {
            if (!confirm('Are you sure you want to cancel this order?')) return;
            
            try {
                const response = await fetch('/api/v1/orders/' + orderNumber + '/cancel', {
                    method: 'POST'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.$store.toast.success('Order cancelled');
                    this.loadOrders();
                } else {
                    this.$store.toast.error(data.message || 'Failed to cancel order');
                }
            } catch (error) {
                this.$store.toast.error('Failed to cancel order');
            }
        }
    }));
});
</script>
