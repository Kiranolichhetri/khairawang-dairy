<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\BlogService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Blog Controller
 * 
 * Handles blog post management in the admin panel.
 */
class BlogController
{
    private BlogService $blogService;

    public function __construct()
    {
        $this->blogService = new BlogService();
    }

    /**
     * List all posts
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $status = $request->query('status');
        
        $result = $this->blogService->getAllPosts($page, $perPage, $status);
        
        $formattedPosts = [];
        foreach ($result['posts'] as $post) {
            $formattedPosts[] = $this->formatPost($post);
        }
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'posts' => $formattedPosts,
                ],
                'meta' => [
                    'total' => $result['total'],
                    'per_page' => $result['per_page'],
                    'current_page' => $result['current_page'],
                    'last_page' => $result['last_page'],
                ],
            ]);
        }
        
        return Response::view('admin.blog.index', [
            'title' => 'Blog Posts',
            'posts' => $formattedPosts,
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            ],
            'status_filter' => $status,
        ]);
    }

    /**
     * Show create post form
     */
    public function create(Request $request): Response
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        
        return Response::view('admin.blog.create', [
            'title' => 'Create Post',
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Store new post
     */
    public function store(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'title_en' => 'required|min:5|max:255',
            'content_en' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/blog/create');
        }

        // Get current user as author
        $session = Application::getInstance()?->session();
        $authorId = $session?->get('user_id');

        // Process featured image
        $featuredImage = $this->processImage($request);

        $post = $this->blogService->createPost([
            'author_id' => $authorId,
            'category_id' => $request->input('category_id') ? (int) $request->input('category_id') : null,
            'title_en' => $request->input('title_en'),
            'title_ne' => $request->input('title_ne'),
            'slug' => $request->input('slug'),
            'excerpt' => $request->input('excerpt'),
            'content_en' => $request->input('content_en'),
            'content_ne' => $request->input('content_ne'),
            'featured_image' => $featuredImage,
            'status' => $request->input('status', BlogPost::STATUS_DRAFT),
            'published_at' => $request->input('published_at'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'tags' => $request->input('tags', []),
        ]);

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Post created successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Post created successfully',
                'post' => $post->toArray(),
            ], 201);
        }

