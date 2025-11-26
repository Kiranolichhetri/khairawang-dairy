<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

/**
 * Order Model
 * 
 * Represents a customer order with items, shipping, and payment information.
 */
class Order extends Model
{
    protected static string $table = 'orders';
    
    protected static array $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'transaction_id',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'notes',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'subtotal' => 'float',
        'shipping_cost' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get order status enum
     */
    public function getStatus(): OrderStatus
    {
        return OrderStatus::from($this->attributes['status'] ?? 'pending');
    }

    /**
     * Get payment status enum
     */
    public function getPaymentStatus(): PaymentStatus
    {
        return PaymentStatus::from($this->attributes['payment_status'] ?? 'pending');
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->getPaymentStatus()->isSuccessful();
    }

    /**
     * Check if order can be cancelled
     */
    public function canCancel(): bool
    {
        return $this->getStatus()->canCancel();
    }

    /**
     * Update order status
     */
    public function updateStatus(OrderStatus $status): bool
    {
        if (!$this->getStatus()->canTransitionTo($status)) {
            return false;
        }
        
        $this->status = $status->value;
        return $this->save();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(PaymentStatus $status, ?string $transactionId = null): bool
    {
        $this->payment_status = $status->value;
        
        if ($transactionId !== null) {
            $this->transaction_id = $transactionId;
        }
        
        return $this->save();
    }

    /**
     * Cancel order
     */
    public function cancel(): bool
    {
        if (!$this->canCancel()) {
            return false;
        }
        
        // Restore stock for each item
        foreach ($this->items() as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product !== null) {
                $product->increaseStock($item['quantity']);
            }
        }
        
        $this->status = OrderStatus::CANCELLED->value;
        return $this->save();
    }

    /**
     * Get order items
     * 
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        return self::db()->table('order_items')
            ->where('order_id', $this->getKey())
            ->get();
    }

    /**
     * Get order items with product details
     * 
     * @return array<int, array<string, mixed>>
     */
    public function itemsWithProducts(): array
    {
        return self::db()->table('order_items')
            ->select(['order_items.*', 'products.slug', 'products.images'])
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $this->getKey())
            ->get();
    }

    /**
     * Get item count
     */
    public function getItemCount(): int
    {
        $items = $this->items();
        $count = 0;
        
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }

    /**
     * Get order user
     * 
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get customer name
     */
    public function getCustomerName(): string
    {
        $user = $this->user();
        return $user['name'] ?? $this->attributes['shipping_name'] ?? '';
    }

    /**
     * Get formatted shipping address
     */
    public function getFormattedAddress(): string
    {
        $parts = [
            $this->attributes['shipping_address'] ?? '',
            $this->attributes['shipping_city'] ?? '',
        ];
        
        return implode(', ', array_filter($parts));
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'KD';
        $timestamp = date('ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        
        return $prefix . $timestamp . $random;
    }

    /**
     * Create order from cart
     * 
     * @param array<string, mixed> $shippingData
     */
    public static function createFromCart(Cart $cart, array $shippingData): self
    {
        $items = $cart->items();
        
        if (empty($items)) {
            throw new \InvalidArgumentException('Cart is empty');
        }
        
        $subtotal = $cart->getSubtotal();
        $shippingCost = $shippingData['shipping_cost'] ?? 0;
        $discount = $shippingData['discount'] ?? 0;
        $total = $subtotal + $shippingCost - $discount;
        
        // Create order
        $order = static::create([
            'user_id' => $cart->user_id,
            'order_number' => static::generateOrderNumber(),
            'status' => OrderStatus::PENDING->value,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => $shippingData['payment_method'] ?? 'cod',
            'payment_status' => PaymentStatus::PENDING->value,
            'shipping_name' => $shippingData['name'],
            'shipping_email' => $shippingData['email'],
            'shipping_phone' => $shippingData['phone'],
            'shipping_address' => $shippingData['address'],
            'shipping_city' => $shippingData['city'] ?? null,
            'notes' => $shippingData['notes'] ?? null,
        ]);
        
        // Create order items
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product === null) {
                continue;
            }
            
            self::db()->insert('order_items', [
                'order_id' => $order->getKey(),
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'product_name' => $product->getName(),
                'variant_name' => $item['variant_name'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $product->getCurrentPrice(),
                'total' => $product->getCurrentPrice() * $item['quantity'],
            ]);
            
            // Reduce stock
            $product->reduceStock($item['quantity']);
        }
        
        // Clear cart
        $cart->clear();
        
        return $order;
    }

    /**
     * Find by order number
     */
    public static function findByOrderNumber(string $orderNumber): ?self
    {
        return static::findBy('order_number', $orderNumber);
    }

    /**
     * Get orders by user
     * 
     * @return array<self>
     */
    public static function forUser(int $userId): array
    {
        $rows = static::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get recent orders
     * 
     * @return array<self>
     */
    public static function recent(int $limit = 10): array
    {
        $rows = static::query()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get orders by status
     * 
     * @return array<self>
     */
    public static function byStatus(OrderStatus $status): array
    {
        $rows = static::query()
            ->where('status', $status->value)
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get pending orders count
     */
    public static function pendingCount(): int
    {
        return static::query()
            ->where('status', OrderStatus::PENDING->value)
            ->count();
    }

    /**
     * Get today's revenue
     */
    public static function todayRevenue(): float
    {
        $result = self::db()->selectOne(
            "SELECT SUM(total) as revenue FROM orders 
             WHERE payment_status = ? AND DATE(created_at) = CURDATE()",
            [PaymentStatus::PAID->value]
        );
        
        return (float) ($result['revenue'] ?? 0);
    }

    /**
     * Get total revenue
     */
    public static function totalRevenue(): float
    {
        $result = self::db()->selectOne(
            "SELECT SUM(total) as revenue FROM orders WHERE payment_status = ?",
            [PaymentStatus::PAID->value]
        );
        
        return (float) ($result['revenue'] ?? 0);
    }
}
