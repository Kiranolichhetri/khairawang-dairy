<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Blog Category Model
 * 
 * Represents a category for blog posts.
 */
class BlogCategory extends Model
{
    protected static string $table = 'blog_categories';
    
    protected static array $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if category is active
     */
    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? '') === 'active';
    }

    /**
     * Get posts in this category
     * 
     * @return array<BlogPost>
     */
    public function posts(): array
    {
        $rows = self::db()->table('blog_posts')
            ->where('category_id', $this->getKey())
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => BlogPost::hydrate($row), $rows);
    }

    /**
     * Get post count for this category
     */
    public function getPostCount(): int
    {
        return self::db()->table('blog_posts')
            ->where('category_id', $this->getKey())
            ->where('status', 'published')
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
     * Get all active categories
     * 
     * @return array<self>
     */
    public static function active(): array
    {
        $rows = static::query()
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
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
}
