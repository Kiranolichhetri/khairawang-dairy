<?php
/**
 * Notifications List Page
 * KHAIRAWANG DAIRY
 */
$view->extends('account');
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
?>

<?php $view->section('content'); ?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-dark-brown">Notifications</h1>
            <?php if ($unreadCount > 0): ?>
                <p class="text-gray-600"><?= $unreadCount ?> unread notification<?= $unreadCount > 1 ? 's' : '' ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($unreadCount > 0): ?>
            <form action="/account/notifications/read-all" method="POST">
                <?= $view->csrf() ?>
                <button 
                    type="submit"
                    class="text-accent-orange hover:text-orange-600 font-medium text-sm"
                >
                    Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if ($view->flash('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            <?= $view->e($view->flash('success')) ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($notifications)): ?>
        <div class="bg-white rounded-xl shadow-soft p-12 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                <span class="text-4xl">🔔</span>
            </div>
            <h2 class="text-xl font-semibold text-dark-brown mb-2">No Notifications</h2>
            <p class="text-gray-600">You're all caught up! Check back later for updates.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-soft divide-y divide-gray-100">
            <?php foreach ($notifications as $notification): ?>
                <div class="p-6 <?= !$notification->isRead() ? 'bg-blue-50/50' : '' ?> hover:bg-gray-50 transition">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-accent-orange/10 rounded-full flex items-center justify-center text-2xl">
                            <?= $notification->getIcon() ?>
                        </div>
                        
                        <div class="flex-grow min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-dark-brown <?= !$notification->isRead() ? 'font-bold' : '' ?>">
                                        <?= $view->e($notification->title ?? '') ?>
                                    </h3>
                                    <p class="text-gray-600 mt-1">
                                        <?= $view->e($notification->message ?? '') ?>
                                    </p>
                                    <p class="text-sm text-gray-400 mt-2">
                                        <?= $notification->getTimeAgo() ?>
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <?php if (!$notification->isRead()): ?>
                                        <form action="/account/notifications/<?= $notification->getKey() ?>/read" method="POST">
                                            <?= $view->csrf() ?>
                                            <button 
                                                type="submit"
                                                class="text-xs text-accent-orange hover:text-orange-600"
                                                title="Mark as read"
                                            >
                                                ✓
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="/account/notifications/<?= $notification->getKey() ?>" method="POST">
                                        <?= $view->csrf() ?>
                                        <?= $view->method('DELETE') ?>
                                        <button 
                                            type="submit"
                                            class="text-xs text-gray-400 hover:text-red-500"
                                            title="Delete"
                                            onclick="return confirm('Delete this notification?')"
                                        >
                                            ×
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="mt-8 text-center">
        <a 
            href="/account/notifications/preferences" 
            class="text-accent-orange hover:text-orange-600 font-medium"
        >
            Manage notification preferences →
        </a>
    </div>
</div>

<?php $view->endSection(); ?>
