<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BlogService;
use App\Services\SeoService;
use Core\Request;
use Core\Response;

/**
 * Blog Controller
 * 
 * Handles public blog functionality.
 */
class BlogController
{
    private BlogService $blogService;
    private SeoService $seoService;

    public function __construct()
    {
        $this->blogService = new BlogService();
        $this->seoService = new SeoService();
    }

    /**
     * List all published posts
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        
        $result = $this->blogService->getPosts($page, $perPage);
        $sidebar = $this->blogService->getSidebarData();
        
        $seoMeta = $this->seoService->generateMeta([
            'title' => 'Blog | KHAIRAWANG DAIRY',
            'description' => 'Read our latest articles about dairy products, recipes, health tips, and farm updates.',
            'url' => '/blog',
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'posts' => array_map(fn($p) => $this->formatPost($p), $result['posts']),
                ],
                'meta' => [
                    'total' => $result['total'],
                    'per_page' => $result['per_page'],
                    'current_page' => $result['current_page'],
                    'last_page' => $result['last_page'],
                ],
            ]);
        }
        
        return Response::view('blog.index', [
            'title' => 'Blog',
            'posts' => $result['posts'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            ],
            'sidebar' => $sidebar,
            'seo' => $seoMeta,
        ]);
    }

    /**
     * Show single post
     */
    public function show(Request $request, string $slug): Response
    {
        $post = $this->blogService->getPost($slug);
        
        if ($post === null) {
            if ($request->expectsJson()) {
                return Response::error('Post not found', 404);
            }
            
            return Response::view('errors.404', ['message' => 'Post not found'], 404);
        }
        
        $relatedPosts = $this->blogService->getRelatedPosts($post);
        $sidebar = $this->blogService->getSidebarData();
        
        $seoMeta = $this->seoService->generateBlogMeta($post);
        
        $breadcrumbs = $this->seoService->generateBreadcrumbs([
            ['name' => 'Blog', 'url' => '/blog'],
            ['name' => $post->getTitle(), 'url' => '/blog/' . $slug],
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $this->formatPost($post, true),
            ]);
        }
        