        return Response::redirect('/admin/blog');
    }

    /**
     * Show edit post form
     */
    public function edit(Request $request, string $id): Response
    {
        $post = BlogPost::find((int) $id);
        
        if ($post === null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Post not found.');
            
            return Response::redirect('/admin/blog');
        }
        
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        $postTags = $post->tags();
        
        return Response::view('admin.blog.edit', [
            'title' => 'Edit Post',
            'post' => $post->toArray(),
            'categories' => $categories,
            'tags' => $tags,
            'post_tags' => $postTags,
        ]);
    }

    /**
     * Update post
     */
    public function update(Request $request, string $id): Response
    {
        $post = BlogPost::find((int) $id);
        
        if ($post === null) {
            if ($request->expectsJson()) {
                return Response::error('Post not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Post not found.');
            
            return Response::redirect('/admin/blog');
        }
        
        $validator = new Validator($request->all(), [
            'title_en' => 'required|min:5|max:255',
            'content_en' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/blog/' . $id . '/edit');
        }

        // Process featured image
        $featuredImage = $this->processImage($request);
        
        // Keep existing image if no new one uploaded
        if ($featuredImage === null && isset($post->attributes['featured_image'])) {
            $featuredImage = $post->attributes['featured_image'];
        }

        $this->blogService->updatePost((int) $id, [
            'category_id' => $request->input('category_id') ? (int) $request->input('category_id') : null,
            'title_en' => $request->input('title_en'),
            'title_ne' => $request->input('title_ne'),
            'slug' => $request->input('slug'),
            'excerpt' => $request->input('excerpt'),
            'content_en' => $request->input('content_en'),
            'content_ne' => $request->input('content_ne'),
            'featured_image' => $featuredImage,
            'status' => $request->input('status', BlogPost::STATUS_DRAFT),
            'published_at' => $request->input('published_at'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'tags' => $request->input('tags', []),
        ]);

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Post updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Post updated successfully',
            ]);
        }

        return Response::redirect('/admin/blog');
    }

    /**
     * Delete post
     */
    public function delete(Request $request, string $id): Response
    {
        $deleted = $this->blogService->deletePost((int) $id);
        
        if (!$deleted) {
            if ($request->expectsJson()) {
                return Response::error('Post not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Post not found.');
            
            return Response::redirect('/admin/blog');
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Post deleted successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);
        }

        return Response::redirect('/admin/blog');
    }

    /**
     * Toggle publish status
     */
    public function togglePublish(Request $request, string $id): Response
    {
        $post = $this->blogService->togglePublish((int) $id);
        
        if ($post === null) {
            if ($request->expectsJson()) {
                return Response::error('Post not found', 404);
            }
            
            return Response::redirect('/admin/blog');
        }

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Post status updated',
                'status' => $post->attributes['status'],
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Post status updated!');

        return Response::redirect('/admin/blog');
    }

    /**
     * Categories management
     */
    public function categories(Request $request): Response
    {
        $categories = BlogCategory::all();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => array_map(fn($c) => $c->toArray(), $categories),
            ]);
        }
        
        return Response::view('admin.blog.categories', [
            'title' => 'Blog Categories',
            'categories' => $categories,
        ]);
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            
            return Response::redirect('/admin/blog/categories');
        }

        $category = $this->blogService->createCategory([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'active'),
        ]);

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category->toArray(),
            ], 201);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Category created successfully!');

        return Response::redirect('/admin/blog/categories');
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, string $id): Response
    {
        $category = $this->blogService->updateCategory((int) $id, [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'active'),
        ]);

        if ($category === null) {
            if ($request->expectsJson()) {
                return Response::error('Category not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Category not found.');
            
            return Response::redirect('/admin/blog/categories');
        }

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Category updated successfully',
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Category updated successfully!');

        return Response::redirect('/admin/blog/categories');
    }

    /**
     * Delete category
     */
    public function deleteCategory(Request $request, string $id): Response
    {
        $deleted = $this->blogService->deleteCategory((int) $id);

        if (!$deleted) {
            if ($request->expectsJson()) {
                return Response::error('Category not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Category not found.');
            
            return Response::redirect('/admin/blog/categories');
        }

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Category deleted successfully!');

        return Response::redirect('/admin/blog/categories');
    }

    /**
     * Process featured image upload
     */
    private function processImage(Request $request): ?string
    {
        if (!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        $file = $_FILES['featured_image'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return null;
        }
        
        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($detectedMimeType, $allowedMimeTypes, true)) {
            return null;
        }
        
        // Map MIME type to extension
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        
        $extension = $mimeToExt[$detectedMimeType] ?? 'jpg';
        
        // Generate secure filename with fallback
        try {
            $randomPart = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            // Fallback to uniqid if random_bytes fails
            $randomPart = uniqid('', true);
            $randomPart = str_replace('.', '', $randomPart);
        }
        
        $filename = 'blog_' . $randomPart . '.' . $extension;
        
        // Ensure upload directory exists
        $uploadDir = Application::getInstance()?->basePath('public/uploads/blog') ?? 'public/uploads/blog';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }
        
        return $filename;
    }

    /**
     * Format post for API response
     * 
     * @return array<string, mixed>
     */
    private function formatPost(BlogPost $post): array
    {
        return [
            'id' => $post->getKey(),
            'title' => $post->getTitle(),
            'title_ne' => $post->attributes['title_ne'] ?? '',
            'slug' => $post->attributes['slug'] ?? '',
            'excerpt' => $post->getExcerpt(100),
            'featured_image' => $post->getFeaturedImageUrl(),
            'category' => $post->category(),
            'author' => $post->author(),
            'status' => $post->attributes['status'] ?? 'draft',
            'is_published' => $post->isPublished(),
            'views_count' => (int) ($post->attributes['views_count'] ?? 0),
            'published_at' => $post->attributes['published_at'] ?? null,
            'created_at' => $post->attributes['created_at'] ?? null,
            'updated_at' => $post->attributes['updated_at'] ?? null,
        ];
    }
}
