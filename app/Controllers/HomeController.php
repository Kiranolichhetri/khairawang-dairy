<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Home Controller
 * 
 * Handles the main home page display with featured products,
 * categories, and promotional content. 
 */
class HomeController
{
    /**
     * Display the home page
     */
    public function index(Request $request): Response
    {
        // Get featured products
        $featuredProducts = $this->getFeaturedProducts(8);
        
        // Get active categories
        $categories = $this->getCategories();
        
        return Response::view('home.index', [
            'title' => 'Fresh From Farm To Table',
            'pageDescription' => 'KHAIRAWANG DAIRY - Premium fresh dairy products delivered from our farm to your table. Explore our collection of milk, cheese, yogurt, and more.',
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
        ]);
    }

    /**
     * Get featured products for display
     * 
     * @return array<array<string, mixed>>
     */
    private function getFeaturedProducts(int $limit = 8): array
    {
        $app = Application::getInstance();
        
        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            
            // First try to get featured products
            $products = $mongo->find('products', [
                'featured' => true,
                'status' => 'published',
                'deleted_at' => null,
            ], [
                'limit' => $limit,
                'sort' => ['created_at' => -1],
            ]);
            
            // If no featured products found, fall back to all published products
            if (empty($products)) {
                $products = $mongo->find('products', [
                    'status' => 'published',
                    'deleted_at' => null,
                ], [
                    'limit' => $limit,
                    'sort' => ['created_at' => -1],
                ]);
            }
            
            // If still no products, show any non-deleted products
            if (empty($products)) {
                $products = $mongo->find('products', [
                    'deleted_at' => null,
                ], [
                    'limit' => $limit,
                    'sort' => ['created_at' => -1],
                ]);
            }
            
            return array_map(function($product) {
                $images = $product['images'] ?? [];
                $firstImage = '/assets/images/placeholder.png';
                if (!empty($images) && is_array($images)) {
                    $img = $images[0];
                    if (str_starts_with($img, '/uploads/') || str_starts_with($img, 'http')) {
                        $firstImage = $img;
                    } else {
                        $firstImage = '/uploads/products/' . $img;
                    }
                }
                
                $price = (float) ($product['price'] ?? 0);
                $salePrice = isset($product['sale_price']) && $product['sale_price'] > 0 ? (float) $product['sale_price'] : null;
                $currentPrice = $salePrice ?? $price;
                $isOnSale = $salePrice !== null && $salePrice < $price;
                $discountPercentage = $isOnSale && $price > 0 ? (int) round((($price - $salePrice) / $price) * 100) : 0;
                
                return [
                    'id' => (string) ($product['_id'] ?? $product['id'] ?? ''),
                    'name' => $product['name_en'] ?? '',
                    'slug' => $product['slug'] ?? '',
                    'short_description' => $product['short_description'] ?? '',
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'current_price' => $currentPrice,
                    'is_on_sale' => $isOnSale,
                    'discount_percentage' => $discountPercentage,
                    'in_stock' => ($product['stock'] ?? 0) > 0,
                    'image' => $firstImage,
                    'category' => null,
                ];
            }, $products);
        }
        
        // MySQL fallback
        $products = Product::featured($limit);
        
        return array_map(function($product) {
            return [
                'id' => $product->getKey(),
                'name' => $product->getName(),
                'slug' => $product->attributes['slug'] ?? '',
                'short_description' => $product->attributes['short_description'] ?? '',
                'price' => (float) ($product->attributes['price'] ?? 0),
                'sale_price' => isset($product->attributes['sale_price']) ? (float) $product->attributes['sale_price'] : null,
                'current_price' => $product->getCurrentPrice(),
                'is_on_sale' => $product->isOnSale(),
                'discount_percentage' => $product->getDiscountPercentage(),
                'in_stock' => $product->isInStock(),
                'image' => $product->getPrimaryImage(),
                'category' => $product->category()['name_en'] ?? null,
            ];
        }, $products);
    }

    /**
     * Get active categories for display
     * 
     * @return array<array<string, mixed>>
     */
    private function getCategories(): array
    {
        $app = Application::getInstance();
        
        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            
            // First try to get categories with status: active
            $categories = $mongo->find('categories', [
                'status' => 'active',
            ], [
                'sort' => ['display_order' => 1],
            ]);
            
            // If no categories found with status filter, get all categories
            // (handles cases where status field may not be set)
            if (empty($categories)) {
                $categories = $mongo->find('categories', [], [
                    'sort' => ['display_order' => 1],
                ]);
            }
            
            return array_map(function($category) use ($app) {
                $image = $category['image'] ?? null;
                $imageUrl = $image ? '/uploads/categories/' . $image : '/assets/images/category-placeholder.png';
                
                // Count products in this category
                $productCount = $app->mongo()->count('products', [
                    'category_id' => (string) ($category['_id'] ?? ''),
                    'status' => 'published',
                    'deleted_at' => null,
                ]);
                
                return [
                    'id' => (string) ($category['_id'] ?? $category['id'] ?? ''),
                    'name' => $category['name_en'] ?? '',
                    'slug' => $category['slug'] ?? '',
                    'description' => $category['description'] ?? '',
                    'image' => $imageUrl,
                    'product_count' => $productCount,
                ];
            }, $categories);
        }
        
        // MySQL fallback
        $categories = Category::roots();
        
        return array_map(function($category) {
            if ($category === null) {
                return null;
            }
            return [
                'id' => $category->getKey(),
                'name' => $category->getName(),
                'slug' => $category->attributes['slug'] ?? '',
                'description' => $category->attributes['description'] ?? '',
                'image' => $category->getImageUrl(),
                'product_count' => $category->getProductCount(),
            ];
        }, array_filter($categories));
    }
}