        return Response::view('blog.show', [
            'title' => $post->getTitle(),
            'post' => $post,
            'related_posts' => $relatedPosts,
            'sidebar' => $sidebar,
            'seo' => $seoMeta,
            'breadcrumbs' => $breadcrumbs,
            'structured_data' => $this->seoService->generateStructuredData('article', [
                'title' => $post->getTitle(),
                'description' => $post->getExcerpt(),
                'image' => $post->getFeaturedImageUrl(),
                'slug' => $slug,
                'published_at' => $post->attributes['published_at'] ?? '',
                'updated_at' => $post->attributes['updated_at'] ?? '',
                'author' => $post->author()['name'] ?? 'KHAIRAWANG DAIRY',
            ]),
        ]);
    }

    /**
     * Show posts by category
     */
    public function category(Request $request, string $slug): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        
        $result = $this->blogService->getPostsByCategory($slug, $page);
        
        if ($result['category'] === null) {
            if ($request->expectsJson()) {
                return Response::error('Category not found', 404);
            }
            
            return Response::view('errors.404', ['message' => 'Category not found'], 404);
        }
        
        $sidebar = $this->blogService->getSidebarData();
        
        $categoryName = $result['category']->attributes['name'] ?? '';
        
        $seoMeta = $this->seoService->generateMeta([
            'title' => $categoryName . ' | Blog | KHAIRAWANG DAIRY',
            'description' => $result['category']->attributes['description'] ?? "Read articles in {$categoryName} category.",
            'url' => '/blog/category/' . $slug,
        ]);
        
        $breadcrumbs = $this->seoService->generateBreadcrumbs([
            ['name' => 'Blog', 'url' => '/blog'],
            ['name' => $categoryName, 'url' => '/blog/category/' . $slug],
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'category' => $result['category']->toArray(),
                    'posts' => array_map(fn($p) => $this->formatPost($p), $result['posts']),
                ],
                'meta' => [
                    'total' => $result['total'],
                    'per_page' => $result['per_page'],
                    'current_page' => $result['current_page'],
                    'last_page' => $result['last_page'],
                ],
            ]);
        }
        
        return Response::view('blog.category', [
            'title' => $categoryName,
            'category' => $result['category'],
            'posts' => $result['posts'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            ],
            'sidebar' => $sidebar,
            'seo' => $seoMeta,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show posts by tag
     */
    public function tag(Request $request, string $slug): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        
        $result = $this->blogService->getPostsByTag($slug, $page);
        
        if ($result['tag'] === null) {
            if ($request->expectsJson()) {
                return Response::error('Tag not found', 404);
            }
            
            return Response::view('errors.404', ['message' => 'Tag not found'], 404);
        }
        
        $sidebar = $this->blogService->getSidebarData();
        
        $tagName = $result['tag']->attributes['name'] ?? '';
        
        $seoMeta = $this->seoService->generateMeta([
            'title' => 'Posts tagged "' . $tagName . '" | Blog | KHAIRAWANG DAIRY',
            'description' => "Browse articles tagged with {$tagName}.",
            'url' => '/blog/tag/' . $slug,
        ]);
        
        $breadcrumbs = $this->seoService->generateBreadcrumbs([
            ['name' => 'Blog', 'url' => '/blog'],
            ['name' => 'Tag: ' . $tagName, 'url' => '/blog/tag/' . $slug],
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'tag' => $result['tag']->toArray(),
                    'posts' => array_map(fn($p) => $this->formatPost($p), $result['posts']),
                ],
                'meta' => [
                    'total' => $result['total'],
                    'per_page' => $result['per_page'],
                    'current_page' => $result['current_page'],
                    'last_page' => $result['last_page'],
                ],
            ]);
        }
        
        return Response::view('blog.tag', [
            'title' => 'Tag: ' . $tagName,
            'tag' => $result['tag'],
            'posts' => $result['posts'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            ],
            'sidebar' => $sidebar,
            'seo' => $seoMeta,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Search posts
     */
    public function search(Request $request): Response
    {
        $query = trim($request->query('q', '') ?? '');
        
        if (empty($query)) {
            return Response::redirect('/blog');
        }
        
        $posts = $this->blogService->searchPosts($query, 20);
        $sidebar = $this->blogService->getSidebarData();
        
        $seoMeta = $this->seoService->generateMeta([
            'title' => 'Search: ' . $query . ' | Blog | KHAIRAWANG DAIRY',
            'description' => "Search results for '{$query}' in our blog.",
            'url' => '/blog/search?q=' . urlencode($query),
        ]);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'posts' => array_map(fn($p) => $this->formatPost($p), $posts),
                ],
            ]);
        }
        
        return Response::view('blog.search', [
            'title' => 'Search: ' . $query,
            'query' => $query,
            'posts' => $posts,
            'sidebar' => $sidebar,
            'seo' => $seoMeta,
        ]);
    }

    /**
     * Format post for API response
     * 
     * @return array<string, mixed>
     */
    private function formatPost($post, bool $includeContent = false): array
    {
        $data = [
            'id' => $post->getKey(),
            'title' => $post->getTitle(),
            'slug' => $post->attributes['slug'] ?? '',
            'excerpt' => $post->getExcerpt(),
            'featured_image' => $post->getFeaturedImageUrl(),
            'category' => $post->category(),
            'author' => $post->author(),
            'tags' => $post->tags(),
            'views_count' => (int) ($post->attributes['views_count'] ?? 0),
            'published_at' => $post->attributes['published_at'] ?? null,
            'created_at' => $post->attributes['created_at'] ?? null,
        ];
        
        if ($includeContent) {
            $data['content'] = $post->getContent();
            $data['meta_title'] = $post->getMetaTitle();
            $data['meta_description'] = $post->getMetaDescription();
        }
        
        return $data;
    }
}
