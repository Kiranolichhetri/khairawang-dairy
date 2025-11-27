<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use Core\Request;
use Core\Response;

/**
 * Cart Controller
 * 
 * Handles shopping cart operations.
 */
class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    /**
     * View cart contents
     * 
     * Renders the shopping cart HTML view.
     * The view uses Alpine.js which fetches data from /api/v1/cart.
     */
    public function index(Request $request): Response
    {
        return Response::view('cart.index', [
            'title' => 'Shopping Cart',
            'pageDescription' => 'View and manage items in your shopping cart at KHAIRAWANG DAIRY.',
        ]);
    }

    /**
     * API endpoint: Get cart contents (JSON)
     */
    public function apiIndex(Request $request): Response
    {
        $cart = $this->cartService->getCartContents();
        
        return Response::json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): Response
    {
        $productId = (int) $request->input('product_id', 0);
        $quantity = (int) $request->input('quantity', 1);
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        
        if ($productId <= 0) {
            return Response::error('Invalid product', 400);
        }
        
        if ($quantity <= 0) {
            return Response::error('Invalid quantity', 400);
        }
        
        $result = $this->cartService->addItem($productId, $quantity, $variantId);
        
        if ($result['success']) {
            return Response::json($result, 201);
        }
        
        return Response::error($result['message'], 400);
    }

    /**
     * Update item quantity
     */
    public function update(Request $request, string $id): Response
    {
        $itemId = (int) $id;
        $quantity = (int) $request->input('quantity', 1);
        
        if ($itemId <= 0) {
            return Response::error('Invalid item', 400);
        }
        
        $result = $this->cartService->updateItem($itemId, $quantity);
        
        if ($result['success']) {
            return Response::json($result);
        }
        
        return Response::error($result['message'], 400);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, string $id): Response
    {
        $itemId = (int) $id;
        
        if ($itemId <= 0) {
            return Response::error('Invalid item', 400);
        }
        
        $result = $this->cartService->removeItem($itemId);
        
        if ($result['success']) {
            return Response::json($result);
        }
        
        return Response::error($result['message'], 400);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): Response
    {
        $result = $this->cartService->clearCart();
        return Response::json($result);
    }

    /**
     * Sync localStorage cart with session/database
     * Used when guest logs in to merge carts
     */
    public function sync(Request $request): Response
    {
        $items = $request->input('items', []);
        
        if (!is_array($items)) {
            return Response::error('Invalid items format', 400);
        }
        
        // Validate items structure
        $validItems = [];
        foreach ($items as $item) {
            if (isset($item['product_id']) && is_numeric($item['product_id'])) {
                $validItems[] = [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 1,
                    'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                ];
            }
        }
        
        $result = $this->cartService->syncCart($validItems);
        return Response::json($result);
    }

    /**
     * Get cart count (for header badge)
     */
    public function count(Request $request): Response
    {
        $cart = $this->cartService->getCartContents();
        
        return Response::json([
            'success' => true,
            'count' => $cart['count'],
            'total' => $cart['total'],
        ]);
    }
}
