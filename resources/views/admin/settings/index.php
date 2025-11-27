<?php
/**
 * Admin Settings
 * 
 * @var \Core\View $view
 * @var string $title
 * @var array $settings
 */
$view->extends('admin');
?>

<?php $view->section('content'); ?>
<div class="max-w-4xl space-y-6">
    <form action="/admin/settings" method="POST" class="space-y-6">
        <?= $view->csrf() ?>
        
        <?php foreach ($settings as $group => $groupSettings): ?>
            <div class="bg-white rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-dark-brown capitalize"><?= $view->e(str_replace('_', ' ', $group)) ?> Settings</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php foreach ($groupSettings as $setting): ?>
                        <div>
                            <label for="<?= $view->e($setting['key']) ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                <?= $view->e(ucwords(str_replace('_', ' ', $setting['key']))) ?>
                            </label>
                            
                            <?php if ($setting['type'] === 'boolean'): ?>
                                <div class="flex items-center">
                                    <input type="hidden" name="settings[<?= $view->e($setting['key']) ?>]" value="0">
                                    <input type="checkbox" 
                                           id="<?= $view->e($setting['key']) ?>" 
                                           name="settings[<?= $view->e($setting['key']) ?>]" 
                                           value="1"
                                           <?= $setting['value'] ? 'checked' : '' ?>
                                           class="h-4 w-4 text-accent-orange focus:ring-accent-orange border-gray-300 rounded">
                                    <label for="<?= $view->e($setting['key']) ?>" class="ml-2 text-sm text-gray-600">
                                        Enable
                                    </label>
                                </div>
                            <?php elseif ($setting['type'] === 'integer'): ?>
                                <input type="number" 
                                       id="<?= $view->e($setting['key']) ?>" 
                                       name="settings[<?= $view->e($setting['key']) ?>]" 
                                       value="<?= $view->e($setting['value']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                            <?php elseif ($setting['type'] === 'json'): ?>
                                <textarea id="<?= $view->e($setting['key']) ?>" 
                                          name="settings[<?= $view->e($setting['key']) ?>]" 
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange font-mono text-sm"><?= $view->e(json_encode($setting['value'], JSON_PRETTY_PRINT)) ?></textarea>
                            <?php else: ?>
                                <input type="text" 
                                       id="<?= $view->e($setting['key']) ?>" 
                                       name="settings[<?= $view->e($setting['key']) ?>]" 
                                       value="<?= $view->e($setting['value']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($settings)): ?>
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark-brown">No settings found</h3>
                <p class="text-gray-500 mt-1">Settings will be available once configured.</p>
            </div>
        <?php else: ?>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-accent-orange text-white font-medium rounded-lg hover:bg-accent-orange-dark">
                    Save Settings
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>
<?php $view->endSection(); ?>
