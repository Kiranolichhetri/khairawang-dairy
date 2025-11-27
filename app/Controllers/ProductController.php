<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Core\Request;
use Core\Response;

/**
 * Product Controller
 * 
 * Handles product listing, search, and detail pages.
 */
class ProductController
{
    /**
     * List all products with pagination, filters, search
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 12)));
        $search = trim($request->query('q', '') ?? '');
        $categorySlug = $request->query('category');
        $sortBy = $request->query('sort', 'newest');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        
        $query = Product::query()
            ->where('status', ProductStatus::PUBLISHED->value);
        
        // Search filter
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where('name_en', 'LIKE', $searchTerm);
        }
        
        // Category filter
        $category = null;
        if ($categorySlug) {
            $category = Category::findBySlug($categorySlug);
            if ($category) {
                $query->where('category_id', $category->getKey());
            }
        }
        
        // Price filter
        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('price', '>=', (float) $minPrice);
        }
        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('price', '<=', (float) $maxPrice);
        }
        
        // Sorting
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'ASC');
                break;
            case 'price_high':
                $query->orderBy('price', 'DESC');
                break;
            case 'name':
                $query->orderBy('name_en', 'ASC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'DESC');
                break;
        }
        
        // Get total count for pagination
        $total = $query->count();
        
        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $productsData = $query->limit($perPage)->offset($offset)->get();
        
        $products = [];
        foreach ($productsData as $row) {
            $product = Product::find($row['id']);
            if ($product !== null) {
                $products[] = $this->formatProduct($product);
            }
        }
        
        // Get categories for filter
        $categories = $this->getActiveCategories();
        
        return Response::json([
            'success' => true,
            'data' => [
                'products' => $products,
                'category' => $category ? [
                    'id' => $category->getKey(),
                    'name' => $category->getName(),
                    'slug' => $category->attributes['slug'],
                ] : null,
                'filters' => [
                    'search' => $search,
                    'category' => $categorySlug,
                    'sort' => $sortBy,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                ],
            ],
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Single product details
     */
    public function show(Request $request, string $slug): Response
    {
        $product = Product::findBySlug($slug);
        
        if ($product === null || !$product->isPublished()) {
            return Response::error('Product not found', 404);
        }
        
        $category = $product->category();
        $variants = $product->variants();
        
        // Get related products from same category
        $relatedProducts = [];
        if ($category) {
            $relatedData = Product::query()
                ->where('category_id', $category['id'])
                ->where('id', '!=', $product->getKey())
                ->where('status', ProductStatus::PUBLISHED->value)
                ->limit(4)
                ->get();
            
            foreach ($relatedData as $row) {
                $relatedProduct = Product::find($row['id']);
                if ($relatedProduct !== null) {
                    $relatedProducts[] = $this->formatProduct($relatedProduct);
                }
            }
        }
        
        return Response::json([
            'success' => true,
            'data' => [
                'product' => $this->formatProductDetail($product),
                'category' => $category ? [
                    'id' => $category['id'],
                    'name' => $category['name_en'],
                    'slug' => $category['slug'],
                ] : null,
                'variants' => $variants,
                'related_products' => $relatedProducts,
            ],
        ]);
    }

    /**
     * Products by category
     */
    public function category(Request $request, string $slug): Response
    {
        $category = Category::findBySlug($slug);
        
        if ($category === null || !$category->isActive()) {
            return Response::error('Category not found', 404);
        }
        
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 12)));
        
        $query = Product::query()
            ->where('category_id', $category->getKey())
            ->where('status', ProductStatus::PUBLISHED->value)
            ->orderBy('created_at', 'DESC');
        
        $total = $query->count();
        $offset = ($page - 1) * $perPage;
        $productsData = $query->limit($perPage)->offset($offset)->get();
        
        $products = [];
        foreach ($productsData as $row) {
            $product = Product::find($row['id']);
            if ($product !== null) {
                $products[] = $this->formatProduct($product);
            }
        }
        
        return Response::json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->getKey(),
                    'name' => $category->getName(),
                    'name_ne' => $category->getName('ne'),
                    'slug' => $category->attributes['slug'],
                    'description' => $category->attributes['description'],
                    'image' => $category->getImageUrl(),
                ],
                'products' => $products,
            ],
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Featured products
     */
    public function featured(Request $request): Response
    {
        $limit = min(20, max(1, (int) $request->query('limit', 8)));
        $products = Product::featured($limit);
        
        $formatted = array_map(function($product) {
            return $this->formatProduct($product);
        }, $products);
        
        return Response::json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): Response
    {
        $query = trim($request->query('q', '') ?? '');
        $limit = min(50, max(1, (int) $request->query('limit', 20)));
        
        if (empty($query)) {
            return Response::json([
                'success' => true,
                'data' => [],
                'query' => '',
            ]);
        }
        
        // Search in name and description
        $searchTerm = '%' . $query . '%';
        $results = Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->where('name_en', 'LIKE', $searchTerm)
            ->limit($limit)
            ->get();
        
        $products = [];
        foreach ($results as $row) {
            $product = Product::find($row['id']);
            if ($product !== null) {
                $products[] = $this->formatProduct($product);
            }
        }
        
        return Response::json([
            'success' => true,
            'data' => $products,
            'query' => $query,
            'count' => count($products),
        ]);
    }

    /**
     * Format product for listing
     * 
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->getKey(),
            'name' => $product->getName(),
            'name_ne' => $product->getName('ne'),
            'slug' => $product->attributes['slug'],
            'short_description' => $product->attributes['short_description'],
            'price' => (float) $product->attributes['price'],
            'sale_price' => $product->attributes['sale_price'] ? (float) $product->attributes['sale_price'] : null,
            'current_price' => $product->getCurrentPrice(),
            'is_on_sale' => $product->isOnSale(),
            'discount_percentage' => $product->getDiscountPercentage(),
            'stock' => $product->attributes['stock'],
            'in_stock' => $product->isInStock(),
            'stock_status' => $product->getStockStatus(),
            'featured' => (bool) $product->attributes['featured'],
            'image' => $product->getPrimaryImage(),
        ];
    }

    /**
     * Format product for detail view
     * 
     * @return array<string, mixed>
     */
    private function formatProductDetail(Product $product): array
    {
        return array_merge($this->formatProduct($product), [
            'description' => $product->getDescription(),
            'description_ne' => $product->getDescription('ne'),
            'sku' => $product->attributes['sku'],
            'weight' => $product->attributes['weight'],
            'images' => $product->getImageUrls(),
            'low_stock' => $product->isLowStock(),
            'seo_title' => $product->attributes['seo_title'],
            'seo_description' => $product->attributes['seo_description'],
        ]);
    }

    /**
     * Get active categories for filter
     * 
     * @return array<array<string, mixed>>
     */
    private function getActiveCategories(): array
    {
        $categories = Category::roots();
        
        return array_map(function($category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->getName(),
                'slug' => $category->attributes['slug'],
                'product_count' => $category->getProductCount(),
            ];
        }, $categories);
    }
}
