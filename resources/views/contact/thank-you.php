<?php
/**
 * Contact Thank You Page
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<div class="max-w-2xl mx-auto px-4 py-24 text-center">
    <div class="mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-6">
            <span class="text-5xl">✅</span>
        </div>
        <h1 class="text-4xl font-bold text-dark-brown mb-4">Thank You!</h1>
        <p class="text-lg text-gray-600">
            Your message has been received. We'll get back to you as soon as possible.
        </p>
    </div>
    
    <div class="bg-warm-cream rounded-xl p-8 mb-8">
        <h2 class="text-xl font-semibold text-dark-brown mb-3">What happens next?</h2>
        <ul class="text-left text-gray-600 space-y-2">
            <li class="flex items-start gap-2">
                <span class="text-green-500 mt-1">✓</span>
                <span>Our team will review your inquiry</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="text-green-500 mt-1">✓</span>
                <span>You'll receive a response within 24 hours</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="text-green-500 mt-1">✓</span>
                <span>Check your email for updates</span>
            </li>
        </ul>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a 
            href="/" 
            class="inline-block bg-accent-orange text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-600 transition"
        >
            Back to Home
        </a>
        <a 
            href="/products" 
            class="inline-block bg-white text-dark-brown py-3 px-8 rounded-lg font-semibold border border-gray-300 hover:border-accent-orange hover:text-accent-orange transition"
        >
            Browse Products
        </a>
    </div>
</div>

<?php $view->endSection(); ?>
