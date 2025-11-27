<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Blog Tag Model
 * 
 * Represents a tag for blog posts.
 */
class BlogTag extends Model
{
    protected static string $table = 'blog_tags';
    
    protected static array $fillable = [
        'name',
        'slug',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get posts with this tag
     * 
     * @return array<int, array<string, mixed>>
     */
    public function posts(): array
    {
        return self::db()->table('blog_posts')
            ->join('blog_post_tags', 'blog_posts.id', '=', 'blog_post_tags.post_id')
            ->where('blog_post_tags.tag_id', $this->getKey())
            ->where('blog_posts.status', 'published')
            ->orderBy('blog_posts.published_at', 'DESC')
            ->get();
    }

    /**
     * Get post count for this tag
     */
    public function getPostCount(): int
    {
        return self::db()->table('blog_post_tags')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_tags.post_id')
            ->where('blog_post_tags.tag_id', $this->getKey())
            ->where('blog_posts.status', 'published')
            ->count();
    }

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get or create tag by name
     */
    public static function findOrCreateByName(string $name): self
    {
        $slug = static::generateSlug($name);
        
        $existing = static::findBySlug($slug);
        if ($existing !== null) {
            return $existing;
        }
        
        return static::create([
            'name' => trim($name),
            'slug' => $slug,
        ]);
    }

    /**
     * Generate unique slug
     */
    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
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

    /**
     * Get popular tags (by post count)
     * 
     * @return array<self>
     */
    public static function popular(int $limit = 10): array
    {
        $rows = self::db()->select(
            "SELECT t.*, COUNT(pt.post_id) as post_count 
             FROM blog_tags t 
             LEFT JOIN blog_post_tags pt ON t.id = pt.tag_id 
             LEFT JOIN blog_posts p ON pt.post_id = p.id AND p.status = 'published'
             GROUP BY t.id 
             HAVING post_count > 0
             ORDER BY post_count DESC 
             LIMIT ?",
            [$limit]
        );
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }
}
