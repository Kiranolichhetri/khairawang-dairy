<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\QueryBuilder;

/**
 * Newsletter Campaign Model
 */
class NewsletterCampaign extends Model
{
    protected static string $table = 'newsletter_campaigns';
    
    protected static array $fillable = [
        'subject',
        'content',
        'sent_count',
        'opened_count',
        'clicked_count',
        'status',
        'scheduled_at',
        'sent_at',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'sent_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Campaign statuses
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';

    /**
     * Get campaigns by status
     * 
     * @return array<self>
     */
    public static function getByStatus(string $status): array
    {
        return static::findAllBy('status', $status);
    }

    /**
     * Get draft campaigns
     * 
     * @return array<self>
     */
    public static function getDrafts(): array
    {
        return static::getByStatus(self::STATUS_DRAFT);
    }

    /**
     * Get sent campaigns
     * 
     * @return array<self>
     */
    public static function getSent(): array
    {
        return static::getByStatus(self::STATUS_SENT);
    }

    /**
     * Check if campaign is draft
     */
    public function isDraft(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_DRAFT;
    }

    /**
     * Check if campaign has been sent
     */
    public function isSent(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_SENT;
    }

    /**
     * Mark campaign as sent
     */
    public function markAsSent(int $sentCount = 0): bool
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = date('Y-m-d H:i:s');
        $this->sent_count = $sentCount;
        return $this->save();
    }

    /**
     * Mark campaign as sending
     */
    public function markAsSending(): bool
    {
        $this->status = self::STATUS_SENDING;
        return $this->save();
    }

    /**
     * Schedule campaign
     */
    public function schedule(string $datetime): bool
    {
        $this->status = self::STATUS_SCHEDULED;
        $this->scheduled_at = $datetime;
        return $this->save();
    }

    /**
     * Get open rate
     */
    public function getOpenRate(): float
    {
        $sent = (int) ($this->attributes['sent_count'] ?? 0);
        $opened = (int) ($this->attributes['opened_count'] ?? 0);
        
        if ($sent === 0) {
            return 0.0;
        }
        
        return round(($opened / $sent) * 100, 2);
    }

    /**
     * Get click rate
     */
    public function getClickRate(): float
    {
        $sent = (int) ($this->attributes['sent_count'] ?? 0);
        $clicked = (int) ($this->attributes['clicked_count'] ?? 0);
        
        if ($sent === 0) {
            return 0.0;
        }
        
        return round(($clicked / $sent) * 100, 2);
    }

    /**
     * Get all campaigns ordered by date
     * 
     * @return array<self>
     */
    public static function getAll(): array
    {
        $rows = static::query()
            ->orderBy('created_at', 'DESC')
            ->get();
        
        return array_map(fn($row) => static::hydrate($row), $rows);
    }
}
