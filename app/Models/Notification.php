<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Notification Model
 */
class Notification extends Model
{
    protected static string $table = 'notifications';
    
    protected static array $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'is_read' => 'boolean',
        'data' => 'json',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    public const TYPE_ORDER_PLACED = 'order_placed';
    public const TYPE_ORDER_SHIPPED = 'order_shipped';
    public const TYPE_ORDER_DELIVERED = 'order_delivered';
    public const TYPE_ORDER_CANCELLED = 'order_cancelled';
    public const TYPE_PAYMENT_RECEIVED = 'payment_received';
    public const TYPE_REVIEW_APPROVED = 'review_approved';
    public const TYPE_PRICE_DROP = 'price_drop';
    public const TYPE_BACK_IN_STOCK = 'back_in_stock';
    public const TYPE_PROMOTION = 'promotion';

    /**
     * Get notifications for a user
     * 
     * @return array<self>
     */
    public static function getForUser(int $userId, int $limit = 50, int $offset = 0, bool $unreadOnly = false): array
    {
        $query = static::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset);
        
        if ($unreadOnly) {
            $query->where('is_read', false);
        }
        
        $rows = $query->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount(int $userId): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return (bool) ($this->attributes['is_read'] ?? false);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }
        
        $this->is_read = true;
        $this->read_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        $this->is_read = false;
        $this->read_at = null;
        return $this->save();
    }

    /**
     * Get the notification data
     * 
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $data = $this->attributes['data'] ?? null;
        
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }
        
        return is_array($data) ? $data : [];
    }

    /**
     * Get notification type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->attributes['type'] ?? '') {
            self::TYPE_ORDER_PLACED => 'Order Placed',
            self::TYPE_ORDER_SHIPPED => 'Order Shipped',
            self::TYPE_ORDER_DELIVERED => 'Order Delivered',
            self::TYPE_ORDER_CANCELLED => 'Order Cancelled',
            self::TYPE_PAYMENT_RECEIVED => 'Payment Received',
            self::TYPE_REVIEW_APPROVED => 'Review Approved',
            self::TYPE_PRICE_DROP => 'Price Drop',
            self::TYPE_BACK_IN_STOCK => 'Back in Stock',
            self::TYPE_PROMOTION => 'Promotion',
            default => 'Notification',
        };
    }

    /**
     * Get notification icon
     */
    public function getIcon(): string
    {
        return match ($this->attributes['type'] ?? '') {
            self::TYPE_ORDER_PLACED => '🛒',
            self::TYPE_ORDER_SHIPPED => '📦',
            self::TYPE_ORDER_DELIVERED => '✅',
            self::TYPE_ORDER_CANCELLED => '❌',
            self::TYPE_PAYMENT_RECEIVED => '💳',
            self::TYPE_REVIEW_APPROVED => '⭐',
            self::TYPE_PRICE_DROP => '💰',
            self::TYPE_BACK_IN_STOCK => '📢',
            self::TYPE_PROMOTION => '🎉',
            default => '🔔',
        };
    }

    /**
     * Get time ago string
     */
    public function getTimeAgo(): string
    {
        $createdAt = $this->attributes['created_at'] ?? '';
        if (empty($createdAt)) {
            return '';
        }
        
        $timestamp = is_string($createdAt) ? strtotime($createdAt) : $createdAt;
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}
