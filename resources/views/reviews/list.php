<?php
/**
 * Reviews List Component
 * 
 * @var \Core\View $view
 * @var array $reviews
 * @var float $averageRating
 * @var array $ratingBreakdown
 * @var int $total
 */
?>

<div class="space-y-6">
    <!-- Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Average Rating -->
            <div class="text-center md:text-left">
                <div class="text-5xl font-bold text-dark-brown"><?= number_format($averageRating ?? 0, 1) ?></div>
                <div class="flex items-center justify-center md:justify-start gap-1 mt-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="text-xl <?= $i <= round($averageRating ?? 0) ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <p class="text-sm text-gray-500 mt-1">Based on <?= $total ?? 0 ?> reviews</p>
            </div>
            
            <!-- Rating Breakdown -->
            <div class="flex-1 space-y-2">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php 
                    $count = $ratingBreakdown[$star] ?? 0;
                    $totalReviews = $total ?? 1;
                    $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                    ?>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600 w-8"><?= $star ?> ★</span>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full" style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="text-sm text-gray-500 w-12 text-right"><?= $count ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <?php if (!empty($reviews)): ?>
        <div class="space-y-4">
            <?php foreach ($reviews as $review): ?>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <!-- Review Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <img src="<?= $view->e($review['user_avatar']) ?>" 
                                 alt="<?= $view->e($review['user_name']) ?>" 
                                 class="w-10 h-10 rounded-full object-cover">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-dark-brown"><?= $view->e($review['user_name']) ?></span>
                                    <?php if ($review['is_verified_purchase']): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Verified
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="text-sm <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <span class="text-sm text-gray-500"><?= $view->date($review['created_at'], 'M d, Y') ?></span>
                    </div>
                    
                    <!-- Review Content -->
                    <?php if (!empty($review['title'])): ?>
                        <h4 class="font-semibold text-dark-brown mb-2"><?= $view->e($review['title']) ?></h4>
                    <?php endif; ?>
                    
                    <?php if (!empty($review['comment'])): ?>
                        <p class="text-gray-600"><?= nl2br($view->e($review['comment'])) ?></p>
                    <?php endif; ?>
                    
                    <!-- Review Images -->
                    <?php if (!empty($review['images'])): ?>
                        <div class="flex gap-2 mt-4 flex-wrap">
                            <?php foreach ($review['images'] as $image): ?>
                                <a href="<?= $view->e($image) ?>" target="_blank" class="block w-20 h-20 rounded-lg overflow-hidden bg-light-gray">
                                    <img src="<?= $view->e($image) ?>" alt="Review image" class="w-full h-full object-cover">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Helpful Button -->
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                        <form action="/reviews/<?= $review['id'] ?>/helpful" method="POST" class="inline">
                            <?= $view->csrf() ?>
                            <button type="submit" class="flex items-center gap-2 text-sm text-gray-500 hover:text-accent-orange transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                </svg>
                                Helpful (<?= $review['helpful_count'] ?>)
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-light-gray rounded-xl p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </div>
            <h3 class="font-semibold text-dark-brown mb-2">No reviews yet</h3>
            <p class="text-gray-500">Be the first to review this product!</p>
        </div>
    <?php endif; ?>
</div>
