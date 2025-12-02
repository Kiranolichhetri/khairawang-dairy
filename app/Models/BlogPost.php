<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Blog Post Model
 * 
 * Represents a blog post/article.
 */
class BlogPost extends Model
{
    protected static string $table = 'blog_posts';
    
    protected static array $fillable = [
        'author_id',
        'category_id',
        'title_en',
        'title_ne',
        'slug',
        'excerpt',
        'content_en',
        'content_ne',
        'featured_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views_count',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'author_id' => 'integer',
        'category_id' => 'integer',
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Post status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Pivot table for post tags
     */
    protected const PIVOT_TABLE_TAGS = 'blog_post_tags';

    /**
     * Get current datetime formatted for queries
     */
    private static function getCurrentDateTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_PUBLISHED;
    }

    /**
     * Check if post is draft
     */
    public function isDraft(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_DRAFT;
    }

    /**
     * Get localized title
     */
    public function getTitle(string $locale = 'en'): string
    {
        $key = 'title_' . $locale;
        return $this->attributes[$key] ?? $this->attributes['title_en'] ?? '';
    }

    /**
     * Get localized content
     */
    public function getContent(string $locale = 'en'): string
    {
        $key = 'content_' . $locale;
        return $this->attributes[$key] ?? $this->attributes['content_en'] ?? '';
    }

