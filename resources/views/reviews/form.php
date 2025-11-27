<?php
/**
 * Review Form Component
 * 
 * @var \Core\View $view
 * @var string $productSlug
 * @var bool $canReview
 * @var bool $hasPurchased
 */
?>

<?php if ($canReview ?? true): ?>
<div class="bg-white rounded-xl shadow-sm p-6" x-data="{ rating: 0, hoverRating: 0 }">
    <h3 class="text-lg font-semibold text-dark-brown mb-4">Write a Review</h3>
    
    <form action="/products/<?= $view->e($productSlug ?? '') ?>/reviews" method="POST" enctype="multipart/form-data">
        <?= $view->csrf() ?>
        
        <!-- Star Rating -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-dark-brown mb-2">Your Rating <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" 
                            @click="rating = <?= $i ?>" 
                            @mouseenter="hoverRating = <?= $i ?>" 
                            @mouseleave="hoverRating = 0"
                            class="text-3xl transition-colors focus:outline-none"
                            :class="(hoverRating >= <?= $i ?> || rating >= <?= $i ?>) ? 'text-yellow-400' : 'text-gray-300'">
                        ★
                    </button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" x-model="rating" required>
            <p class="text-xs text-gray-500 mt-1" x-show="rating === 0">Click to rate</p>
            <p class="text-sm text-gray-600 mt-1" x-show="rating > 0">
                <span x-show="rating === 1">Poor</span>
                <span x-show="rating === 2">Fair</span>
                <span x-show="rating === 3">Good</span>
                <span x-show="rating === 4">Very Good</span>
                <span x-show="rating === 5">Excellent</span>
            </p>
        </div>
        
        <!-- Review Title -->
        <div class="mb-4">
            <label for="review_title" class="block text-sm font-medium text-dark-brown mb-1">Review Title</label>
            <input type="text" 
                   id="review_title" 
                   name="title" 
                   placeholder="Sum up your experience"
                   maxlength="255"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
        </div>
        
        <!-- Review Content -->
        <div class="mb-4">
            <label for="review_comment" class="block text-sm font-medium text-dark-brown mb-1">Your Review</label>
            <textarea id="review_comment" 
                      name="comment" 
                      rows="4"
                      placeholder="Share your experience with this product..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent resize-none"></textarea>
        </div>
        
        <!-- Photo Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-dark-brown mb-1">Add Photos (Optional)</label>
            <input type="file" 
                   name="images[]" 
                   accept="image/*" 
                   multiple
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent-orange focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Max 5 images. JPG, PNG, or WebP. Max 5MB each.</p>
        </div>
        
        <!-- Verified Purchase Badge -->
        <?php if ($hasPurchased ?? false): ?>
            <div class="mb-4 flex items-center gap-2 text-green-600 text-sm">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>Your review will have a "Verified Purchase" badge</span>
            </div>
        <?php endif; ?>
        
        <button type="submit" 
                class="w-full px-6 py-3 bg-accent-orange text-white rounded-lg hover:bg-accent-orange-dark transition-colors"
                :disabled="rating === 0"
                :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : ''">
            Submit Review
        </button>
    </form>
</div>
<?php else: ?>
<div class="bg-light-gray rounded-xl p-6 text-center">
    <p class="text-gray-600">You have already reviewed this product.</p>
</div>
<?php endif; ?>
