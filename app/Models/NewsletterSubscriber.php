<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Newsletter Subscriber Model
 */
class NewsletterSubscriber extends Model
{
    protected static string $table = 'newsletter_subscribers';
    
    protected static array $fillable = [
        'email',
        'name',
        'is_active',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Find subscriber by email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::findBy('email', $email);
    }

    /**
     * Find subscriber by unsubscribe token
     */
    public static function findByToken(string $token): ?self
    {
        return static::findBy('unsubscribe_token', $token);
    }

    /**
     * Get all active subscribers
     * 
     * @return array<self>
     */
    public static function getActive(): array
    {
        return static::findAllBy('is_active', true);
    }

    /**
     * Count subscribers
     */
    public static function count(bool $activeOnly = true): int
    {
        $query = static::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->count();
    }

    /**
     * Check if subscriber is active
     */
    public function isActive(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }

    /**
     * Unsubscribe
     */
    public function unsubscribe(): bool
    {
        $this->is_active = false;
        $this->unsubscribed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(): bool
    {
        $this->is_active = true;
        $this->unsubscribed_at = null;
        $this->subscribed_at = date('Y-m-d H:i:s');
        return $this->save();
    }
}
