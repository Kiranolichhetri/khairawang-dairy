<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User Role Enum
 * 
 * Represents the different user roles in the system.
 */
enum UserRole: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case STAFF = 'staff';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::CUSTOMER => 'Customer',
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::STAFF => 'Staff',
        };
    }

    /**
     * Get Nepali label
     */
    public function labelNe(): string
    {
        return match($this) {
            self::CUSTOMER => 'ग्राहक',
            self::ADMIN => 'प्रशासक',
            self::MANAGER => 'व्यवस्थापक',
            self::STAFF => 'कर्मचारी',
        };
    }

    /**
     * Check if role has admin access
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role has staff access (admin, manager, or staff)
     */
    public function isStaff(): bool
    {
        return in_array($this, [
            self::ADMIN,
            self::MANAGER,
            self::STAFF,
        ], true);
    }

    /**
     * Check if role is customer
     */
    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    /**
     * Get permission level (higher = more access)
     */
    public function level(): int
    {
        return match($this) {
            self::CUSTOMER => 0,
            self::STAFF => 10,
            self::MANAGER => 50,
            self::ADMIN => 100,
        };
    }

    /**
     * Check if role can access given role level
     */
    public function canAccess(self $requiredRole): bool
    {
        return $this->level() >= $requiredRole->level();
    }

    /**
     * Get default permissions for role
     * 
     * @return array<string>
     */
    public function permissions(): array
    {
        return match($this) {
            self::CUSTOMER => [
                'view_products',
                'place_order',
                'view_own_orders',
                'update_profile',
            ],
            self::STAFF => [
                'view_products',
                'view_orders',
                'update_order_status',
                'view_customers',
            ],
            self::MANAGER => [
                'view_products',
                'manage_products',
                'view_orders',
                'manage_orders',
                'view_customers',
                'view_reports',
            ],
            self::ADMIN => [
                '*', // All permissions
            ],
        };
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions();
        
        return in_array('*', $permissions, true) 
            || in_array($permission, $permissions, true);
    }

    /**
     * Get all roles as array for select options
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
     * Get staff roles (non-customer)
     * 
     * @return array<self>
     */
    public static function staffRoles(): array
    {
        return [
            self::STAFF,
            self::MANAGER,
            self::ADMIN,
        ];
    }
}
