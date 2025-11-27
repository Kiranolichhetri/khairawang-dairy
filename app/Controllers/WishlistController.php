<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\WishlistService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Wishlist Controller
 * 
 * Handles user wishlist operations.
 */
class WishlistController
{
    private WishlistService $wishlistService;

    public function __construct()
    {
        $this->wishlistService = new WishlistService();
    }

    /**
     * Get current user ID from session
     */
    private function getUserId(): ?int
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        return $userId ? (int) $userId : null;
    }

    /**
     * View wishlist
     */
    public function index(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $items = $this->wishlistService->getWishlistItems($userId);
        $count = $this->wishlistService->getWishlistCount($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'count' => $count,
                ],
            ]);
        }
        
        return Response::view('account.wishlist.index', [
            'title' => 'My Wishlist',
            'items' => $items,
            'count' => $count,
        ]);
    }

    /**
     * Get wishlist count (for header badge)
     */
    public function count(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::json(['count' => 0]);
        }
        
        $count = $this->wishlistService->getWishlistCount($userId);
        
        return Response::json(['count' => $count]);
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request, string $productId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Please login to add items to your wishlist',
                    'redirect' => '/login',
                ], 401);
            }
            
            $session = Application::getInstance()?->session();
            $session?->setIntendedUrl('/account/wishlist');
            return Response::redirect('/login');
        }
        
        $result = $this->wishlistService->addToWishlist($userId, (int) $productId);
        
        if ($request->expectsJson()) {
            return Response::json(array_merge($result, [
                'count' => $this->wishlistService->getWishlistCount($userId),
            ]), $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        // Redirect back to previous page
        $referer = $request->header('Referer') ?? '/account/wishlist';
        return Response::redirect($referer);
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Request $request, string $productId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->wishlistService->removeFromWishlist($userId, (int) $productId);
        
        if ($request->expectsJson()) {
            return Response::json(array_merge($result, [
                'count' => $this->wishlistService->getWishlistCount($userId),
            ]), $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/wishlist');
    }

    /**
     * Toggle wishlist status
     */
    public function toggle(Request $request, string $productId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Please login to use wishlist',
                    'redirect' => '/login',
                ], 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->wishlistService->toggleWishlist($userId, (int) $productId);
        
        if ($request->expectsJson()) {
            return Response::json(array_merge($result, [
                'count' => $this->wishlistService->getWishlistCount($userId),
            ]));
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        // Redirect back to previous page
        $referer = $request->header('Referer') ?? '/account/wishlist';
        return Response::redirect($referer);
    }

    /**
     * Move item to cart
     */
    public function moveToCart(Request $request, string $productId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->wishlistService->moveToCart($userId, (int) $productId);
        
        if ($request->expectsJson()) {
            return Response::json(array_merge($result, [
                'wishlist_count' => $this->wishlistService->getWishlistCount($userId),
            ]), $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/wishlist');
    }

    /**
     * Clear entire wishlist
     */
    public function clear(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->wishlistService->clearWishlist($userId);
        
        if ($request->expectsJson()) {
            return Response::json($result);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/wishlist');
    }

    /**
     * Sync guest wishlist after login
     */
    public function sync(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $productIds = $request->input('product_ids', []);
        
        if (!is_array($productIds)) {
            return Response::json(['success' => false, 'message' => 'Invalid data'], 400);
        }
        
        $result = $this->wishlistService->syncGuestWishlist($userId, $productIds);
        
        return Response::json(array_merge($result, [
            'count' => $this->wishlistService->getWishlistCount($userId),
        ]));
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, string $productId): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::json(['in_wishlist' => false]);
        }
        
        $inWishlist = $this->wishlistService->isInWishlist($userId, (int) $productId);
        
        return Response::json(['in_wishlist' => $inWishlist]);
    }
}
