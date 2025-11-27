<?php
/**
 * Newsletter Unsubscribe Page
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
$success = $success ?? false;
$message = $message ?? '';
?>

<?php $view->section('content'); ?>

<div class="max-w-lg mx-auto px-4 py-24 text-center">
    <?php if ($success): ?>
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
                <span class="text-5xl">📭</span>
            </div>
            <h1 class="text-3xl font-bold text-dark-brown mb-4">Unsubscribed Successfully</h1>
            <p class="text-gray-600">
                <?= $view->e($message) ?>
            </p>
        </div>
        
        <div class="bg-warm-cream rounded-xl p-6 mb-8">
            <p class="text-gray-600">
                You have been removed from our newsletter list. You will no longer receive promotional emails from us.
            </p>
        </div>
        
        <div class="space-y-4">
            <p class="text-sm text-gray-500">
                Changed your mind? You can always subscribe again from our website.
            </p>
            <a 
                href="/" 
                class="inline-block bg-accent-orange text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-600 transition"
            >
                Back to Home
            </a>
        </div>
    <?php else: ?>
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-6">
                <span class="text-5xl">❌</span>
            </div>
            <h1 class="text-3xl font-bold text-dark-brown mb-4">Unsubscribe Failed</h1>
            <p class="text-gray-600">
                <?= $view->e($message) ?>
            </p>
        </div>
        
        <p class="text-gray-500 mb-6">
            The unsubscribe link may be invalid or expired. Please contact support if you need help.
        </p>
        
        <a 
            href="/contact" 
            class="inline-block bg-accent-orange text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-600 transition"
        >
            Contact Support
        </a>
    <?php endif; ?>
</div>

<?php $view->endSection(); ?>
