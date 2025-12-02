<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Product Controller
 * 
 * Handles product management in the admin panel.
 */
class ProductController
{
    /**
     * List all products with pagination and search
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $search = trim($request->query('q', '') ?? '');
        $status = $request->query('status');
        $categoryId = $request->query('category_id');
        
        $query = Product::withTrashed();
        
        // Search filter
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where('name_en', 'LIKE', $searchTerm);
        }
        
        // Status filter
        if ($status && in_array($status, ['draft', 'published', 'archived'])) {
            $query->where('status', $status);
        }
        
        // Category filter
        if ($categoryId) {
            $query->where('category_id', (int) $categoryId);
        }
        
        // Order by latest
        $query->orderBy('created_at', 'DESC');
        
        // Get total count
        $total = $query->count();
        
        // Pagination
        $offset = ($page - 1) * $perPage;
        $productsData = $query->limit($perPage)->offset($offset)->get();
        
        $products = [];
        foreach ($productsData as $row) {
            $product = Product::withTrashed()->find($row['id'], 'id');
            if ($product !== null) {
                $products[] = $this->formatProduct($product);
            }
        }
        
        // Get categories for filter
        $categories = Category::all();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
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
        
        return Response::view('admin.products.index', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category_id' => $categoryId,
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Show create product form
     */
    public function create(Request $request): Response
    {
        $categories = Category::all();
        
        return Response::view('admin.products.create', [
            'title' => 'Create Product',
            'categories' => $categories,
            'statuses' => ProductStatus::cases(),
        ]);
    }

    /**
     * Store new product
     */
    public function store(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'name_en' => 'required|min:2|max:255',
            'slug' => 'required|min:2|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|numeric',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/products/create');
        }

        // Check if slug is unique
        $existingProduct = Product::findBy('slug', $request->input('slug'));
        if ($existingProduct !== null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['slug' => ['This slug is already in use.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/products/create');
        }

        // Process images
        $images = $this->processImages($request);

        // Handle category_id for MongoDB (string) vs MySQL (int)
        $categoryId = $request->input('category_id');
        $app = Application::getInstance();
        if (!$app?->isMongoDbDefault()) {
            $categoryId = (int) $categoryId;
        }

        // Create product
        $product = Product::create([
            'category_id' => $categoryId,
            'name_en' => $request->input('name_en'),
            'name_ne' => $request->input('name_ne'),
            'slug' => $request->input('slug'),
            'description_en' => $request->input('description_en'),
            'description_ne' => $request->input('description_ne'),
            'short_description' => $request->input('short_description'),
            'price' => (float) $request->input('price'),
            'sale_price' => $request->input('sale_price') ? (float) $request->input('sale_price') : null,
            'sku' => $request->input('sku'),
            'stock' => (int) $request->input('stock', 0),
            'low_stock_threshold' => (int) $request->input('low_stock_threshold', 10),
            'weight' => $request->input('weight') ? (float) $request->input('weight') : null,
            'images' => $images,
            'featured' => (bool) $request->input('featured', false),
            'status' => $request->input('status'),
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
        ]);

        $session = $app?->session();
        $session?->success('Product created successfully!');

        return Response::redirect('/admin/products');
    }

    /**
     * Show edit product form
     */
    public function edit(Request $request, string $id): Response
    {
        $product = Product::withTrashed()->find($id, 'id');
        
        if ($product === null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Product not found.');
            
            return Response::redirect('/admin/products');
        }
        
        $categories = Category::all();
        
        return Response::view('admin.products.edit', [
            'title' => 'Edit Product',
            'product' => $product->toArray(),
            'categories' => $categories,
            'statuses' => ProductStatus::cases(),
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, string $id): Response
    {
        $product = Product::withTrashed()->find($id, 'id');
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Product not found.');
            
            return Response::redirect('/admin/products');
        }
        
        $validator = new Validator($request->all(), [
            'name_en' => 'required|min:2|max:255',
            'slug' => 'required|min:2|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|numeric',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/products/' . $id . '/edit');
        }

        // Check if slug is unique (excluding current product)
        $existingProduct = Product::findBy('slug', $request->input('slug'));
        if ($existingProduct !== null && (string) $existingProduct->getKey() !== (string) $id) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['slug' => ['This slug is already in use.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/products/' . $id . '/edit');
        }

        // Process images
        $images = $this->processImages($request);
        
        // Keep existing images if no new ones uploaded
        if (empty($images) && isset($product->attributes['images'])) {
            $existingImages = $product->attributes['images'];
            if (is_string($existingImages)) {
                $images = json_decode($existingImages, true) ?? [];
            } else {
                $images = $existingImages;
            }
        }

        // Handle category_id for MongoDB (string) vs MySQL (int)
        $categoryId = $request->input('category_id');
        $app = Application::getInstance();
        if (!$app?->isMongoDbDefault()) {
            $categoryId = (int) $categoryId;
        }

        // Update product
        $product->fill([
            'category_id' => $categoryId,
            'name_en' => $request->input('name_en'),
            'name_ne' => $request->input('name_ne'),
            'slug' => $request->input('slug'),
            'description_en' => $request->input('description_en'),
            'description_ne' => $request->input('description_ne'),
            'short_description' => $request->input('short_description'),
            'price' => (float) $request->input('price'),
            'sale_price' => $request->input('sale_price') ? (float) $request->input('sale_price') : null,
            'sku' => $request->input('sku'),
            'stock' => (int) $request->input('stock', 0),
            'low_stock_threshold' => (int) $request->input('low_stock_threshold', 10),
            'weight' => $request->input('weight') ? (float) $request->input('weight') : null,
            'images' => $images,
            'featured' => (bool) $request->input('featured', false),
            'status' => $request->input('status'),
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
        ]);
        $product->save();

        $session = $app?->session();
        $session?->success('Product updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Product updated successfully',
            ]);
        }

        return Response::redirect('/admin/products');
    }