    /**
     * Get excerpt (auto-generated if not set)
     */
    public function getExcerpt(int $length = 200): string
    {
        $excerpt = $this->attributes['excerpt'] ?? '';
        
        if (empty($excerpt)) {
            $content = strip_tags($this->getContent());
            $excerpt = substr($content, 0, $length);
            
            if (strlen($content) > $length) {
                $excerpt .= '...';
            }
        }
        
        return $excerpt;
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrl(): string
    {
        $image = $this->attributes['featured_image'] ?? '';
        
        if (!empty($image)) {
            return '/uploads/blog/' . $image;
        }
        
        return '/assets/images/blog-placeholder.png';
    }

    /**
     * Get author
     * 
     * @return array<string, mixed>|null
     */
    public function author(): ?array
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get category
     * 
     * @return array<string, mixed>|null
     */
    public function category(): ?array
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Get tags for this post
     * 
     * @return array<int, array<string, mixed>>
     */
    public function tags(): array
    {
        return self::db()->table('blog_tags')
            ->join('blog_post_tags', 'blog_tags.id', '=', 'blog_post_tags.tag_id')
            ->where('blog_post_tags.post_id', $this->getKey())
            ->get();
    }

    /**
     * Sync tags for this post
     * 
     * @param array<int> $tagIds
     */
    public function syncTags(array $tagIds): void
    {
        // Remove existing tags
        self::db()->delete('blog_post_tags', ['post_id' => $this->getKey()]);
        
        // Add new tags
        foreach ($tagIds as $tagId) {
            self::db()->insert('blog_post_tags', [
                'post_id' => $this->getKey(),
                'tag_id' => $tagId,
            ]);
        }
    }

    /**
     * Add tag to post
     */
    public function addTag(int $tagId): void
    {
        $exists = self::db()->table('blog_post_tags')
            ->where('post_id', $this->getKey())
            ->where('tag_id', $tagId)
            ->exists();
        
        if (!$exists) {
            self::db()->insert('blog_post_tags', [
                'post_id' => $this->getKey(),
                'tag_id' => $tagId,
            ]);
        }
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->views_count = ($this->attributes['views_count'] ?? 0) + 1;
        $this->save();
    }

    /**
     * Get SEO meta title (falls back to post title)
     */
    public function getMetaTitle(): string
    {
        return $this->attributes['meta_title'] ?? $this->getTitle();
    }

    /**
     * Get SEO meta description (falls back to excerpt)
     */
    public function getMetaDescription(): string
    {
        return $this->attributes['meta_description'] ?? $this->getExcerpt(160);
    }

    /**
     * Publish the post
     */
    public function publish(): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Unpublish the post
     */
    public function unpublish(): bool
    {
        $this->status = self::STATUS_DRAFT;
        return $this->save();
    }

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get published posts
     * 
     * @return array<self>
     */
    public static function published(int $limit = 10, int $offset = 0): array
    {
        if (static::isMongoDb()) {
            $now = self::getCurrentDateTime();
            $rows = static::query()
                ->where('status', self::STATUS_PUBLISHED)
                ->where('published_at', '<=', $now)
                ->orderBy('published_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();
        } else {
            $rows = static::query()
                ->where('status', self::STATUS_PUBLISHED)
                ->whereRaw('published_at <= NOW()')
                ->orderBy('published_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();
        }
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get published posts count
     */
    public static function publishedCount(): int
    {
        if (static::isMongoDb()) {
            $now = self::getCurrentDateTime();
            return static::query()
                ->where('status', self::STATUS_PUBLISHED)
                ->where('published_at', '<=', $now)
                ->count();
        }
        
        return static::query()
            ->where('status', self::STATUS_PUBLISHED)
            ->whereRaw('published_at <= NOW()')
            ->count();
    }

    /**
     * Get posts by category
     * 
     * @return array<self>
     */
    public static function byCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        if (static::isMongoDb()) {
            $now = self::getCurrentDateTime();
            $rows = static::query()
                ->where('category_id', $categoryId)
                ->where('status', self::STATUS_PUBLISHED)
                ->where('published_at', '<=', $now)
                ->orderBy('published_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();
        } else {
            $rows = static::query()
                ->where('category_id', $categoryId)
                ->where('status', self::STATUS_PUBLISHED)
                ->whereRaw('published_at <= NOW()')
                ->orderBy('published_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();
        }
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get posts by tag
     * 
     * @return array<self>
     */
    public static function byTag(int $tagId, int $limit = 10, int $offset = 0): array
    {
        if (static::isMongoDb()) {
            $mongo = static::mongo();
            $now = self::getCurrentDateTime();
            
            // Get post IDs from pivot collection
            $pivotDocs = $mongo->find(self::PIVOT_TABLE_TAGS, ['tag_id' => $tagId]);
            $postIds = array_column($pivotDocs, 'post_id');
            
            if (empty($postIds)) {
                return [];
            }
            
            $rows = static::query()
                ->whereIn('_id', $postIds)
                ->where('status', self::STATUS_PUBLISHED)
                ->where('published_at', '<=', $now)
                ->orderBy('published_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();
            
            return array_map(fn($row) => static::hydrate($row), $rows);
        }
        
        $rows = self::db()->select(
            "SELECT p.* FROM blog_posts p 
             INNER JOIN blog_post_tags pt ON p.id = pt.post_id 
             WHERE pt.tag_id = ? AND p.status = 'published' AND p.published_at <= NOW()
             ORDER BY p.published_at DESC 
             LIMIT ? OFFSET ?",
            [$tagId, $limit, $offset]
        );
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Search posts
     * 
     * @return array<self>
     */
    public static function search(string $query, int $limit = 10): array
    {
        if (static::isMongoDb()) {
            $now = self::getCurrentDateTime();
            $escapedQuery = preg_quote($query, '/');
            
            $mongo = static::mongo();
            $rows = $mongo->find(static::getTable(), [
                'status' => self::STATUS_PUBLISHED,
                'published_at' => ['$lte' => $now],
                '$or' => [
                    ['title_en' => ['$regex' => $escapedQuery, '$options' => 'i']],
                    ['title_ne' => ['$regex' => $escapedQuery, '$options' => 'i']],
                    ['content_en' => ['$regex' => $escapedQuery, '$options' => 'i']],
                ],
            ], [
                'sort' => ['published_at' => -1],
                'limit' => $limit,
            ]);
            
            return array_map(fn($row) => static::hydrate($row), $rows);
        }
        
        // Escape special characters for LIKE queries
        $escapedQuery = addcslashes($query, '%_');
        $searchTerm = '%' . $escapedQuery . '%';
        
        // Use parameterized query to prevent SQL injection
        $rows = self::db()->select(
            "SELECT * FROM blog_posts 
             WHERE status = ? 
             AND published_at <= NOW()
             AND (title_en LIKE ? OR title_ne LIKE ? OR content_en LIKE ?)
             ORDER BY published_at DESC 
             LIMIT ?",
            [self::STATUS_PUBLISHED, $searchTerm, $searchTerm, $searchTerm, $limit]
        );
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get related posts
     * 
     * @return array<self>
     */
    public function getRelatedPosts(int $limit = 4): array
    {
        $categoryId = $this->attributes['category_id'] ?? null;
        
        if ($categoryId === null) {
            return [];
        }
        
        $rows = static::query()
            ->where('category_id', $categoryId)
            ->where('id', '!=', $this->getKey())
            ->where('status', self::STATUS_PUBLISHED)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get recent posts
     * 
     * @return array<self>
     */
    public static function recent(int $limit = 5): array
    {
        return static::published($limit);
    }

    /**
     * Get popular posts (by views)
     * 
     * @return array<self>
     */
    public static function popular(int $limit = 5): array
    {
        $rows = static::query()
            ->where('status', self::STATUS_PUBLISHED)
            ->orderBy('views_count', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Generate unique slug
     */
    public static function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $query = static::query()->where('slug', $slug);
            
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
