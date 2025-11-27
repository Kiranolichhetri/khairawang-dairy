<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Review Model
 * 
 * Represents a product review with ratings and optional images.
 */
class Review extends Model
{
    protected static string $table = 'reviews';
    
    protected static array $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'status',
        'helpful_count',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'order_id' => 'integer',
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get reviews for a product
     * 
     * @return array<self>
     */
    public static function forProduct(int $productId, bool $approvedOnly = true): array
    {
        $query = static::query()
            ->where('product_id', $productId);
        
        if ($approvedOnly) {
            $query->where('status', 'approved');
        }
        
        $rows = $query->orderBy('created_at', 'DESC')->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get reviews with user details for a product
     * 
     * @return array<array<string, mixed>>
     */
    public static function getReviewsWithUsers(int $productId, bool $approvedOnly = true): array
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?";
        
        $params = [$productId];
        
        if ($approvedOnly) {
            $sql .= " AND r.status = 'approved'";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        return self::db()->select($sql, $params);
    }

    /**
     * Get user's reviews
     * 
     * @return array<self>
     */
    public static function forUser(int $userId): array
    {
        $rows = static::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Check if user has reviewed a product
     */
    public static function hasUserReviewed(int $userId, int $productId): bool
    {
        $data = static::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        
        return $data !== null;
    }

    /**
     * Check if user has purchased the product (for verified purchase badge)
     */
    public static function hasUserPurchased(int $userId, int $productId): bool
    {
        $result = self::db()->selectOne(
            "SELECT COUNT(*) as count FROM order_items oi
             INNER JOIN orders o ON oi.order_id = o.id
             WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'",
            [$userId, $productId]
        );
        
        return ((int) ($result['count'] ?? 0)) > 0;
    }

    /**
     * Get average rating for a product
     */
    public static function getAverageRating(int $productId): float
    {
        $result = self::db()->selectOne(
            "SELECT AVG(rating) as avg_rating FROM reviews 
             WHERE product_id = ? AND status = 'approved'",
            [$productId]
        );
        
        return round((float) ($result['avg_rating'] ?? 0), 1);
    }

    /**
     * Get rating breakdown for a product
     * @return array<int, int>
     */
    public static function getRatingBreakdown(int $productId): array
    {
        $results = self::db()->select(
            "SELECT rating, COUNT(*) as count FROM reviews 
             WHERE product_id = ? AND status = 'approved'
             GROUP BY rating ORDER BY rating DESC",
            [$productId]
        );
        
        $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        
        foreach ($results as $row) {
            $breakdown[(int) $row['rating']] = (int) $row['count'];
        }
        
        return $breakdown;
    }

    /**
     * Get review count for a product
     */
    public static function getReviewCount(int $productId): int
    {
        return static::query()
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(int $userId): bool
    {
        // Check if user already marked this review as helpful
        $existing = self::db()->selectOne(
            "SELECT id FROM review_helpfuls WHERE review_id = ? AND user_id = ?",
            [$this->getKey(), $userId]
        );
        
        if ($existing !== null) {
            return false; // Already marked
        }
        
        // Add helpful record
        self::db()->insert('review_helpfuls', [
            'review_id' => $this->getKey(),
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        // Update helpful count
        $this->helpful_count = ($this->attributes['helpful_count'] ?? 0) + 1;
        return $this->save();
    }

    /**
     * Check if user marked review as helpful
     */
    public function isMarkedHelpfulBy(int $userId): bool
    {
        $existing = self::db()->selectOne(
            "SELECT id FROM review_helpfuls WHERE review_id = ? AND user_id = ?",
            [$this->getKey(), $userId]
        );
        
        return $existing !== null;
    }

    /**
     * Get review images
     * 
     * @return array<array<string, mixed>>
     */
    public function images(): array
    {
        return self::db()->table('review_images')
            ->where('review_id', $this->getKey())
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    /**
     * Add image to review
     */
    public function addImage(string $imagePath): int
    {
        return self::db()->insert('review_images', [
            'review_id' => $this->getKey(),
            'image_path' => $imagePath,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get star rating as HTML
     */
    public function getStarsHtml(): string
    {
        $rating = (int) ($this->attributes['rating'] ?? 0);
        $stars = '';
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars .= '<span class="text-yellow-400">★</span>';
            } else {
                $stars .= '<span class="text-gray-300">★</span>';
            }
        }
        
        return $stars;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        $status = $this->attributes['status'] ?? 'pending';
        
        return match($status) {
            'approved' => '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>',
            'rejected' => '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Rejected</span>',
            default => '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>',
        };
    }
}
