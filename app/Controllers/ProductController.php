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
     * 
     * Renders the products listing HTML view.
     * Products are loaded from MongoDB directly (similar to HomeController).
     */
    public function index(Request $request): Response
    {
        $app = \Core\Application::getInstance();
        $products = [];
        
        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            
            // Get products from MongoDB
            $productsData = $mongo->find('products', [
                'status' => 'published',
                'deleted_at' => null,
            ], [
                'sort' => ['created_at' => -1],
            ]);
            
            foreach ($productsData as $product) {
                $products[] = $this->formatProductFromMongoRow($product);
            }
        }
        
        // Get categories for filter
        $categories = [];
        if ($app?->isMongoDbDefault()) {
            $categoriesData = $app->mongo()->find('categories', [], [
                'sort' => ['display_order' => 1],
            ]);
            foreach ($categoriesData as $cat) {
                $categories[] = [
                    'id' => (string) ($cat['_id'] ?? ''),
                    'name' => $cat['name_en'] ?? '',
                    'slug' => $cat['slug'] ?? '',
                ];
            }
        }
        
        return Response::view('products.index', [
            'title' => 'Our Products',
            'pageDescription' => 'Browse our collection of fresh dairy products from KHAIRAWANG DAIRY.',
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Format product from MongoDB document
     * 
     * @param array<string, mixed> $product
     * @return array<string, mixed>
     */
    private function formatProductFromMongoRow(array $product): array
    {
        $images = $product['images'] ?? [];
        $firstImage = '/assets/images/placeholder.png';
        if (!empty($images) && is_array($images)) {
            $img = $images[0];
            // Validate image path to prevent path traversal attacks
            if (is_string($img) && !str_contains($img, '..') && !str_contains($img, "\0")) {
                if (str_starts_with($img, '/uploads/') || str_starts_with($img, 'http')) {
                    $firstImage = $img;
                } else {
                    // Only allow alphanumeric, dash, underscore, dot and forward slash
                    $sanitizedImg = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', basename($img));
                    if (!empty($sanitizedImg)) {
                        $firstImage = '/uploads/products/' . $sanitizedImg;
                    }
                }
            }
        }
        
        $price = (float) ($product['price'] ?? 0);
        $salePrice = isset($product['sale_price']) && $product['sale_price'] > 0 ? (float) $product['sale_price'] : null;
        $currentPrice = $salePrice ?? $price;
        $isOnSale = $salePrice !== null && $salePrice < $price;
        $discountPercentage = $isOnSale && $price > 0 ? (int) round((($price - $salePrice) / $price) * 100) : 0;
        $stock = (int) ($product['stock'] ?? 0);
        
        // Stock status
        $stockStatus = 'In Stock';
        if ($stock <= 0) {
            $stockStatus = 'Out of Stock';
        } elseif ($stock <= 10) {
            $stockStatus = 'Low Stock';
        }
        
        return [
            'id' => (string) ($product['_id'] ?? $product['id'] ?? ''),
            'name' => $product['name_en'] ?? '',
            'name_ne' => $product['name_ne'] ?? '',
            'slug' => $product['slug'] ?? '',
            'short_description' => $product['short_description'] ?? '',
            'price' => $price,
            'sale_price' => $salePrice,
            'current_price' => $currentPrice,
            'is_on_sale' => $isOnSale,
            'discount_percentage' => $discountPercentage,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'stock_status' => $stockStatus,
            'featured' => (bool) ($product['featured'] ?? false),
            'image' => $firstImage,
        ];
    }

    /**
     * API endpoint: List all products with pagination, filters, search (JSON)
     */
    public function apiIndex(Request $request): Response
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
        
        // Clone query for count (to avoid modifying original)
        $countQuery = clone $query;
        $total = $countQuery->count();

        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $productsData = $query->select('*')->limit($perPage)->offset($offset)->get();

        $products = [];
        foreach ($productsData as $row) {
            $products[] = $this->formatProductFromRow($row);
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
                    'slug' => $category->attributes['slug'] ?? '',
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
     * 
     * Renders the product detail HTML view.
     * The view uses Alpine.js which fetches data from /api/v1/products/{slug}.
     */
    public function show(Request $request, string $slug): Response
    {
        // Verify product exists before rendering view
        $product = Product::findBySlug($slug);
        
        if ($product === null || !$product->isPublished()) {
            return Response::error('Product not found', 404);
        }
        
        return Response::view('products.show', [
            'title' => $product->getName(),
            'pageDescription' => $product->attributes['short_description'] ?? 'View product details at KHAIRAWANG DAIRY.',
            'product' => $this->formatProductDetail($product),
        ]);
    }

    /**
     * API endpoint: Single product details (JSON)
     */
    public function apiShow(Request $request, string $slug): Response
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
                $relatedProducts[] = $this->formatProductFromRow($row);
            }
        }
        
        return Response::json([
            'success' => true,
            'data' => [
                'product' => $this->formatProductDetail($product),
                'category' => $category ? [
                    'id' => $category['id'] ?? null,
                    'name' => $category['name_en'] ?? '',
                    'slug' => $category['slug'] ?? '',
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
        
        // Clone query for count
        $countQuery = clone $query;
        $total = $countQuery->count();
        
        $offset = ($page - 1) * $perPage;
        $productsData = $query->select('*')->limit($perPage)->offset($offset)->get();
        
        $products = [];
        foreach ($productsData as $row) {
            $products[] = $this->formatProductFromRow($row);
        }
        
        return Response::json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->getKey(),
                    'name' => $category->getName(),
                    'name_ne' => $category->getName('ne'),
                    'slug' => $category->attributes['slug'] ?? '',
                    'description' => $category->attributes['description'] ?? '',
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
        $searchTerm = '%' . $query .  '%';
        $results = Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->where('name_en', 'LIKE', $searchTerm)
            ->select('*')
            ->limit($limit)
            ->get();
        
        $products = [];
        foreach ($results as $row) {
            $products[] = $this->formatProductFromRow($row);
        }
        
        return Response::json([
            'success' => true,
            'data' => $products,
            'query' => $query,
            'count' => count($products),
        ]);
    }

    /**
     * Format product from database row array
     * 
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatProductFromRow(array $row): array
    {
        $price = (float) ($row['price'] ??  0);
        $salePrice = ! empty($row['sale_price']) ? (float) $row['sale_price'] : null;
        $currentPrice = ($salePrice !== null && $salePrice > 0) ? $salePrice : $price;
        $isOnSale = $salePrice !== null && $salePrice > 0 && $salePrice < $price;
        $discountPercentage = $isOnSale && $price > 0 ? (int) round((($price - $salePrice) / $price) * 100) : 0;
        $stock = (int) ($row['stock'] ?? 0);
        
        // Stock status
        $stockStatus = 'In Stock';
        if ($stock <= 0) {
            $stockStatus = 'Out of Stock';
        } elseif ($stock <= 10) {
            $stockStatus = 'Low Stock';
        }
        
        return [
            'id' => (int) ($row['id'] ??  0),
            'name' => $row['name_en'] ?? '',
            'name_ne' => $row['name_ne'] ??  $row['name_en'] ?? '',
            'slug' => $row['slug'] ?? '',
            'short_description' => $row['short_description'] ?? '',
            'price' => $price,
            'sale_price' => $salePrice,
            'current_price' => $currentPrice,
            'is_on_sale' => $isOnSale,
            'discount_percentage' => $discountPercentage,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'stock_status' => $stockStatus,
            'featured' => (bool) ($row['featured'] ??  false),
            'image' => '/assets/images/product-placeholder.png',
        ];
    }

    /**
     * Format product for listing (from Product model)
     * 
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product): array
    {
        $attrs = $product->toArray();
        return $this->formatProductFromRow($attrs);
    }

    /**
     * Format product for detail view
     * 
     * @return array<string, mixed>
     */
    private function formatProductDetail(Product $product): array
    {
        $attrs = $product->toArray();
        
        return array_merge($this->formatProductFromRow($attrs), [
            'description' => $attrs['description_en'] ?? '',
            'description_ne' => $attrs['description_ne'] ?? '',
            'sku' => $attrs['sku'] ?? '',
            'weight' => $attrs['weight'] ?? null,
            'images' => ['/assets/images/product-placeholder.png'],
            'low_stock' => ($attrs['stock'] ?? 0) > 0 && ($attrs['stock'] ??  0) <= 10,
            'seo_title' => $attrs['seo_title'] ??  '',
            'seo_description' => $attrs['seo_description'] ?? '',
        ]);
    }

    /**
     * Get active categories for filter
     * 
     * @return array<array<string, mixed>>
     */
    private function getActiveCategories(): array
    {
        try {
            $categories = Category::roots();
            
            return array_map(function($category) {
                return [
                    'id' => $category->getKey(),
                    'name' => $category->getName(),
                    'slug' => $category->attributes['slug'] ?? '',
                    'product_count' => $category->getProductCount(),
                ];
            }, $categories);
        } catch (\Exception $e) {
            return [];
        }
    }
}