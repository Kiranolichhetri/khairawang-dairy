<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Core\Session;
use Core\Application;

/**
 * Cart Service
 * 
 * Handles shopping cart operations for both guests and authenticated users.
 */
class CartService
{
    private StockService $stockService;
    private ?Session $session;
    
    public function __construct(?StockService $stockService = null)
    {
        $this->stockService = $stockService ?? new StockService();
        $this->session = Application::getInstance()?->session();
    }

    /**
     * Get or create cart for current user/session
     */
    public function getCart(): Cart
    {
        $userId = $this->session?->get('user_id');
        
        if ($userId !== null) {
            return Cart::forUser((int) $userId);
        }
        
        $sessionId = $this->getSessionId();
        return Cart::forSession($sessionId);
    }

    /**
     * Get session ID for guest cart
     */
    private function getSessionId(): string
    {
        $sessionId = $this->session?->get('cart_session_id');
        
        if ($sessionId === null) {
            $sessionId = bin2hex(random_bytes(16));
            $this->session?->set('cart_session_id', $sessionId);
        }
        
        return $sessionId;
    }

    /**
     * Get cart contents with product details
     * 
     * @return array<string, mixed>
     */
    public function getCartContents(): array
    {
        $cart = $this->getCart();
        $items = $cart->itemsWithProducts();
        
        $cartItems = [];
        $subtotal = 0.0;
        
        foreach ($items as $item) {
            $salePrice = $item['sale_price'] ?? null;
            $price = ($salePrice !== null && $salePrice > 0) ? (float) $salePrice : (float) $item['price'];
            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;
            
            // Handle images - can be JSON string (MySQL) or array (MongoDB)
            $images = $item['images'] ?? [];
            if (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }
            
            $primaryImage = '/assets/images/product-placeholder.png';
            if (!empty($images) && is_array($images)) {
                $firstImage = $images[0];
                // Check if already has path prefix
                if (str_starts_with($firstImage, '/uploads/') || str_starts_with($firstImage, 'http')) {
                    $primaryImage = $firstImage;
                } else {
                    $primaryImage = '/uploads/products/' . $firstImage;
                }
            }
            
            $cartItems[] = [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'name' => $item['name_en'],
                'slug' => $item['slug'],
                'price' => $price,
                'original_price' => (float) $item['price'],
                'quantity' => $item['quantity'],
                'stock' => $item['stock'],
                'total' => $itemTotal,
                'image' => $primaryImage,
            ];
        }
        
        $shippingCost = $this->calculateShipping($subtotal);
        $total = $subtotal + $shippingCost;
        
        return [
            'items' => $cartItems,
            'count' => $cart->getItemCount(),
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'total' => $total,
            'free_shipping' => $shippingCost === 0.0,
            'free_shipping_threshold' => 1000.0,
        ];
    }

    /**
     * Add item to cart
     * 
     * @return array<string, mixed>
     */
    public function addItem(string|int $productId, int $quantity = 1, string|int|null $variantId = null): array
    {
        // Validate product
        $product = Product::find($productId);
        
        if ($product === null) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        if (!$product->isPublished()) {
            return ['success' => false, 'message' => 'Product is not available'];
        }
        
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }
        
        // Check stock
        if (!$this->stockService->hasStock($productId, $quantity)) {
            $stock = $product->attributes['stock'] ?? 0;
            return [
                'success' => false,
                'message' => "Only {$stock} units available",
                'available_stock' => $stock,
            ];
        }
        
        $cart = $this->getCart();
        
        if ($cart->addItem($productId, $quantity, $variantId)) {
            return [
                'success' => true,
                'message' => 'Item added to cart',
                'cart' => $this->getCartContents(),
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add item to cart'];
    }

    /**
     * Update item quantity
     * 
     * @return array<string, mixed>
     */
    public function updateItem(string|int $itemId, int $quantity): array
    {
        $cart = $this->getCart();
        
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }
        
        // Get item to validate stock
        $items = $cart->items();
        $item = null;
        
        foreach ($items as $cartItem) {
            // Compare as strings to handle both int and string IDs
            if ((string) $cartItem['id'] === (string) $itemId) {
                $item = $cartItem;
                break;
            }
        }
        
        if ($item === null) {
            return ['success' => false, 'message' => 'Item not found in cart'];
        }
        
        // Check stock
        if (!$this->stockService->hasStock($item['product_id'], $quantity)) {
            $product = Product::find($item['product_id']);
            $stock = $product?->attributes['stock'] ?? 0;
            return [
                'success' => false,
                'message' => "Only {$stock} units available",
                'available_stock' => $stock,
            ];
        }
        
        if ($cart->updateItemQuantity($itemId, $quantity)) {
            return [
                'success' => true,
                'message' => 'Cart updated',
                'cart' => $this->getCartContents(),
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to update cart'];
    }

    /**
     * Remove item from cart
     * 
     * @return array<string, mixed>
     */
    public function removeItem(string|int $itemId): array
    {
        $cart = $this->getCart();
        
        if ($cart->removeItem($itemId)) {
            return [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => $this->getCartContents(),
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to remove item'];
    }

    /**
     * Clear cart
     * 
     * @return array<string, mixed>
     */
    public function clearCart(): array
    {
        $cart = $this->getCart();
        $cart->clear();
        
        return [
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $this->getCartContents(),
        ];
    }

    /**
     * Sync guest cart with localStorage items on login
     * 
     * @param array<array{product_id: string|int, quantity: int, variant_id?: string|int|null}> $items
     * @return array<string, mixed>
     */
    public function syncCart(array $items): array
    {
        $cart = $this->getCart();
        $errors = [];
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $variantId = $item['variant_id'] ?? null;
            
            if (!empty($productId) && $quantity > 0) {
                $product = Product::find($productId);
                
                if ($product !== null && $product->isPublished()) {
                    if ($this->stockService->hasStock($productId, $quantity)) {
                        $cart->addItem($productId, $quantity, $variantId);
                    } else {
                        $errors[] = "Insufficient stock for: " . $product->getName();
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'message' => empty($errors) ? 'Cart synced successfully' : 'Cart synced with some issues',
            'errors' => $errors,
            'cart' => $this->getCartContents(),
        ];
    }

    /**
     * Merge guest cart to user cart after login
     */
    public function mergeGuestCart(int $userId): void
    {
        $sessionId = $this->session?->get('cart_session_id');
        
        if ($sessionId !== null) {
            Cart::mergeGuestCart($userId, $sessionId);
        }
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShipping(float $subtotal): float
    {
        // Free shipping over NPR 1000
        if ($subtotal >= 1000.0) {
            return 0.0;
        }
        
        // Default shipping cost
        return config('app.shipping_cost', 100.0);
    }

    /**
     * Validate cart before checkout
     * 
     * @return array<string, mixed>
     */
    public function validateForCheckout(): array
    {
        $cart = $this->getCart();
        $items = $cart->items();
        
        if (empty($items)) {
            return ['valid' => false, 'message' => 'Cart is empty'];
        }
        
        $stockItems = [];
        foreach ($items as $item) {
            $stockItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ];
        }
        
        $validation = $this->stockService->validateStock($stockItems);
        
        if (!$validation['valid']) {
            return [
                'valid' => false,
                'message' => 'Some items are no longer available',
                'errors' => $validation['errors'],
            ];
        }
        
        return ['valid' => true];
    }
}
