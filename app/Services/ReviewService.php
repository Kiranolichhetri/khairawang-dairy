<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Core\Application;
use Core\Database;

/**
 * Review Service
 * 
 * Handles product review operations.
 */
class ReviewService
{
    private ?Database $db = null;

    /**
     * Get database connection
     */
    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->db = $app->db();
            }
        }
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not available');
        }
        
        return $this->db;
    }

    /**
     * Get reviews for a product
     * 
     * @return array<string, mixed>
     */
    public function getProductReviews(int $productId, int $page = 1, int $perPage = 10): array
    {
        $reviews = Review::getReviewsWithUsers($productId, true);
        
        // Get review images
        $reviewIds = array_column($reviews, 'id');
        $images = [];
        
        if (!empty($reviewIds)) {
            $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
            $imageRows = $this->db()->select(
                "SELECT * FROM review_images WHERE review_id IN ({$placeholders})",
                $reviewIds
            );
            
            foreach ($imageRows as $img) {
                $images[$img['review_id']][] = '/uploads/reviews/' . $img['image_path'];
            }
        }
        
        // Format reviews
        $formattedReviews = array_map(function($review) use ($images) {
            return [
                'id' => $review['id'],
                'rating' => (int) $review['rating'],
                'title' => $review['title'],
                'comment' => $review['comment'],
                'user_name' => $review['user_name'] ?? 'Anonymous',
                'user_avatar' => $review['user_avatar'] 
                    ? '/uploads/avatars/' . $review['user_avatar'] 
                    : 'https://www.gravatar.com/avatar/?d=mp&s=40',
                'is_verified_purchase' => (bool) $review['is_verified_purchase'],
                'helpful_count' => (int) $review['helpful_count'],
                'images' => $images[$review['id']] ?? [],
                'created_at' => $review['created_at'],
            ];
        }, $reviews);
        
        // Paginate
        $total = count($formattedReviews);
        $offset = ($page - 1) * $perPage;
        $paginatedReviews = array_slice($formattedReviews, $offset, $perPage);
        
        return [
            'reviews' => $paginatedReviews,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
            'average_rating' => Review::getAverageRating($productId),
            'rating_breakdown' => Review::getRatingBreakdown($productId),
        ];
    }

    /**
     * Get user's reviews
     * 
     * @return array<array<string, mixed>>
     */
    public function getUserReviews(int $userId): array
    {
        $reviews = Review::forUser($userId);
        
        return array_map(function($review) {
            $product = Product::find($review->attributes['product_id']);
            $images = $review->images();
            
            $productImages = [];
            if ($product !== null) {
                $productImages = json_decode($product->attributes['images'] ?? '[]', true);
            }
            
            return [
                'id' => $review->getKey(),
                'rating' => (int) $review->attributes['rating'],
                'title' => $review->attributes['title'],
                'comment' => $review->attributes['comment'],
                'status' => $review->attributes['status'],
                'is_verified_purchase' => (bool) $review->attributes['is_verified_purchase'],
                'helpful_count' => (int) $review->attributes['helpful_count'],
                'images' => array_map(fn($img) => '/uploads/reviews/' . $img['image_path'], $images),
                'product' => $product ? [
                    'id' => $product->getKey(),
                    'name' => $product->attributes['name_en'],
                    'slug' => $product->attributes['slug'],
                    'image' => !empty($productImages) ? '/uploads/products/' . $productImages[0] : null,
                ] : null,
                'created_at' => $review->attributes['created_at'],
                'updated_at' => $review->attributes['updated_at'],
            ];
        }, $reviews);
    }

    /**
     * Create a new review
     * 
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, review?: Review}
     */
    public function createReview(int $userId, int $productId, array $data): array
    {
        // Check if product exists
        $product = Product::find($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check if user already reviewed this product
        if (Review::hasUserReviewed($userId, $productId)) {
            return ['success' => false, 'message' => 'You have already reviewed this product'];
        }
        
        // Validate rating
        $rating = (int) ($data['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }
        
        // Check for verified purchase
        $isVerifiedPurchase = Review::hasUserPurchased($userId, $productId);
        
        // Create review
        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_verified_purchase' => $isVerifiedPurchase,
            'status' => 'pending', // Require admin approval
            'helpful_count' => 0,
        ]);
        
        // Handle image uploads
        if (!empty($data['images']) && is_array($data['images'])) {
            $this->processReviewImages($review, $data['images']);
        }
        
        return [
            'success' => true,
            'message' => 'Review submitted successfully. It will be visible after approval.',
            'review' => $review,
        ];
    }

    /**
     * Update a review
     * 
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string}
     */
    public function updateReview(int $reviewId, int $userId, array $data): array
    {
        $review = Review::find($reviewId);
        
        if ($review === null) {
            return ['success' => false, 'message' => 'Review not found'];
        }
        
        // Check ownership
        if ($review->attributes['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'You can only edit your own reviews'];
        }
        
        // Validate rating if provided
        if (isset($data['rating'])) {
            $rating = (int) $data['rating'];
            if ($rating < 1 || $rating > 5) {
                return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
            }
            $review->rating = $rating;
        }
        
        if (isset($data['title'])) {
            $review->title = $data['title'];
        }
        
        if (isset($data['comment'])) {
            $review->comment = $data['comment'];
        }
        
        // Reset to pending for re-approval after edit
        $review->status = 'pending';
        $review->save();
        
        return [
            'success' => true,
            'message' => 'Review updated successfully. It will be reviewed again before appearing.',
        ];
    }

    /**
     * Delete a review
     * 
     * @return array{success: bool, message: string}
     */
    public function deleteReview(int $reviewId, int $userId): array
    {
        $review = Review::find($reviewId);
        
        if ($review === null) {
            return ['success' => false, 'message' => 'Review not found'];
        }
        
        // Check ownership
        if ($review->attributes['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'You can only delete your own reviews'];
        }
        
        // Delete images
        $images = $review->images();
        $uploadDir = Application::getInstance()?->basePath() . '/public/uploads/reviews';
        
        foreach ($images as $img) {
            $imagePath = $uploadDir . '/' . $img['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $review->delete();
        
        return ['success' => true, 'message' => 'Review deleted successfully'];
    }

    /**
     * Mark review as helpful
     * 
     * @return array{success: bool, message: string, helpful_count?: int}
     */
    public function markHelpful(int $reviewId, int $userId): array
    {
        $review = Review::find($reviewId);
        
        if ($review === null) {
            return ['success' => false, 'message' => 'Review not found'];
        }
        
        // Can't mark your own review as helpful
        if ($review->attributes['user_id'] === $userId) {
            return ['success' => false, 'message' => 'You cannot mark your own review as helpful'];
        }
        
        // Check if already marked
        if ($review->isMarkedHelpfulBy($userId)) {
            return ['success' => false, 'message' => 'You have already marked this review as helpful'];
        }
        
        $review->markHelpful($userId);
        
        return [
            'success' => true,
            'message' => 'Thank you for your feedback',
            'helpful_count' => $review->attributes['helpful_count'],
        ];
    }

    /**
     * Check if user can review a product
     * 
     * @return array{can_review: bool, reason?: string, has_purchased: bool}
     */
    public function canReviewProduct(int $userId, int $productId): array
    {
        // Check if already reviewed
        if (Review::hasUserReviewed($userId, $productId)) {
            return [
                'can_review' => false,
                'reason' => 'You have already reviewed this product',
                'has_purchased' => Review::hasUserPurchased($userId, $productId),
            ];
        }
        
        return [
            'can_review' => true,
            'has_purchased' => Review::hasUserPurchased($userId, $productId),
        ];
    }

    /**
     * Process and save review images
     * 
     * @param array<array<string, mixed>> $files
     */
    private function processReviewImages(Review $review, array $files): void
    {
        $uploadDir = Application::getInstance()?->basePath() . '/public/uploads/reviews';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxFiles = 5;
        $count = 0;
        
        foreach ($files as $file) {
            if ($count >= $maxFiles) {
                break;
            }
            
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                continue;
            }
            
            // Check file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                continue;
            }
            
            // Check file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                continue;
            }
            
            // Generate filename
            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $filename = 'review_' . $review->getKey() . '_' . ($count + 1) . '_' . time() . '.' . $extension;
            
            // Move file
            $destination = $uploadDir . '/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $review->addImage($filename);
                $count++;
            }
        }
    }
}
