<?php
/**
 * 403 Error Page - Forbidden
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<section class="min-h-[60vh] flex items-center justify-center py-16 bg-cream">
    <div class="max-w-xl mx-auto px-4 text-center">
        <div class="w-32 h-32 mx-auto mb-8 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-16 h-16 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        
        <h1 class="text-8xl md:text-9xl font-heading font-bold text-yellow-600 mb-4">403</h1>
        
        <h2 class="text-2xl md:text-3xl font-heading font-bold text-dark-brown mb-4">
            Access Forbidden
        </h2>
        
        <p class="text-gray-600 mb-8">
            Sorry, you don't have permission to access this page. 
            If you believe this is a mistake, please contact our support team.
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/" class="inline-flex items-center px-6 py-3 bg-accent-orange text-white font-semibold rounded-xl hover:bg-orange-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Go Home
            </a>
            <a href="/login" class="inline-flex items-center px-6 py-3 border-2 border-dark-brown text-dark-brown font-semibold rounded-xl hover:bg-dark-brown hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                Sign In
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
