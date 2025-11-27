<?php
/**
 * Admin Contact Inquiry Detail Page
 * KHAIRAWANG DAIRY
 */
$view->extends('admin');
$inquiry = $inquiry ?? null;
?>

<?php $view->section('content'); ?>

<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="/admin/contacts" class="text-accent-orange hover:text-orange-600 text-sm">
                ← Back to Inquiries
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Contact Inquiry</h1>
        </div>
        
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
        
        <?php if ($inquiry): ?>
            <div class="grid gap-6">
                <!-- Inquiry Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800"><?= $view->e($inquiry->subject ?? '') ?></h2>
                            <p class="text-gray-500 text-sm mt-1">
                                Submitted on <?= $view->date($inquiry->created_at ?? '', 'F j, Y \a\t g:i A') ?>
                            </p>
                        </div>
                        
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?php
                            echo match($inquiry->status ?? 'new') {
                                'new' => 'bg-yellow-100 text-yellow-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                'resolved' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        ?>">
                            <?= $inquiry->getStatusLabel() ?>
                        </span>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wider">Name</label>
                            <p class="font-medium text-gray-800"><?= $view->e($inquiry->name ?? '') ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wider">Email</label>
                            <p class="font-medium text-gray-800">
                                <a href="mailto:<?= $view->e($inquiry->email ?? '') ?>" class="text-accent-orange hover:text-orange-600">
                                    <?= $view->e($inquiry->email ?? '') ?>
                                </a>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wider">Phone</label>
                            <p class="font-medium text-gray-800">
                                <?php if (!empty($inquiry->phone)): ?>
                                    <a href="tel:<?= $view->e($inquiry->phone) ?>" class="text-accent-orange hover:text-orange-600">
                                        <?= $view->e($inquiry->phone) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">Not provided</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wider mb-2 block">Message</label>
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-800 whitespace-pre-wrap">
                            <?= $view->e($inquiry->message ?? '') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Previous Reply -->
                <?php if ($inquiry->hasReply()): ?>
                    <div class="bg-green-50 rounded-lg shadow-sm p-6 border-l-4 border-green-400">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-green-600 text-lg">✓</span>
                            <h3 class="font-semibold text-green-800">Admin Reply</h3>
                            <span class="text-green-600 text-sm">
                                (<?= $view->date($inquiry->replied_at ?? '', 'M j, Y g:i A') ?>)
                            </span>
                        </div>
                        <div class="text-green-900 whitespace-pre-wrap">
                            <?= $view->e($inquiry->admin_reply ?? '') ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Reply Form -->
                <?php if (!$inquiry->isResolved()): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <?= $inquiry->hasReply() ? 'Send Another Reply' : 'Reply to Inquiry' ?>
                        </h3>
                        
                        <form action="/admin/contacts/<?= $inquiry->getKey() ?>/reply" method="POST" class="space-y-4">
                            <?= $view->csrf() ?>
                            
                            <div>
                                <textarea 
                                    name="message" 
                                    rows="6"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-orange focus:border-transparent <?= $view->hasError('message') ? 'border-red-500' : '' ?>"
                                    placeholder="Type your reply here..."
                                ></textarea>
                                <?php if ($view->hasError('message')): ?>
                                    <p class="mt-1 text-sm text-red-500"><?= $view->e($view->error('message')) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-end gap-4">
                                <button 
                                    type="submit"
                                    class="bg-accent-orange text-white py-2 px-6 rounded-lg font-semibold hover:bg-orange-600 transition"
                                >
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex gap-4">
                        <?php if (!$inquiry->isResolved()): ?>
                            <form action="/admin/contacts/<?= $inquiry->getKey() ?>/resolve" method="POST">
                                <?= $view->csrf() ?>
                                <button 
                                    type="submit"
                                    class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition"
                                >
                                    ✓ Mark as Resolved
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <form action="/admin/contacts/<?= $inquiry->getKey() ?>" method="POST">
                        <?= $view->csrf() ?>
                        <?= $view->method('DELETE') ?>
                        <button 
                            type="submit"
                            class="text-red-600 hover:text-red-800"
                            onclick="return confirm('Are you sure you want to delete this inquiry?')"
                        >
                            Delete Inquiry
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <p class="text-gray-600">Inquiry not found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $view->endSection(); ?>
