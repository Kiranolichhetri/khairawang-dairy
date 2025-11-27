<?php
/**
 * Admin Newsletter Campaigns Page
 * KHAIRAWANG DAIRY
 */
$view->extends('admin');
$campaigns = $campaigns ?? [];
?>

<?php $view->section('content'); ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Newsletter Campaigns</h1>
            <p class="text-gray-600">Manage your email campaigns</p>
        </div>
        
        <div class="flex gap-4">
            <a 
                href="/admin/newsletter"
                class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition"
            >
                Subscribers
            </a>
            <a 
                href="/admin/newsletter/campaigns/create"
                class="bg-accent-orange text-white py-2 px-4 rounded-lg hover:bg-orange-600 transition"
            >
                + New Campaign
            </a>
        </div>
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
    
    <!-- Campaigns List -->
    <div class="bg-white rounded-lg shadow-sm">
        <?php if (empty($campaigns)): ?>
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <span class="text-3xl">📧</span>
                </div>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">No Campaigns Yet</h2>
                <p class="text-gray-600 mb-4">Create your first newsletter campaign to engage your subscribers.</p>
                <a 
                    href="/admin/newsletter/campaigns/create"
                    class="inline-block bg-accent-orange text-white py-2 px-6 rounded-lg hover:bg-orange-600 transition"
                >
                    Create Campaign
                </a>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($campaigns as $campaign): ?>
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-grow">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <?= $view->e($campaign->subject ?? '') ?>
                                </h3>
                                <p class="text-gray-600 mt-1 line-clamp-2">
                                    <?= $view->truncate(strip_tags($campaign->content ?? ''), 150) ?>
                                </p>
                                
                                <div class="flex items-center gap-6 mt-4 text-sm">
                                    <div>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                            echo match($campaign->status ?? 'draft') {
                                                'draft' => 'bg-gray-100 text-gray-700',
                                                'scheduled' => 'bg-blue-100 text-blue-700',
                                                'sending' => 'bg-yellow-100 text-yellow-700',
                                                'sent' => 'bg-green-100 text-green-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        ?>">
                                            <?= ucfirst($campaign->status ?? 'draft') ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (($campaign->status ?? '') === 'sent'): ?>
                                        <div class="text-gray-500">
                                            <span class="font-medium"><?= $campaign->sent_count ?? 0 ?></span> sent
                                        </div>
                                        <div class="text-gray-500">
                                            <span class="font-medium"><?= $campaign->getOpenRate() ?>%</span> opened
                                        </div>
                                        <div class="text-gray-500">
                                            <span class="font-medium"><?= $campaign->getClickRate() ?>%</span> clicked
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-gray-400">
                                        Created <?= $view->date($campaign->created_at ?? '', 'M j, Y') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 ml-4">
                                <?php if (($campaign->status ?? '') === 'draft'): ?>
                                    <form action="/admin/newsletter/campaigns/<?= $campaign->getKey() ?>/send" method="POST" class="inline">
                                        <?= $view->csrf() ?>
                                        <button 
                                            type="submit"
                                            class="bg-green-500 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-600 transition"
                                            onclick="return confirm('Send this campaign to all subscribers?')"
                                        >
                                            Send Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="/admin/newsletter/campaigns/<?= $campaign->getKey() ?>" method="POST" class="inline">
                                    <?= $view->csrf() ?>
                                    <?= $view->method('DELETE') ?>
                                    <button 
                                        type="submit"
                                        class="text-red-600 hover:text-red-900 p-2"
                                        onclick="return confirm('Delete this campaign?')"
                                        title="Delete"
                                    >
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $view->endSection(); ?>
