<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Admin Category Controller
 * 
 * Handles category management in the admin panel.
 */
class CategoryController
{
    /**
     * List all categories
     */
    public function index(Request $request): Response
    {
        $categories = Category::all();
        
        // Format categories with product counts
        $formattedCategories = array_map(function($category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->attributes['name_en'] ?? '',
                'name_ne' => $category->attributes['name_ne'] ?? '',
                'slug' => $category->attributes['slug'] ?? '',
                'description' => $category->attributes['description'] ?? '',
                'image' => $category->attributes['image'] ?? null,
                'status' => $category->attributes['status'] ?? 'active',
                'parent_id' => $category->attributes['parent_id'] ?? null,
                'display_order' => (int) ($category->attributes['display_order'] ?? 0),
                'product_count' => $category->getProductCount(),
                'created_at' => $category->attributes['created_at'] ?? null,
            ];
        }, $categories);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => $formattedCategories,
            ]);
        }
        
        return Response::view('admin.categories.index', [
            'title' => 'Categories',
            'categories' => $formattedCategories,
        ]);
    }

    /**
     * Show create category form
     */
    public function create(Request $request): Response
    {
        $categories = Category::roots();
        
        return Response::view('admin.categories.create', [
            'title' => 'Create Category',
            'parentCategories' => $categories,
        ]);
    }

    /**
     * Store new category
     */
    public function store(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'name_en' => 'required|min:2|max:255',
            'slug' => 'required|min:2|max:255',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/categories/create');
        }

        // Check if slug is unique
        $existingCategory = Category::findBySlug($request->input('slug'));
        if ($existingCategory !== null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['slug' => ['This slug is already in use.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/categories/create');
        }

        $parentId = $request->input('parent_id') ?: null;
        $app = Application::getInstance();
        if ($parentId !== null) {
            $parentId = (int) $parentId;
        }

        // Create category
        Category::create([
            'parent_id' => $parentId,
            'name_en' => $request->input('name_en'),
            'name_ne' => $request->input('name_ne'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'display_order' => (int) $request->input('display_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        $session = $app?->session();
        $session?->success('Category created successfully!');

        return Response::redirect('/admin/categories');
    }

    /**
     * Show edit category form
     */
    public function edit(Request $request, string $id): Response
    {
        $category = Category::find($id);
        
        if ($category === null) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Category not found.');
            
            return Response::redirect('/admin/categories');
        }
        
        // Get parent categories excluding current one
        $parentCategories = array_filter(Category::roots(), function($cat) use ($id) {
            return (string) $cat->getKey() !== (string) $id;
        });
        
        return Response::view('admin.categories.edit', [
            'title' => 'Edit Category',
            'category' => $category->toArray(),
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Update category
     */
    public function update(Request $request, string $id): Response
    {
        $category = Category::find($id);
        
        if ($category === null) {
            if ($request->expectsJson()) {
                return Response::error('Category not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Category not found.');
            
            return Response::redirect('/admin/categories');
        }
        
        $validator = new Validator($request->all(), [
            'name_en' => 'required|min:2|max:255',
            'slug' => 'required|min:2|max:255',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/categories/' . $id . '/edit');
        }

        // Check if slug is unique (excluding current category)
        $existingCategory = Category::findBySlug($request->input('slug'));
        if ($existingCategory !== null && (string) $existingCategory->getKey() !== (string) $id) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['slug' => ['This slug is already in use.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/categories/' . $id . '/edit');
        }

        // Prevent self-referencing parent
        $parentId = $request->input('parent_id') ?: null;
        if ($parentId !== null && (string) $parentId === (string) $id) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['parent_id' => ['Category cannot be its own parent.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/admin/categories/' . $id . '/edit');
        }

        if ($parentId !== null) {
            $parentId = (int) $parentId;
        }

        // Update category
        $category->fill([
            'parent_id' => $parentId,
            'name_en' => $request->input('name_en'),
            'name_ne' => $request->input('name_ne'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'display_order' => (int) $request->input('display_order', 0),
            'status' => $request->input('status', 'active'),
        ]);
        $category->save();

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Category updated successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Category updated successfully',
            ]);
        }

        return Response::redirect('/admin/categories');
    }

    /**
     * Delete category
     */
    public function delete(Request $request, string $id): Response
    {
        $category = Category::find($id);
        
        if ($category === null) {
            if ($request->expectsJson()) {
                return Response::error('Category not found', 404);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Category not found.');
            
            return Response::redirect('/admin/categories');
        }
        
        // Check if category has products
        if ($category->getProductCount() > 0) {
            if ($request->expectsJson()) {
                return Response::error('Cannot delete category with products', 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Cannot delete category that has products. Please reassign or delete the products first.');
            
            return Response::redirect('/admin/categories');
        }
        
        $category->delete();

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Category deleted successfully!');

        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        }

        return Response::redirect('/admin/categories');
    }
}
