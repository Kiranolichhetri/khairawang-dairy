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
     * Get or create cart for current user/session using the SQL-backed Cart model
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
            $price = ($item['sale_price'] ?? 0) > 0 ? (float) ($item['sale_price'] ?? 0) : (float) ($item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $itemTotal = $price * $quantity;
            $subtotal += $itemTotal;

            $images = [];
            if (!empty($item['images'])) {
                // images may be JSON string or array depending on source (MongoCart encodes arrays as JSON in some flows)
                if (is_string($item['images'])) {
                    $decoded = json_decode($item['images'], true);
                    $images = is_array($decoded) ? $decoded : [];
                } elseif (is_array($item['images'])) {
                    $images = $item['images'];
                }
            }

            $primaryImage = '/assets/images/product-placeholder.png';
            if (!empty($images)) {
                $first = $images[0];
                if (is_string($first) && str_starts_with($first, 'http')) {
                    $primaryImage = $first;
                } else {
                    $primaryImage = '/uploads/products/' . $first;
                }
            }

            $cartItems[] = [
                'id' => (string) ($item['id'] ?? ($item['item_id'] ?? '')),
                'product_id' => (string) ($item['product_id'] ?? ''),
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $item['name_en'] ?? ($item['name'] ?? ''),
                'slug' => $item['slug'] ?? '',
                'price' => $price,
                'original_price' => (float) ($item['price'] ?? 0),
                'quantity' => $quantity,
                'stock' => (int) ($item['stock'] ?? 0),
                'total' => $itemTotal,
                'image' => $primaryImage,
            ];
        }

        $shippingCost = $this->calculateShipping($subtotal);
        $total = $subtotal + $shippingCost;

        // Determine count - prefer cart->getItemCount() if available
        $count = 0;
        try {
            $count = method_exists($cart, 'getItemCount') ? $cart->getItemCount() : array_sum(array_column($cartItems, 'quantity'));
        } catch (\Throwable $e) {
            $count = array_sum(array_column($cartItems, 'quantity'));
        }

        return [
            'items' => $cartItems,
            'count' => $count,
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
        // Validate input
        if (empty($productId) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid product or quantity'];
        }

        // Check stock & product availability using StockService
        if (!$this->stockService->hasStock($productId, $quantity)) {
            // Try to get available stock for message
            $available = 0;
            try {
                $product = Product::find($productId);
                if ($product !== null) {
                    $available = $product->attributes['stock'] ?? 0;
                }
            } catch (\Throwable $e) {
                $available = 0;
            }

            return [
                'success' => false,
                'message' => $available > 0 ? "Only {$available} units available" : 'Insufficient stock',
                'available_stock' => $available,
            ];
        }

        $cart = $this->getCart();

        // Attempt to add via cart instance (MongoCart or SQL Cart)
        try {
            $ok = $cart->addItem($productId, $quantity, $variantId);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to add item to cart'];
        }

        if ($ok) {
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
        if (empty($itemId)) {
            return ['success' => false, 'message' => 'Invalid item'];
        }

        $cart = $this->getCart();

        try {
            $ok = $cart->updateItemQuantity($itemId, $quantity);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to update cart'];
        }

        if ($ok) {
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
        if (empty($itemId)) {
            return ['success' => false, 'message' => 'Invalid item'];
        }

        $cart = $this->getCart();

        try {
            $ok = $cart->removeItem($itemId);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to remove item'];
        }

        if ($ok) {
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

        try {
            $ok = $cart->clear();
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to clear cart'];
        }

        if ($ok) {
            return [
                'success' => true,
                'message' => 'Cart cleared',
                'cart' => $this->getCartContents(),
            ];
        }

        return ['success' => false, 'message' => 'Failed to clear cart'];
    }

    /**
     * Sync guest cart items into current cart (used by client to sync localStorage)
     *
     * @param array $items
     * @return array<string, mixed>
     */
    public function syncCart(array $items): array
    {
        if (!is_array($items)) {
            return ['success' => false, 'message' => 'Invalid items format'];
        }

        $cart = $this->getCart();
        $errors = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
            $variantId = $item['variant_id'] ?? null;

            if (empty($productId) || $quantity <= 0) {
                continue;
            }

            // Validate product exists and is published
            $valid = true;
            try {
                // Rely on stockService for validation
                if (!$this->stockService->hasStock($productId, $quantity)) {
                    $valid = false;
                    $errors[] = "Insufficient stock for product {$productId}";
                }
            } catch (\Throwable $e) {
                $valid = false;
            }

            if ($valid) {
                try {
                    $cart->addItem($productId, $quantity, $variantId);
                } catch (\Throwable $e) {
                    $errors[] = "Failed to add product {$productId} to cart";
                }
            }
        }

        return [
            'success' => empty($errors),
            'message' => empty($errors) ? 'Cart synced successfully' : 'Cart synced with some issues',
            'errors' => $errors,
            'cart' => $this->getCartContents(),
        ];
    }

    /**
     * Merge guest cart into user cart after login (SQL path used to use Cart::mergeGuestCart)
     *
     * @param int $userId
     */
    public function mergeGuestCart(int $userId): void
    {
        Cart::mergeGuestCart($userId, $this->getSessionId());
    }

    /**
     * Get cart count for header / badge
     *
     * @return array<string, mixed>
     */
    public function count(): array
    {
        $cart = $this->getCart();

        $count = 0;
        $total = 0.0;
        try {
            $count = method_exists($cart, 'getItemCount') ? $cart->getItemCount() : 0;
            $contents = $this->getCartContents();
            $total = $contents['total'] ?? 0.0;
        } catch (\Throwable $e) {
            $count = 0;
            $total = 0.0;
        }

        return [
            'success' => true,
            'count' => $count,
            'total' => $total,
        ];
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

        // items() should return raw items (product_id, quantity)
        $items = [];
        try {
            $items = $cart->items();
        } catch (\Throwable $e) {
            $items = [];
        }

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