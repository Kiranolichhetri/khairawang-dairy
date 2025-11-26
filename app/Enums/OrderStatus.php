<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Order Status Enum
 * 
 * Represents the various states an order can be in during its lifecycle.
 */
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::PACKED => 'Packed',
            self::SHIPPED => 'Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::RETURNED => 'Returned',
        };
    }

    /**
     * Get Nepali label
     */
    public function labelNe(): string
    {
        return match($this) {
            self::PENDING => 'पर्खिरहेको',
            self::PROCESSING => 'प्रक्रियामा',
            self::PACKED => 'प्याक गरिएको',
            self::SHIPPED => 'पठाइएको',
            self::OUT_FOR_DELIVERY => 'डेलिभरीमा',
            self::DELIVERED => 'डेलिभर भएको',
            self::CANCELLED => 'रद्द गरिएको',
            self::RETURNED => 'फिर्ता भएको',
        };
    }

    /**
     * Get CSS color class
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::PACKED => 'info',
            self::SHIPPED => 'primary',
            self::OUT_FOR_DELIVERY => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::RETURNED => 'secondary',
        };
    }

    /**
     * Check if order can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PROCESSING,
            self::PACKED,
        ], true);
    }

    /**
     * Check if order is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::CANCELLED,
            self::RETURNED,
        ], true);
    }

    /**
     * Get next possible statuses
     * 
     * @return array<self>
     */
    public function nextStatuses(): array
    {
        return match($this) {
            self::PENDING => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::PACKED, self::CANCELLED],
            self::PACKED => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::OUT_FOR_DELIVERY],
            self::OUT_FOR_DELIVERY => [self::DELIVERED],
            self::DELIVERED => [self::RETURNED],
            self::CANCELLED, self::RETURNED => [],
        };
    }

    /**
     * Check if can transition to given status
     */
    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    /**
     * Get all statuses as array for select options
     * 
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        
        return $options;
    }

    /**
     * Get active order statuses (not final)
     * 
     * @return array<self>
     */
    public static function active(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::PACKED,
            self::SHIPPED,
            self::OUT_FOR_DELIVERY,
        ];
    }
}
