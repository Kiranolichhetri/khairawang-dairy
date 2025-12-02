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
                'sort' => ['created_at' => -1],
                'skip' => $offset,
                'limit' => $perPage,
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
                $products[] = $this->formatProductArray($row->toArray());
            }
        }

        $categories = Category::all();

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => ['products' => $products],
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
            'id' => (string) ($product['_id'] ?? $product['id'] ?? ''),
            'name' => $product['name_en'] ?? '',
            'name_ne' => $product['name_ne'] ?? '',
            'slug' => $product['slug'] ?? '',
            'short_description' => $product['short_description'] ?? '',
            'price' => (float) ($product['price'] ?? 0),
            'sale_price' => isset($product['sale_price']) ? (float) $product['sale_price'] : null,
            'stock' => (int) ($product['stock'] ?? 0),
            'low_stock_threshold' => (int) ($product['low_stock_threshold'] ?? 10),
            'featured' => (bool) ($product['featured'] ?? false),
            'status' => $product['status'] ?? 'draft',
            'category_id' => $product['category_id'] ?? null,
            'images' => $images,
            'image' => $firstImage,
            'created_at' => $product['created_at'] ?? null,
            'updated_at' => $product['updated_at'] ?? null,
        ];
    }

    public function create(Request $request): Response
    {
        $categories = Category::all();

        return Response::view('admin.products.create', [
            'title' => 'Add Product',
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'name_en' => 'required',
            'category_id' => 'required',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $app = Application::getInstance();
        $data = $request->all();
        $data['slug'] = strtolower(str_replace(' ', '-', $data['name_en']));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $inserted = $mongo->insert('products', $data);

            return Response::json([
                'success' => true,
                'id' => (string) $inserted,
            ]);
        }

        $product = Product::create($data);

        return Response::json([
            'success' => true,
            'id' => $product->id,
        ]);
    }

    public function edit(Request $request, string $id): Response
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $product = $mongo->findOne('products', ['_id' => $id]);
            if (!$product) {
                return Response::status(404);
            }
            $product = $this->formatProductArray($product);
        } else {
            $product = Product::find($id);
            if (!$product) {
                return Response::status(404);
            }
            $product = $this->formatProductArray($product->toArray());
        }

        $categories = Category::all();

        return Response::view('admin.products.edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $app = Application::getInstance();
        $data = $request->all();
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $mongo->update('products', ['_id' => $id], ['$set' => $data]);

            return Response::json(['success' => true]);
        }

        $product = Product::find($id);
        if (!$product) {
            return Response::status(404);
        }

        $product->update($data);

        return Response::json(['success' => true]);
    }

    public function delete(Request $request, string $id): Response
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $mongo->delete('products', ['_id' => $id]);
            return Response::json(['success' => true]);
        }

        $product = Product::find($id);
        if (!$product) {
            return Response::status(404);
        }

        $product->delete();

        return Response::json(['success' => true]);
    }

    public function toggleStatus(Request $request, string $id): Response
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $mongo = $app->mongo();
            $product = $mongo->findOne('products', ['_id' => $id]);
            if (!$product) {
                return Response::status(404);
            }

            $new = ($product['status'] === 'published') ? 'draft' : 'published';
            $mongo->update('products', ['_id' => $id], ['$set' => ['status' => $new]]);

            return Response::json(['success' => true, 'status' => $new]);
        }

        $product = Product::find($id);
        if (!$product) {
            return Response::status(404);
        }

        $product->status = ($product->status === 'published') ? 'draft' : 'published';
        $product->save();

        return Response::json(['success' => true, 'status' => $product->status]);
    }
}
