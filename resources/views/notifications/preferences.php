<?php
/**
 * Notification Preferences Page
 * KHAIRAWANG DAIRY
 */
$view->extends('account');
$preferences = $preferences ?? null;
?>

<?php $view->section('content'); ?>

<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="/account/notifications" class="text-accent-orange hover:text-orange-600 text-sm">
            ← Back to Notifications
        </a>
        <h1 class="text-3xl font-bold text-dark-brown mt-2">Notification Preferences</h1>
        <p class="text-gray-600">Choose how you'd like to receive notifications</p>
    </div>
    
    <?php if ($view->flash('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            <?= $view->e($view->flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <form action="/account/notifications/preferences" method="POST" class="space-y-8">
        <?= $view->csrf() ?>
        
        <!-- Email Notifications -->
        <div class="bg-white rounded-xl shadow-soft p-6">
            <h2 class="text-xl font-semibold text-dark-brown mb-4 flex items-center gap-2">
                <span>✉️</span> Email Notifications
            </h2>
            
            <div class="space-y-4">
                <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <div>
                        <span class="font-medium text-dark-brown">Order Updates</span>
                        <p class="text-sm text-gray-500">Receive emails about your order status</p>
                    </div>
                    <input 
                        type="checkbox" 
                        name="email_orders" 
                        value="1"
                        <?= ($preferences?->email_orders ?? true) ? 'checked' : '' ?>
                        class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                    >
                </label>
                
                <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <div>
                        <span class="font-medium text-dark-brown">Promotions & Offers</span>
                        <p class="text-sm text-gray-500">Get notified about deals and special offers</p>
                    </div>
                    <input 
                        type="checkbox" 
                        name="email_promotions" 
                        value="1"
                        <?= ($preferences?->email_promotions ?? true) ? 'checked' : '' ?>
                        class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                    >
                </label>
                
                <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <div>
                        <span class="font-medium text-dark-brown">Newsletter</span>
                        <p class="text-sm text-gray-500">Weekly updates and dairy tips</p>
                    </div>
                    <input 
                        type="checkbox" 
                        name="email_newsletter" 
                        value="1"
                        <?= ($preferences?->email_newsletter ?? true) ? 'checked' : '' ?>
                        class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                    >
                </label>
            </div>
        </div>
        
        <!-- SMS Notifications -->
        <div class="bg-white rounded-xl shadow-soft p-6">
            <h2 class="text-xl font-semibold text-dark-brown mb-4 flex items-center gap-2">
                <span>📱</span> SMS Notifications
            </h2>
            
            <div class="space-y-4">
                <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <div>
                        <span class="font-medium text-dark-brown">Order Updates</span>
                        <p class="text-sm text-gray-500">Receive SMS about your order status</p>
                    </div>
                    <input 
                        type="checkbox" 
                        name="sms_orders" 
                        value="1"
                        <?= ($preferences?->sms_orders ?? true) ? 'checked' : '' ?>
                        class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                    >
                </label>
                
                <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                    <div>
                        <span class="font-medium text-dark-brown">Promotions & Offers</span>
                        <p class="text-sm text-gray-500">Get SMS about deals and special offers</p>
                    </div>
                    <input 
                        type="checkbox" 
                        name="sms_promotions" 
                        value="1"
                        <?= ($preferences?->sms_promotions ?? false) ? 'checked' : '' ?>
                        class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                    >
                </label>
            </div>
        </div>
        
        <!-- Push Notifications -->
        <div class="bg-white rounded-xl shadow-soft p-6">
            <h2 class="text-xl font-semibold text-dark-brown mb-4 flex items-center gap-2">
                <span>🔔</span> In-App Notifications
            </h2>
            
            <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                <div>
                    <span class="font-medium text-dark-brown">Enable Notifications</span>
                    <p class="text-sm text-gray-500">Receive notifications in the app</p>
                </div>
                <input 
                    type="checkbox" 
                    name="push_enabled" 
                    value="1"
                    <?= ($preferences?->push_enabled ?? true) ? 'checked' : '' ?>
                    class="w-5 h-5 text-accent-orange border-gray-300 rounded focus:ring-accent-orange"
                >
            </label>
        </div>
        
        <div class="flex justify-end">
            <button 
                type="submit"
                class="bg-accent-orange text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-600 transition focus:ring-2 focus:ring-offset-2 focus:ring-accent-orange"
            >
                Save Preferences
            </button>
        </div>
    </form>
</div>

<?php $view->endSection(); ?>
