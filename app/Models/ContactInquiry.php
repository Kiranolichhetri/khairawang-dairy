<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

/**
 * Contact Inquiry Model
 */
class ContactInquiry extends Model
{
    protected static string $table = 'contact_inquiries';
    
    protected static array $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at',
    ];
    
    protected static array $casts = [
        'id' => 'integer',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    /**
     * Get inquiries by status
     * 
     * @return array<self>
     */
    public static function getByStatus(string $status): array
    {
        return static::findAllBy('status', $status);
    }

    /**
     * Get new inquiries
     * 
     * @return array<self>
     */
    public static function getNew(): array
    {
        return static::getByStatus(self::STATUS_NEW);
    }

    /**
     * Count new inquiries
     */
    public static function countNew(): int
    {
        return static::query()->where('status', self::STATUS_NEW)->count();
    }

    /**
     * Check if inquiry is new
     */
    public function isNew(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_NEW;
    }

    /**
     * Check if inquiry is resolved
     */
    public function isResolved(): bool
    {
        return ($this->attributes['status'] ?? '') === self::STATUS_RESOLVED;
    }

    /**
     * Check if inquiry has been replied to
     */
    public function hasReply(): bool
    {
        return !empty($this->attributes['admin_reply']);
    }

    /**
     * Mark as in progress
     */
    public function markInProgress(): bool
    {
        $this->status = self::STATUS_IN_PROGRESS;
        return $this->save();
    }

    /**
     * Mark as resolved
     */
    public function markResolved(): bool
    {
        $this->status = self::STATUS_RESOLVED;
        return $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->attributes['status'] ?? '') {
            self::STATUS_NEW => 'New',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            default => 'Unknown',
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->attributes['status'] ?? '') {
            self::STATUS_NEW => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_RESOLVED => 'green',
            default => 'gray',
        };
    }

    /**
     * Get all inquiries ordered by date
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
