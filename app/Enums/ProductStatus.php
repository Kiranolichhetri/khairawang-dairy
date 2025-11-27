<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Product Status Enum
 * 
 * Represents the publication status of a product.
 */
enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    /**
     * Get Nepali label
     */
    public function labelNe(): string
    {
        return match($this) {
            self::DRAFT => 'मस्यौदा',
            self::PUBLISHED => 'प्रकाशित',
            self::ARCHIVED => 'संग्रहित',
        };
    }

    /**
     * Get CSS color class
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'warning',
        };
    }

    /**
     * Check if product is visible to customers
     */
    public function isVisible(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Check if product can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PUBLISHED,
        ], true);
    }

    /**
     * Check if product can be published
     */
    public function canPublish(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Check if product can be archived
     */
    public function canArchive(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Check if product can be restored from archive
     */
    public function canRestore(): bool
    {
        return $this === self::ARCHIVED;
    }

    /**
     * Get next possible statuses
     * 
     * @return array<self>
     */
    public function nextStatuses(): array
    {
        return match($this) {
            self::DRAFT => [self::PUBLISHED],
            self::PUBLISHED => [self::DRAFT, self::ARCHIVED],
            self::ARCHIVED => [self::DRAFT],
        };
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
     * Get active statuses (not archived)
     * 
     * @return array<self>
     */
    public static function active(): array
    {
        return [
            self::DRAFT,
            self::PUBLISHED,
        ];
    }
}
