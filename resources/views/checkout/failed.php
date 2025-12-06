<?php 
/**
 * Checkout Failed Page
 * 
 * Displays payment failure message and recovery options.
 */
$view->extends('app');
$view->section('title');
echo 'Payment Failed';
$view->endSection();
?>

<div class="min-h-screen bg-cream py-8">
    <div class="container-dairy max-w-2xl">
        <!-- Failure Message -->
        <div class="bg-white rounded-xl shadow-soft p-8 text-center">
            <!-- Error Icon -->
            <div class="w-20 h-20 mx-auto mb-6 bg-error-red bg-opacity-10 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-error-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-heading font-bold text-dark-brown mb-4">Payment Failed</h1>

            <!-- Error Message -->
            <p class="text-gray-600 mb-2">
                <?php 
                $error = $_GET['error'] ?? 'Your payment could not be processed.';
                echo htmlspecialchars($error);
                ?>
            </p>

            <!-- Order Number (if available) -->
            <?php if (!empty($_GET['order'])): ?>
                <p class="text-sm text-gray-500 mb-6">
                    Order Number: <span class="font-medium text-dark-brown"><?php echo htmlspecialchars($_GET['order']); ?></span>
                </p>
            <?php endif; ?>

            <!-- Possible Reasons -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                <h2 class="font-semibold text-dark-brown mb-3">Possible reasons:</h2>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Insufficient balance in your eSewa wallet</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Payment was cancelled by you</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Incorrect password or MPIN</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Technical issue with payment gateway</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Network connectivity issues</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (!empty($_GET['order'])): ?>
                    <a href="/account/orders/<?php echo htmlspecialchars($_GET['order']); ?>" 
                       class="btn btn-secondary justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        View Order Details
                    </a>
                <?php endif; ?>
                
                <a href="/checkout" class="btn btn-primary justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Try Again
                </a>
                
                <a href="/products" class="btn btn-outline justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Continue Shopping
                </a>
            </div>

            <!-- Help Text -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-2">Need help?</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center text-sm">
                    <a href="/contact" class="text-accent-orange hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Contact Support
                    </a>
                    <span class="text-gray-400 hidden sm:inline">|</span>
                    <a href="tel:+977-1-XXXXXXX" class="text-accent-orange hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Call Us
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Method Info -->
        <div class="mt-6 bg-blue-50 rounded-lg p-4 text-sm text-blue-800">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium mb-1">Payment Tips:</p>
                    <ul class="space-y-1 text-xs">
                        <li>• Ensure you have sufficient balance in your eSewa wallet</li>
                        <li>• Check your internet connection before attempting payment</li>
                        <li>• You can also try Cash on Delivery as an alternative payment method</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
