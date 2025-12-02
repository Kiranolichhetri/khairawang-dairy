<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Category Model
 * 
 * Represents a product category with support for hierarchical structure.
 */
class Category extends Model
{
    protected static string $table = 'categories';
    
    protected static array $fillable = [
        'parent_id',
        'name_en',
        'name_ne',
        'slug',
        'description',
        'image',
        'display_order',
        'status',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'display_order' => 'integer',
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
     * Get localized name
     */
    public function getName(string $locale = 'en'): string
    {
        $key = 'name_' . $locale;
        return $this->attributes[$key] ?? $this->attributes['name_en'] ?? '';
    }

    /**
     * Get image URL
     */
    public function getImageUrl(): string
    {
        $image = $this->attributes['image'] ?? null;
        
        if ($image) {
            return '/uploads/categories/' . $image;
        }
        
        return '/assets/images/category-placeholder.png';
    }

    /**
     * Get parent category
     * 
     * @return array<string, mixed>|null
     */
    public function parent(): ?array
    {
        $parentId = $this->attributes['parent_id'] ?? null;
        
        if ($parentId === null) {
            return null;
        }
        
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child categories
     * 
     * @return array<int, array<string, mixed>>
     */
    public function children(): array
    {
        return static::query()
            ->where('parent_id', $this->getKey())
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Check if category has children
     */
    public function hasChildren(): bool
    {
        return static::query()
            ->where('parent_id', $this->getKey())
            ->exists();
    }

    /**
     * Check if category is a root category (no parent)
     */
    public function isRoot(): bool
    {
        return $this->attributes['parent_id'] === null;
    }

    /**
     * Get products in this category
     * 
     * @return array<int, array<string, mixed>>
     */
    public function products(): array
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get product count
     */
    public function getProductCount(): int
    {
        $app = \Core\Application::getInstance();
        if ($app?->isMongoDbDefault()) {
            return $app->mongo()->count('products', [
                'category_id' => (string) $this->getKey(),
                'status' => 'published',
                'deleted_at' => null
            ]);
        }
        
        return self::db()->table('products')
            ->where('category_id', $this->getKey())
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get ancestors (breadcrumb path)
     * 
     * @return array<self>
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $current = $this;
        
        while ($current->attributes['parent_id'] !== null) {
            $parent = static::find($current->attributes['parent_id']);
            
            if ($parent === null) {
                break;
            }
            
            array_unshift($ancestors, $parent);
            $current = $parent;
        }
        
        return $ancestors;
    }

    /**
     * Get breadcrumb path including self
     * 
     * @return array<self>
     */
    public function getBreadcrumb(): array
    {
        $breadcrumb = $this->getAncestors();
        $breadcrumb[] = $this;
        
        return $breadcrumb;
    }

    /**
     * Get all descendants
     * 
     * @return array<self>
     */
    public function getDescendants(): array
    {
        $descendants = [];
        $children = $this->children();
        
        foreach ($children as $childData) {
            $child = static::hydrate($childData);
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }
        
        return $descendants;
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
     * Find by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get root categories (no parent)
     * 
     * @return array<self>
     */
    public static function roots(): array
    {
        $rows = static::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get all categories as a tree structure
     * 
     * @return array<array<string, mixed>>
     */
    public static function tree(): array
    {
        $categories = static::query()
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();
        
        return self::buildTree($categories);
    }

    /**
     * Build tree from flat array
     * 
     * @param array<array<string, mixed>> $categories
     * @return array<array<string, mixed>>
     */
    private static function buildTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] === $parentId) {
                $children = self::buildTree($categories, $category['id']);
                
                if (!empty($children)) {
                    $category['children'] = $children;
                }
                
                $tree[] = $category;
            }
        }
        
        return $tree;
    }

    /**
     * Get categories for select dropdown (flat with indentation)
     * 
     * @return array<int, string>
     */
    public static function selectOptions(?int $excludeId = null, string $prefix = ''): array
    {
        $options = [];
        $roots = static::roots();
        
        foreach ($roots as $root) {
            if ($excludeId !== null && $root->getKey() === $excludeId) {
                continue;
            }
            
            $options[$root->getKey()] = $prefix . $root->getName();
            self::addChildOptions($options, $root, $excludeId, $prefix . '-- ');
        }
        
        return $options;
    }

    /**
     * Add child options recursively
     * 
     * @param array<int, string> $options
     */
    private static function addChildOptions(array &$options, self $parent, ?int $excludeId, string $prefix): void
    {
        $children = $parent->children();
        
        foreach ($children as $childData) {
            $child = static::hydrate($childData);
            
            if ($excludeId !== null && $child->getKey() === $excludeId) {
                continue;
            }
            
            $options[$child->getKey()] = $prefix . $child->getName();
            self::addChildOptions($options, $child, $excludeId, $prefix . '-- ');
        }
    }
}
