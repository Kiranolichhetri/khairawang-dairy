<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;

/**
 * Blog Service
 * 
 * Handles blog post operations.
 */
class BlogService
{
    /**
     * Get paginated published posts
     * 
     * @return array<string, mixed>
     */
    public function getPosts(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $posts = BlogPost::published($perPage, $offset);
        $total = BlogPost::publishedCount();
        
        return [
            'posts' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get single post by slug
     */
    public function getPost(string $slug, bool $incrementViews = true): ?BlogPost
    {
        $post = BlogPost::findBySlug($slug);
        
        if ($post === null || !$post->isPublished()) {
            return null;
        }
        
        if ($incrementViews) {
            $post->incrementViews();
        }
        
        return $post;
    }

    /**
     * Get posts by category slug
     * 
     * @return array<string, mixed>
     */
    public function getPostsByCategory(string $categorySlug, int $page = 1, int $perPage = 10): array
    {
        $category = BlogCategory::findBySlug($categorySlug);
        
        if ($category === null) {
            return [
                'category' => null,
                'posts' => [],
                'total' => 0,
            ];
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = BlogPost::byCategory($category->getKey(), $perPage, $offset);
        
        $total = BlogPost::query()
            ->where('category_id', $category->getKey())
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->count();
        
        return [
            'category' => $category,
            'posts' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get posts by tag slug
     * 
     * @return array<string, mixed>
     */
    public function getPostsByTag(string $tagSlug, int $page = 1, int $perPage = 10): array
    {
        $tag = BlogTag::findBySlug($tagSlug);
        
        if ($tag === null) {
            return [
                'tag' => null,
                'posts' => [],
                'total' => 0,
            ];
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = BlogPost::byTag($tag->getKey(), $perPage, $offset);
        
        $total = $tag->getPostCount();
        
        return [
            'tag' => $tag,
            'posts' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Search posts
     * 
     * @return array<BlogPost>
     */
    public function searchPosts(string $query, int $limit = 10): array
    {
        return BlogPost::search($query, $limit);
    }

    /**
     * Get recent posts for sidebar
     * 
     * @return array<BlogPost>
     */
    public function getRecentPosts(int $limit = 5): array
    {
        return BlogPost::recent($limit);
    }

    /**
     * Get popular posts for sidebar
     * 
     * @return array<BlogPost>
     */
    public function getPopularPosts(int $limit = 5): array
    {
        return BlogPost::popular($limit);
    }

    /**
     * Get all categories
     * 
     * @return array<BlogCategory>
     */
    public function getCategories(): array
    {
        return BlogCategory::active();
    }

    /**
     * Get popular tags
     * 
     * @return array<BlogTag>
     */
    public function getPopularTags(int $limit = 15): array
    {
        return BlogTag::popular($limit);
    }

    /**
     * Get related posts
     * 
     * @return array<BlogPost>
     */
    public function getRelatedPosts(BlogPost $post, int $limit = 4): array
    {
        return $post->getRelatedPosts($limit);
    }

    /**
     * Get sidebar data for blog views
     * 
     * @return array<string, mixed>
     */
    public function getSidebarData(): array
    {
        return [
            'recent_posts' => $this->getRecentPosts(),
            'categories' => $this->getCategories(),
            'popular_tags' => $this->getPopularTags(),
        ];
    }

    /**
     * Create a new blog post
     * 
     * @param array<string, mixed> $data
     */
    public function createPost(array $data): BlogPost
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = BlogPost::generateSlug($data['title_en'] ?? '');
        }
        
        // Set published_at if publishing
        if (($data['status'] ?? '') === BlogPost::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $post = BlogPost::create($data);
        
        // Handle tags
        if (!empty($data['tags'])) {
            $this->syncPostTags($post, $data['tags']);
        }
        
        return $post;
    }

    /**
     * Update a blog post
     * 
     * @param array<string, mixed> $data
     */
    public function updatePost(int $id, array $data): ?BlogPost
    {
        $post = BlogPost::find($id);
        
        if ($post === null) {
            return null;
        }
        
        // Update slug if title changed and slug not provided
        if (!empty($data['title_en']) && empty($data['slug'])) {
            $data['slug'] = BlogPost::generateSlug($data['title_en'], $id);
        }
        
        // Set published_at if publishing for first time
        if (($data['status'] ?? '') === BlogPost::STATUS_PUBLISHED 
            && !$post->isPublished() 
            && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $post->fill($data);
        $post->save();
        
        // Handle tags
        if (isset($data['tags'])) {
            $this->syncPostTags($post, $data['tags']);
        }
        
        return $post;
    }

    /**
     * Delete a blog post
     */
    public function deletePost(int $id): bool
    {
        $post = BlogPost::find($id);
        
        if ($post === null) {
            return false;
        }
        
        return $post->delete();
    }

    /**
     * Toggle publish status
     */
    public function togglePublish(int $id): ?BlogPost
    {
        $post = BlogPost::find($id);
        
        if ($post === null) {
            return null;
        }
        
        if ($post->isPublished()) {
            $post->unpublish();
        } else {
            $post->publish();
        }
        
        return $post;
    }

    /**
     * Sync tags for a post
     * 
     * @param array<string|int> $tags Tag IDs or names
     */
    protected function syncPostTags(BlogPost $post, array $tags): void
    {
        $tagIds = [];
        
        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
            } elseif (is_string($tag) && !empty(trim($tag))) {
                $blogTag = BlogTag::findOrCreateByName(trim($tag));
                $tagIds[] = $blogTag->getKey();
            }
        }
        
        $post->syncTags($tagIds);
    }

    /**
     * Get all posts for admin (includes drafts)
     * 
     * @return array<string, mixed>
     */
    public function getAllPosts(int $page = 1, int $perPage = 20, ?string $status = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $query = BlogPost::query()->orderBy('created_at', 'DESC');
        
        if ($status !== null) {
            $query->where('status', $status);
        }
        
        $total = $query->count();
        
        $rows = $query->limit($perPage)->offset($offset)->get();
        $posts = array_map(fn($row) => BlogPost::hydrate($row), $rows);
        
        return [
            'posts' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Create a new category
     * 
     * @param array<string, mixed> $data
     */
    public function createCategory(array $data): BlogCategory
    {
        if (empty($data['slug'])) {
            $data['slug'] = BlogCategory::generateSlug($data['name']);
        }
        
        return BlogCategory::create($data);
    }

    /**
     * Update a category
     * 
     * @param array<string, mixed> $data
     */
    public function updateCategory(int $id, array $data): ?BlogCategory
    {
        $category = BlogCategory::find($id);
        
        if ($category === null) {
            return null;
        }
        
        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = BlogCategory::generateSlug($data['name'], $id);
        }
        
        $category->fill($data);
        $category->save();
        
        return $category;
    }

    /**
     * Delete a category
     */
    public function deleteCategory(int $id): bool
    {
        $category = BlogCategory::find($id);
        
        if ($category === null) {
            return false;
        }
        
        return $category->delete();
    }
}
