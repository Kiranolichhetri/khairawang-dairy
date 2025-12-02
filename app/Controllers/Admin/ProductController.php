<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

class ProductController
{
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $search = trim($request->query('q', '') ?? '');
        $status = $request->query('status');
        $categoryId = $request->query('category_id');

        $app = Application::getInstance();
        $products = [];
        $total = 0;

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $filter = [];

            if (!empty($search)) {
                $filter['name_en'] = ['$regex' => $search, '$options' => 'i'];
            }

            if ($status && in_array($status, ['draft', 'published', 'archived'])) {
                $filter['status'] = $status;
            }

            if ($categoryId) {
                $filter['category_id'] = $categoryId;
            }

            $total = $mongo->count('products', $filter);
            $offset = ($page - 1) * $perPage;

            $productsData = $mongo->find('products', $filter, [
                'sort'   => ['created_at' => -1],
                'skip'   => $offset,
                'limit'  => $perPage,
            ]);

            foreach ($productsData as $row) {
                $products[] = $this->formatProductArray($row);
            }
        } else {
            $query = Product::withTrashed();

            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where('name_en', 'LIKE', $searchTerm);
            }

            if ($status && in_array($status, ['draft', 'published', 'archived'])) {
                $query->where('status', $status);
            }

            if ($categoryId) {
                $query->where('category_id', (int) $categoryId);
            }

            $query->orderBy('created_at', 'DESC');
            $total = $query->count();

            $offset = ($page - 1) * $perPage;
            $productsData = $query->limit($perPage)->offset($offset)->get();

            foreach ($productsData as $row) {
                $products[] = $this->formatProductArray(is_array($row) ? $row : $row->toArray());
            }
        }

        $categories = Category::all();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data'    => ['products' => $products],
                'meta'    => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => (int) ceil($total / $perPage),
                ],
            ]);
        }

        return Response::view('admin.products.index', [
            'title'      => 'Products',
            'products'   => $products,
            'categories' => $categories,
            'filters'    => [
                'search'      => $search,
                'status'      => $status,
                'category_id' => $categoryId,
            ],
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    private function formatProductArray(array $product): array
    {
        $images = $product['images'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        $firstImage = '/assets/images/placeholder.png';

        if (!empty($images) && is_array($images)) {
            $img = $images[0];
            if (str_starts_with($img, '/uploads/') || str_starts_with($img, 'http')) {
                $firstImage = $img;
            } else {
                $firstImage = '/uploads/products/' . $img;
            }
        }

        return [
            'id'                  => (string) ($product['_id'] ?? $product['id'] ?? ''),
            'name'                => $product['name_en'] ?? '',
            'name_ne'             => $product['name_ne'] ?? '',
            'slug'                => $product['slug'] ?? '',
            'short_description'   => $product['short_description'] ?? '',
            'price'               => (float) ($product['price'] ?? 0),
            'sale_price'          => isset($product['sale_price']) ? (float) $product['sale_price'] : null,
            'stock'               => (int) ($product['stock'] ?? 0),
            'low_stock_threshold' => (int) ($product['low_stock_threshold'] ?? 10),
            'featured'            => (bool) ($product['featured'] ?? false),
            'status'              => $product['status'] ?? 'draft',
            'category_id'         => $product['category_id'] ?? null,
            'images'              => $images,
            'image'               => $firstImage,
            'created_at'          => $product['created_at'] ?? null,
            'updated_at'          => $product['updated_at'] ?? null,
        ];
    }

    public function store(Request $request): Response
{
    $app = Application::getInstance();
    $session = $app? ->session();
    
    // Debug: Log incoming data
    error_log("=== PRODUCT STORE ===");
    error_log("Request data: " . json_encode($request->all()));
    
    // Create validator instance
    $validator = new Validator($request->all(), [
        'name_en' => 'required|min:2|max:255',
        'slug' => 'required|min:2|max:255',
        'price' => 'required|numeric',
        'category_id' => 'required',
        'status' => 'required',
    ]);

    if ($validator->fails()) {
        error_log("Validation failed: " . json_encode($validator->errors()));
        $session? ->flashErrors($validator->errors());
        $session? ->flashInput($request->all());
        return Response::redirect('/admin/products/create');
    }

    error_log("Validation passed!");

    // Process images from hidden input
    $images = [];
    $imagesInput = $request->input('images');
    if (!empty($imagesInput)) {
        if (is_string($imagesInput)) {
            $decoded = json_decode($imagesInput, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        } elseif (is_array($imagesInput)) {
            $images = $imagesInput;
        }
    }

    // Build product data
    $data = [
        'name_en' => $request->input('name_en'),
        'name_ne' => $request->input('name_ne') ??  '',
        'slug' => $request->input('slug'),
        'category_id' => $request->input('category_id'),
        'short_description' => $request->input('short_description') ?? '',
        'description_en' => $request->input('description_en') ??  '',
        'description_ne' => $request->input('description_ne') ?? '',
        'price' => (float) $request->input('price'),
        'sale_price' => $request->input('sale_price') ?  (float) $request->input('sale_price') : null,
        'sku' => $request->input('sku') ?? '',
        'stock' => (int) $request->input('stock', 0),
        'low_stock_threshold' => (int) $request->input('low_stock_threshold', 10),
        'weight' => $request->input('weight') ? (float) $request->input('weight') : null,
        'images' => $images,
        'featured' => (bool) $request->input('featured', false),
        'status' => $request->input('status'),
        'seo_title' => $request->input('seo_title') ?? '',
        'seo_description' => $request->input('seo_description') ?? '',
        'deleted_at' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    error_log("Product data: " . json_encode($data));

    try {
        if ($app?->isMongoDbDefault()) {
            error_log("Using MongoDB.. .");
            $mongo = $app->mongo();
            $insertedId = $mongo->insertOne('products', $data);
            error_log("Product inserted with ID: " .  $insertedId);
        } else {
            error_log("Using MySQL...");
            Product::create($data);
        }

        $session?->success('Product created successfully! ');
        error_log("SUCCESS - Redirecting to products list");
        return Response::redirect('/admin/products');
        
    } catch (\Exception $e) {
        error_log("ERROR: " . $e->getMessage());
        $session?->error('Failed to create product: ' . $e->getMessage());
        return Response::redirect('/admin/products/create');
    }
}

    public function edit(Request $request, string $id): Response
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $product = $mongo->findOne('products', ['_id' => new \MongoDB\BSON\ObjectId($id)]);
            if (!$product) {
                return Response::redirect('/admin/products');
            }
            $product = $this->formatProductArray((array) $product);
        } else {
            $product = Product::find($id);
            if (!$product) {
                return Response::redirect('/admin/products');
            }
            $product = $this->formatProductArray($product->toArray());
        }

        $categories = Category::all();

        return Response::view('admin.products.edit', [
            'title'      => 'Edit Product',
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $app = Application::getInstance();
        $session = $app?->session();

        $images = [];
        $imagesInput = $request->input('images');
        if (!empty($imagesInput)) {
            if (is_string($imagesInput)) {
                $decoded = json_decode($imagesInput, true);
                if (is_array($decoded)) {
                    $images = $decoded;
                }
            } elseif (is_array($imagesInput)) {
                $images = $imagesInput;
            }
        }

        $data = [
            'name_en'             => $request->input('name_en'),
            'name_ne'             => $request->input('name_ne') ?? '',
            'slug'                => $request->input('slug'),
            'category_id'         => $request->input('category_id'),
            'short_description'   => $request->input('short_description') ?? '',
            'description_en'      => $request->input('description_en') ?? '',
            'price'               => (float) $request->input('price'),
            'sale_price'          => $request->input('sale_price') ? (float) $request->input('sale_price') : null,
            'sku'                 => $request->input('sku') ?? '',
            'stock'               => (int) $request->input('stock', 0),
            'low_stock_threshold' => (int) $request->input('low_stock_threshold', 10),
            'weight'              => $request->input('weight') ? (float) $request->input('weight') : null,
            'featured'            => (bool) $request->input('featured', false),
            'status'              => $request->input('status'),
            'seo_title'           => $request->input('seo_title') ?? '',
            'seo_description'     => $request->input('seo_description') ?? '',
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if (!empty($images)) {
            $data['images'] = $images;
        }

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $mongo->updateOne('products', ['_id' => new \MongoDB\BSON\ObjectId($id)], $data);
        } else {
            $product = Product::find($id);
            if ($product) {
                $product->update($data);
            }
        }

        $session?->success('Product updated successfully!');
        return Response::redirect('/admin/products');
    }

    public function delete(Request $request, string $id): Response
    {
        $app = Application::getInstance();
        $session = $app?->session();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $mongo->deleteOne('products', ['_id' => new \MongoDB\BSON\ObjectId($id)]);
        } else {
            $product = Product::find($id);
            if ($product) {
                $product->delete();
            }
        }

        $session?->success('Product deleted successfully!');

        if ($request->expectsJson()) {
            return Response::json(['success' => true]);
        }

        return Response::redirect('/admin/products');
    }

    public function toggleStatus(Request $request, string $id): Response
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $product = $mongo->findOne('products', ['_id' => new \MongoDB\BSON\ObjectId($id)]);
            if (!$product) {
                return Response::json(['success' => false], 404);
            }

            $newStatus = ($product['status'] === 'published') ? 'draft' : 'published';
            $mongo->updateOne('products', ['_id' => new \MongoDB\BSON\ObjectId($id)], ['status' => $newStatus]);

            return Response::json(['success' => true, 'status' => $newStatus]);
        }

        $product = Product::find($id);
        if (!$product) {
            return Response::json(['success' => false], 404);
        }

        $product->status = ($product->status === 'published') ? 'draft' : 'published';
        $product->save();

        return Response::json(['success' => true, 'status' => $product->status]);
    }

    public function uploadImage(Request $request): Response
    {
        if (!isset($_FILES['image'])) {
            return Response::json(['success' => false, 'message' => 'No image uploaded'], 400);
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return Response::json(['success' => false, 'message' => 'Upload failed: ' . $file['error']], 400);
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return Response::json(['success' => false, 'message' => 'File too large. Max 5MB'], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return Response::json(['success' => false, 'message' => 'Invalid file type'], 400);
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = $extensions[$mimeType] ?? 'jpg';

        $filename = 'product_' . bin2hex(random_bytes(16)) . '.' . $ext;

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/products';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return Response::json(['success' => false, 'message' => 'Failed to save file'], 500);
        }

        return Response::json([
            'success' => true,
            'url'     => '/uploads/products/' . $filename,
        ]);
    }
}
