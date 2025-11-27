<?php
/**
 * Admin Create Newsletter Campaign Page
 * KHAIRAWANG DAIRY
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>

<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="/admin/newsletter/campaigns" class="text-accent-orange hover:text-orange-600 text-sm">
                ← Back to Campaigns
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Create Newsletter Campaign</h1>
        </div>
        
        <?php if ($view->flash('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <?= $view->e($view->flash('error')) ?>
            </div>
        <?php endif; ?>
        
        <form action="/admin/newsletter/campaigns" method="POST" class="space-y-6">
            <?= $view->csrf() ?>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Campaign Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                            Subject Line <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="subject" 
                            name="subject" 
                            value="<?= $view->e($view->old('subject')) ?>"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent <?= $view->hasError('subject') ? 'border-red-500' : '' ?>"
                            placeholder="Enter email subject line..."
                        >
                        <?php if ($view->hasError('subject')): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('subject')) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-gray-500">Write a compelling subject line that encourages opens.</p>
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Content <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="15"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent font-mono text-sm <?= $view->hasError('content') ? 'border-red-500' : '' ?>"
                            placeholder="Enter your newsletter content here... HTML is supported."
                        ><?= $view->e($view->old('content')) ?></textarea>
                        <?php if ($view->hasError('content')): ?>
                            <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('content')) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-gray-500">You can use HTML for formatting. The content will be wrapped in our email template.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Publishing Options</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="status" 
                                    value="draft" 
                                    checked
                                    class="w-4 h-4 text-accent-orange border-gray-300 focus:ring-accent-orange"
                                >
                                <span>Save as Draft</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800 text-sm">
                    <strong>Note:</strong> After creating the campaign, you can preview it and send it to all active subscribers from the campaigns list.
                </p>
            </div>
            
            <div class="flex items-center justify-end gap-4">
                <a 
                    href="/admin/newsletter/campaigns"
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancel
                </a>
                <button 
                    type="submit"
                    class="bg-accent-orange text-white py-3 px-8 rounded-lg font-semibold hover:bg-orange-600 transition focus:ring-2 focus:ring-offset-2 focus:ring-accent-orange"
                >
                    Create Campaign
                </button>
            </div>
        </form>
    </div>
</div>

<?php $view->endSection(); ?>
