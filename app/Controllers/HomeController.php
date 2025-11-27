<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Core\Request;
use Core\Response;

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
        $products = Product::featured($limit);
        
        return array_map(function($product) {
            return [
                'id' => $product->getKey(),
                'name' => $product->getName(),
                'slug' => $product->attributes['slug'],
                'short_description' => $product->attributes['short_description'],
                'price' => (float) $product->attributes['price'],
                'sale_price' => $product->attributes['sale_price'] ? (float) $product->attributes['sale_price'] : null,
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
        $categories = Category::roots();
        
        return array_map(function($category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->getName(),
                'slug' => $category->attributes['slug'],
                'description' => $category->attributes['description'] ?? '',
                'image' => $category->getImageUrl(),
                'product_count' => $category->getProductCount(),
            ];
        }, $categories);
    }
}
