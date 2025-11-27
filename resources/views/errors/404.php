<?php
/**
 * 404 Error Page - Not Found
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<section class="min-h-[60vh] flex items-center justify-center py-16 bg-cream">
    <div class="max-w-xl mx-auto px-4 text-center">
        <div class="w-32 h-32 mx-auto mb-8 bg-accent-orange/10 rounded-full flex items-center justify-center">
            <span class="text-7xl">🥛</span>
        </div>
        
        <h1 class="text-8xl md:text-9xl font-heading font-bold text-accent-orange mb-4">404</h1>
        
        <h2 class="text-2xl md:text-3xl font-heading font-bold text-dark-brown mb-4">
            Page Not Found
        </h2>
        
        <p class="text-gray-600 mb-8">
            Oops! It looks like the page you're looking for has gone on vacation. 
            Maybe it went to visit our cows at the farm! 🐄
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/" class="inline-flex items-center px-6 py-3 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Go Home
            </a>
            <a href="/products" class="inline-flex items-center px-6 py-3 border-2 border-dark-brown text-dark-brown font-semibold rounded-xl hover:bg-dark-brown hover:text-white transition-colors">
                View Products
            </a>
        </div>
        
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-gray-500 text-sm">
                Need help? <a href="/contact" class="text-accent-orange hover:underline">Contact us</a>
            </p>
        </div>
    </div>
</section>

<?php $view->endSection(); ?>
