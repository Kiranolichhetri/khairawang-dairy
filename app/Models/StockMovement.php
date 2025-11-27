<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Stock Movement Model
 * 
 * Tracks all inventory changes for audit trail.
 */
class StockMovement extends Model
{
    protected static string $table = 'stock_movements';
    
    protected static array $fillable = [
        'product_id',
        'variant_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'stock_before',
        'stock_after',
        'created_by',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'quantity' => 'integer',
        'reference_id' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Stock movement types
     */
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_RESERVED = 'reserved';
    public const TYPE_RELEASED = 'released';

    /**
     * Reference types
     */
    public const REF_ORDER = 'order';
    public const REF_RETURN = 'return';
    public const REF_ADJUSTMENT = 'adjustment';
    public const REF_PURCHASE = 'purchase';

    /**
     * Get the product for this movement
     * 
     * @return array<string, mixed>|null
     */
    public function product(): ?array
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user who created this movement
     * 
     * @return array<string, mixed>|null
     */
    public function createdBy(): ?array
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->attributes['type'] ?? '') {
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_RESERVED => 'Reserved',
            self::TYPE_RELEASED => 'Released',
            default => 'Unknown',
        };
    }

    /**
     * Get type badge class (for UI)
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->attributes['type'] ?? '') {
            self::TYPE_IN => 'bg-green-100 text-green-800',
            self::TYPE_OUT => 'bg-red-100 text-red-800',
            self::TYPE_ADJUSTMENT => 'bg-yellow-100 text-yellow-800',
            self::TYPE_RESERVED => 'bg-blue-100 text-blue-800',
            self::TYPE_RELEASED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if movement is positive (adds stock)
     */
    public function isPositive(): bool
    {
        return in_array($this->attributes['type'] ?? '', [
            self::TYPE_IN,
            self::TYPE_RELEASED,
        ], true);
    }

    /**
     * Get movements for a product
     * 
     * @return array<self>
     */
    public static function forProduct(int $productId, int $limit = 50): array
    {
        $rows = static::query()
            ->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get recent movements
     * 
     * @return array<self>
     */
    public static function recent(int $limit = 50): array
    {
        $rows = static::query()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Create a stock in movement
     */
    public static function stockIn(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?self {
        $product = Product::find($productId);
        
        if ($product === null) {
            return null;
        }
        
        $stockBefore = (int) ($product->attributes['stock'] ?? 0);
        $stockAfter = $stockBefore + $quantity;
        
        return static::create([
            'product_id' => $productId,
            'type' => self::TYPE_IN,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'created_by' => $createdBy,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create a stock out movement
     */
    public static function stockOut(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?self {
        $product = Product::find($productId);
        
        if ($product === null) {
            return null;
        }
        
        $stockBefore = (int) ($product->attributes['stock'] ?? 0);
        $stockAfter = max(0, $stockBefore - $quantity);
        
        return static::create([
            'product_id' => $productId,
            'type' => self::TYPE_OUT,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'created_by' => $createdBy,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create an adjustment movement
     */
    public static function adjustment(
        int $productId,
        int $quantity,
        ?string $notes = null,
        ?int $createdBy = null
    ): ?self {
        $product = Product::find($productId);
        
        if ($product === null) {
            return null;
        }
        
        $stockBefore = (int) ($product->attributes['stock'] ?? 0);
        $stockAfter = $stockBefore + $quantity;
        
        return static::create([
            'product_id' => $productId,
            'type' => self::TYPE_ADJUSTMENT,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'created_by' => $createdBy,
            'reference_type' => self::REF_ADJUSTMENT,
        ]);
    }
}
