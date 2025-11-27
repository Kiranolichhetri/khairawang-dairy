<?php
/**
 * Breadcrumbs Partial
 * 
 * @var array $breadcrumbs Array of breadcrumb items with 'name' and 'url'
 */
$breadcrumbs = $breadcrumbs ?? [];
?>

<?php if (!empty($breadcrumbs)): ?>
<nav aria-label="Breadcrumb" class="py-3">
    <ol class="flex items-center flex-wrap gap-2 text-sm">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <li class="flex items-center">
                <?php if ($i > 0): ?>
                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                <?php endif; ?>
                
                <?php if ($i === count($breadcrumbs) - 1): ?>
                    <span class="text-gray-600" aria-current="page"><?= htmlspecialchars($crumb['name']) ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($crumb['url']) ?>" class="text-accent-orange hover:underline">
                        <?= htmlspecialchars($crumb['name']) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
