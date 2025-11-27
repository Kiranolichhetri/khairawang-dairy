<?php
/**
 * Contact Page
 * KHAIRAWANG DAIRY
 */
$view->extends('app');
?>

<?php $view->section('content'); ?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-dark-brown mb-4">Contact Us</h1>
        <p class="text-lg text-gray-600">
            We'd love to hear from you! Send us a message and we'll respond as soon as possible.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white rounded-xl shadow-soft p-8">
            <h2 class="text-2xl font-semibold text-dark-brown mb-6">Send us a Message</h2>
            
            <?php if ($view->flash('success')): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    <?= $view->e($view->flash('success')) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($view->flash('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <?= $view->e($view->flash('error')) ?>
                </div>
            <?php endif; ?>
            
            <form action="/contact" method="POST" class="space-y-6">
                <?= $view->csrf() ?>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Your Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= $view->e($view->old('name')) ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent transition <?= $view->hasError('name') ? 'border-red-500' : '' ?>"
                        placeholder="John Doe"
                    >
                    <?php if ($view->hasError('name')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('name')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= $view->e($view->old('email')) ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent transition <?= $view->hasError('email') ? 'border-red-500' : '' ?>"
                        placeholder="john@example.com"
                    >
                    <?php if ($view->hasError('email')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('email')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?= $view->e($view->old('phone')) ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent transition"
                        placeholder="98XXXXXXXX"
                    >
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        value="<?= $view->e($view->old('subject')) ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent transition <?= $view->hasError('subject') ? 'border-red-500' : '' ?>"
                        placeholder="How can we help?"
                    >
                    <?php if ($view->hasError('subject')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('subject')) ?></p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="message" 
                        name="message" 
                        rows="5"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent transition <?= $view->hasError('message') ? 'border-red-500' : '' ?>"
                        placeholder="Tell us more about your inquiry..."
                    ><?= $view->e($view->old('message')) ?></textarea>
                    <?php if ($view->hasError('message')): ?>
                        <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('message')) ?></p>
                    <?php endif; ?>
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-accent-orange text-white py-3 px-6 rounded-lg font-semibold hover:bg-orange-600 transition focus:ring-2 focus:ring-offset-2 focus:ring-accent-orange"
                >
                    Send Message
                </button>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div class="space-y-8">
            <div class="bg-warm-cream rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-dark-brown mb-6">Get in Touch</h2>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📍</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-dark-brown">Address</h3>
                            <p class="text-gray-600">Kathmandu, Nepal</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">📞</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-dark-brown">Phone</h3>
                            <p class="text-gray-600">+977-9800000000</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">✉️</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-dark-brown">Email</h3>
                            <p class="text-gray-600">info@khairawangdairy.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-accent-orange/10 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🕒</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-dark-brown">Business Hours</h3>
                            <p class="text-gray-600">
                                Sunday - Friday: 7:00 AM - 7:00 PM<br>
                                Saturday: 8:00 AM - 5:00 PM
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-accent-orange to-orange-600 rounded-xl p-8 text-white">
                <h3 class="text-xl font-semibold mb-3">Quick Response Guaranteed</h3>
                <p class="opacity-90">
                    We typically respond to all inquiries within 24 hours. For urgent matters, 
                    please call us directly.
                </p>
            </div>
        </div>
    </div>
</div>

<?php $view->endSection(); ?>