    /**
     * Delete product
     */
    public function delete(Request $request, string $id): Response
    {
        $product = Product::find($id);
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Product not found.');
            
            return Response::redirect('/admin/products');
        }
        
        $product->delete();

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Product deleted successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        }

        return Response::redirect('/admin/products');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Request $request, string $id): Response
    {
        $product = Product::find($id);
        
        if ($product === null) {
            if ($request->expectsJson()) {
                return Response::error('Product not found', 404);
            }
            
            return Response::redirect('/admin/products');
        }
        
        $currentStatus = $product->attributes['status'];
        $newStatus = $currentStatus === ProductStatus::PUBLISHED->value 
            ? ProductStatus::DRAFT->value 
            : ProductStatus::PUBLISHED->value;
        
        $product->status = $newStatus;
        $product->save();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Product status updated',
                'status' => $newStatus,
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Product status updated!');

        return Response::redirect('/admin/products');
    }

    /**
     * Handle image upload
     */
    public function uploadImage(Request $request): Response
    {
        if (!isset($_FILES['image'])) {
            return Response::error('No image uploaded', 400);
        }
        
        $file = $_FILES['image'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return Response::error('Upload failed with error code: ' . $file['error'], 400);
        }
        
        // Validate file size first (before any processing)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return Response::error('File too large. Maximum size: 5MB', 400);
        }
        
        // Validate MIME type using finfo (more secure than trusting $_FILES['type'])
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp',
        ];
        
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($detectedMimeType, $allowedMimeTypes, true)) {
            return Response::error('Invalid file type. Allowed: JPEG, PNG, GIF, WebP', 400);
        }
        
        // Validate that it's actually a valid image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return Response::error('Invalid image file', 400);
        }
        
        // Map MIME type to extension
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        
        // Use the detected MIME type's extension (not the user-provided one)
        $extension = $mimeToExt[$detectedMimeType] ?? 'jpg';
        
        // Generate unique filename with random bytes for security
        $filename = 'product_' . bin2hex(random_bytes(16)) . '.' . $extension;
        
        // Ensure upload directory exists
        $uploadDir = Application::getInstance()?->basePath('public/uploads/products') ?? 'public/uploads/products';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return Response::error('Failed to upload image', 500);
        }
        
        return Response::json([
            'success' => true,
            'url' => '/uploads/products/' . $filename,
        ]);
    }

    /**
     * Process images from request
     * 
     * @return array<string>
     */
    private function processImages(Request $request): array
    {
        $images = [];
        
        // Get images from request (could be JSON array or uploaded files)
        $imageInput = $request->input('images');
        
        if (is_string($imageInput)) {
            $decoded = json_decode($imageInput, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        } elseif (is_array($imageInput)) {
            $images = $imageInput;
        }
        
        return $images;
    }

    /**
     * Format product for API response
     * 
     * @return array<string, mixed>
     */
    private function formatProduct($product): array
    {
        $images = $product->attributes['images'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }
        
        return [
            'id' => $product->getKey(),
            'name' => $product->attributes['name_en'] ?? '',
            'name_ne' => $product->attributes['name_ne'] ?? '',
            'slug' => $product->attributes['slug'] ?? '',
            'short_description' => $product->attributes['short_description'] ?? '',
            'price' => (float) ($product->attributes['price'] ?? 0),
            'sale_price' => $product->attributes['sale_price'] ? (float) $product->attributes['sale_price'] : null,
            'stock' => (int) ($product->attributes['stock'] ?? 0),
            'low_stock_threshold' => (int) ($product->attributes['low_stock_threshold'] ?? 10),
            'featured' => (bool) ($product->attributes['featured'] ?? false),
            'status' => $product->attributes['status'] ?? 'draft',
            'category_id' => $product->attributes['category_id'] ?? null,
            'images' => $images,
            'image' => !empty($images) ? $images[0] : '/assets/images/placeholder.png',
            'created_at' => $product->attributes['created_at'] ?? null,
            'updated_at' => $product->attributes['updated_at'] ?? null,
        ];
    }
}
