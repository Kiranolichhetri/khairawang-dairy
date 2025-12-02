<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use App\Enums\ProductStatus;

/**
 * Product Model
 * 
 * Represents a product in the e-commerce catalog.
 */
class Product extends Model
{
    protected static string $table = 'products';
    
    protected static bool $softDeletes = true;
    
    protected static array $fillable = [
        'category_id',
        'name_en',
        'name_ne',
        'slug',
        'description_en',
        'description_ne',
        'short_description',
        'price',
        'sale_price',
        'sku',
        'stock',
        'low_stock_threshold',
        'weight',
        'images',
        'featured',
        'status',
        'seo_title',
        'seo_description',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'price' => 'float',
        'sale_price' => 'float',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'weight' => 'float',
        'images' => 'json',
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get product status enum
     */
    public function getStatus(): ProductStatus
    {
        return ProductStatus::from($this->attributes['status'] ?? 'draft');
    }

    /**
     * Check if product is published
     */
    public function isPublished(): bool
    {
        return $this->getStatus()->isVisible();
    }

    /**
     * Get current price (sale price if available)
     */
    public function getCurrentPrice(): float
    {
        $salePrice = $this->attributes['sale_price'] ?? null;
        
        if ($salePrice !== null && $salePrice > 0) {
            return (float) $salePrice;
        }
        
        return (float) ($this->attributes['price'] ?? 0);
    }

    /**
     * Check if product is on sale
     */
    public function isOnSale(): bool
    {
        $salePrice = $this->attributes['sale_price'] ?? null;
        $price = $this->attributes['price'] ?? 0;
        
        return $salePrice !== null && $salePrice > 0 && $salePrice < $price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): int
    {
        if (!$this->isOnSale()) {
            return 0;
        }
        
        $price = (float) ($this->attributes['price'] ?? 0);
        $salePrice = (float) ($this->attributes['sale_price'] ?? 0);
        
        if ($price <= 0) {
            return 0;
        }
        
        return (int) round((($price - $salePrice) / $price) * 100);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return ($this->attributes['stock'] ?? 0) > 0;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        $stock = $this->attributes['stock'] ?? 0;
        $threshold = $this->attributes['low_stock_threshold'] ?? 10;
        
        return $stock > 0 && $stock <= $threshold;
    }

    /**
     * Get stock status label
     */
    public function getStockStatus(): string
    {
        if (!$this->isInStock()) {
            return 'Out of Stock';
        }
        
        if ($this->isLowStock()) {
            return 'Low Stock';
        }
        
        return 'In Stock';
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
     * Get localized description
     */
    public function getDescription(string $locale = 'en'): string
    {
        $key = 'description_' . $locale;
        return $this->attributes[$key] ?? $this->attributes['description_en'] ?? '';
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImage(): string
    {
        $images = $this->attributes['images'] ?? [];
        
        if (!empty($images) && is_array($images)) {
            $image = $images[0];
            // Check if already a full URL path
            if (str_starts_with($image, '/uploads/') || str_starts_with($image, 'http')) {
                return $image;
            }
            return '/uploads/products/' . $image;
        }
        
        return '/assets/images/product-placeholder.png';
    }

    /**
     * Get all image URLs
     * 
     * @return array<string>
     */
    public function getImageUrls(): array
    {
        $images = $this->attributes['images'] ?? [];
        
        if (empty($images) || !is_array($images)) {
            return ['/assets/images/product-placeholder.png'];
        }
        
        return array_map(function($img) {
            // Check if already a full URL path
            if (str_starts_with($img, '/uploads/') || str_starts_with($img, 'http')) {
                return $img;
            }
            return '/uploads/products/' . $img;
        }, $images);
    }

    /**
     * Get product category
     * 
     * @return array<string, mixed>|null
     */
    public function category(): ?array
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get product variants
     * 
     * @return array<int, array<string, mixed>>
     */
    public function variants(): array
    {
        $app = \Core\Application::getInstance();
        if ($app?->isMongoDbDefault()) {
            return $app->mongo()->find('product_variants', [
                'product_id' => (string) $this->getKey()
            ]);
        }
        
        return self::db()->table('product_variants')
            ->where('product_id', $this->getKey())
            ->get();
    }

    /**
     * Reduce stock
     */
    public function reduceStock(int $quantity): bool
    {
        $currentStock = $this->attributes['stock'] ?? 0;
        
        if ($currentStock < $quantity) {
            return false;
        }
        
        $this->stock = $currentStock - $quantity;
        return $this->save();
    }

    /**
     * Increase stock
     */
    public function increaseStock(int $quantity): bool
    {
        $this->stock = ($this->attributes['stock'] ?? 0) + $quantity;
        return $this->save();
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
     * Get featured products
     * 
     * @return array<self>
     */
    public static function featured(int $limit = 8): array
    {
        $rows = static::query()
            ->where('featured', 1)
            ->where('status', ProductStatus::PUBLISHED->value)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get products by category
     * 
     * @return array<self>
     */
    public static function byCategory(int $categoryId, int $limit = 12): array
    {
        $rows = static::query()
            ->where('category_id', $categoryId)
            ->where('status', ProductStatus::PUBLISHED->value)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Search products
     * 
     * @return array<self>
     */
    public static function search(string $term, int $limit = 20): array
    {
        $rows = static::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->search(['name_en', 'name_ne', 'description_en'], $term)
            ->orderBy('relevance_score', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get low stock products
     * 
     * @return array<self>
     */
    public static function lowStock(): array
    {
        $app = \Core\Application::getInstance();
        if ($app?->isMongoDbDefault()) {
            // Use MongoDB aggregation to compare fields
            $pipeline = [
                [
                    '$match' => [
                        'stock' => ['$gt' => 0],
                        'deleted_at' => null,
                        '$expr' => [
                            '$lte' => ['$stock', '$low_stock_threshold']
                        ]
                    ]
                ]
            ];
            $rows = $app->mongo()->aggregate(static::$table, $pipeline);
        } else {
            $rows = self::db()->table(static::$table)
                ->whereRaw('stock <= low_stock_threshold AND stock > 0')
                ->whereNull('deleted_at')
                ->get();
        }
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get out of stock products
     * 
     * @return array<self>
     */
    public static function outOfStock(): array
    {
        $rows = static::query()
            ->where('stock', 0)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }
}
