<?php
/**
 * 500 Error Page - Server Error
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<section class="min-h-[60vh] flex items-center justify-center py-16 bg-cream">
    <div class="max-w-xl mx-auto px-4 text-center">
        <div class="w-32 h-32 mx-auto mb-8 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        
        <h1 class="text-8xl md:text-9xl font-heading font-bold text-red-500 mb-4">500</h1>
        
        <h2 class="text-2xl md:text-3xl font-heading font-bold text-dark-brown mb-4">
            Server Error
        </h2>
        
        <p class="text-gray-600 mb-8">
            Something went wrong on our end. Our team has been notified and we're working to fix it. 
            Please try again in a few minutes.
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/" class="inline-flex items-center px-6 py-3 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Go Home
            </a>
            <button onclick="window.location.reload()" class="inline-flex items-center px-6 py-3 border-2 border-dark-brown text-dark-brown font-semibold rounded-xl hover:bg-dark-brown hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Try Again
            </button>
        </div>
        
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-gray-500 text-sm">
                If the problem persists, please <a href="/contact" class="text-accent-orange hover:underline">contact us</a>
            </p>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
