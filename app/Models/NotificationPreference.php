<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Notification Preference Model
 */
class NotificationPreference extends Model
{
    protected static string $table = 'notification_preferences';
    
    protected static array $fillable = [
        'user_id',
        'email_orders',
        'email_promotions',
        'email_newsletter',
        'sms_orders',
        'sms_promotions',
        'push_enabled',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'email_orders' => 'boolean',
        'email_promotions' => 'boolean',
        'email_newsletter' => 'boolean',
        'sms_orders' => 'boolean',
        'sms_promotions' => 'boolean',
        'push_enabled' => 'boolean',
    ];

    /**
     * Get preferences for a user
     */
    public static function getForUser(int $userId): ?self
    {
        return static::findBy('user_id', $userId);
    }

    /**
     * Get or create preferences for a user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        $pref = static::getForUser($userId);
        
        if ($pref !== null) {
            return $pref;
        }
        
        // Create default preferences
        return static::create([
            'user_id' => $userId,
            'email_orders' => true,
            'email_promotions' => true,
            'email_newsletter' => true,
            'sms_orders' => true,
            'sms_promotions' => false,
            'push_enabled' => true,
        ]);
    }

    /**
     * Check if email orders are enabled
     */
    public function emailOrdersEnabled(): bool
    {
        return (bool) ($this->attributes['email_orders'] ?? true);
    }

    /**
     * Check if email promotions are enabled
     */
    public function emailPromotionsEnabled(): bool
    {
        return (bool) ($this->attributes['email_promotions'] ?? true);
    }

    /**
     * Check if email newsletter is enabled
     */
    public function emailNewsletterEnabled(): bool
    {
        return (bool) ($this->attributes['email_newsletter'] ?? true);
    }

    /**
     * Check if SMS orders are enabled
     */
    public function smsOrdersEnabled(): bool
    {
        return (bool) ($this->attributes['sms_orders'] ?? true);
    }

    /**
     * Check if SMS promotions are enabled
     */
    public function smsPromotionsEnabled(): bool
    {
        return (bool) ($this->attributes['sms_promotions'] ?? false);
    }

    /**
     * Check if push notifications are enabled
     */
    public function pushEnabled(): bool
    {
        return (bool) ($this->attributes['push_enabled'] ?? true);
    }

    /**
     * Enable all notifications
     */
    public function enableAll(): bool
    {
        $this->email_orders = true;
        $this->email_promotions = true;
        $this->email_newsletter = true;
        $this->sms_orders = true;
        $this->sms_promotions = true;
        $this->push_enabled = true;
        return $this->save();
    }

    /**
     * Disable all notifications
     */
    public function disableAll(): bool
    {
        $this->email_orders = false;
        $this->email_promotions = false;
        $this->email_newsletter = false;
        $this->sms_orders = false;
        $this->sms_promotions = false;
        $this->push_enabled = false;
        return $this->save();
    }
}
