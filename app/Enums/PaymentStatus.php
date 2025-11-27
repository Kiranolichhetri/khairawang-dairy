<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Payment Status Enum
 * 
 * Represents the various states a payment can be in.
 */
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }

    /**
     * Get Nepali label
     */
    public function labelNe(): string
    {
        return match($this) {
            self::PENDING => 'पर्खिरहेको',
            self::PAID => 'भुक्तानी भएको',
            self::FAILED => 'असफल',
            self::REFUNDED => 'फिर्ता भएको',
        };
    }

    /**
     * Get CSS color class
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if payment is in final state
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::PAID,
            self::FAILED,
            self::REFUNDED,
        ], true);
    }

    /**
     * Check if payment can be refunded
     */
    public function canRefund(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if payment can be retried
     */
    public function canRetry(): bool
    {
        return $this === self::FAILED;
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
}
