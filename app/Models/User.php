<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use Core\Application;
use App\Enums\UserRole;

/**
 * User Model
 * 
 * Represents a user in the system with authentication and role capabilities.
 * Supports both MySQL and MongoDB backends.
 */
class User extends Model
{
    protected static string $table = 'users';

    protected static array $fillable = [
        'role_id',
        'email',
        'password',
        'name',
        'phone',
        'avatar',
        'google_id',
        'email_verified_at',
        'status',
        'remember_token',
    ];

    protected static array $hidden = [
        'password',
        'remember_token',
    ];

    protected static array $casts = [
        'id' => 'string',
        'role_id' => 'string',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create a User instance from MongoDB document
     */
    public static function fromMongo(array $data): self
    {
        $user = new self();
        $user->attributes = $data;
        $user->id = (string) ($data['_id'] ?? $data['id'] ?? null);
        $user->remember_token = $data['remember_token'] ?? null;
        return $user;
    }

    /**
     * Hash password before saving
     */
    protected function setPasswordAttribute(string $value): string
    {
        return password_hash($value, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password'] ?? '');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? '') === 'active';
    }

    /**
     * Check if user email is verified
     */
    public function isEmailVerified(): bool
    {
        return $this->attributes['email_verified_at'] !== null;
    }

    /**
     * Get user role
     */
    public function getRole(): ?UserRole
    {
        $roleData = $this->belongsTo(Role::class, 'role_id');

        if ($roleData === null) {
            return null;
        }

        return UserRole::tryFrom($roleData['name'] ?? '');
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        $role = $this->getRole();
        return $role?->isAdmin() ?? false;
    }

    /**
     * Check if user is staff (admin, manager, or staff)
     */
    public function isStaff(): bool
    {
        $role = $this->getRole();
        return $role?->isStaff() ?? false;
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $role = $this->getRole();
        return $role?->hasPermission($permission) ?? false;
    }

    /**
     * Get user's orders
     * 
     * @return array<int, array<string, mixed>>
     */
    public function orders(): array
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get user's cart
     * 
     * @return array<string, mixed>|null
     */
    public function cart(): ?array
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::findBy('email', $email);
    }

    /**
     * Authenticate user
     */
    public static function authenticate(string $email, string $password): ?self
    {
        $user = static::findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!$user->verifyPassword($password)) {
            return null;
        }

        if (!$user->isActive()) {
            return null;
        }

        return $user;
    }

    /**
     * Generate remember token
     */
    public function generateRememberToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->remember_token = hash('sha256', $token);
        $this->save();

        return $token;
    }

    /**
     * Verify remember token
     */
    public function verifyRememberToken(string $token): bool
    {
        return hash_equals($this->remember_token ?? '', hash('sha256', $token));
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrl(): string
    {
        $avatar = $this->attributes['avatar'] ?? null;

        if ($avatar) {
            if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
                return $avatar;
            }
            return '/uploads/avatars/' . $avatar;
        }

        $hash = md5(strtolower(trim($this->attributes['email'] ?? '')));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Get users by role
     * 
     * @return array<self>
     */
    public static function findByRole(UserRole $role): array
    {
        $app = Application::getInstance();

        if ($app?->isMongoDbDefault()) {
            $roleData = static::mongo()->findOne('roles', ['name' => $role->value]);

            if ($roleData === null) {
                return [];
            }

            return static::findAllBy('role_id', (string) ($roleData['_id'] ?? $roleData['id']));
        }

        $roleData = self::db()->table('roles')->where('name', $role->value)->first();

        if ($roleData === null) {
            return [];
        }

        return static::findAllBy('role_id', $roleData['id']);
    }
}

/**
 * Role Model (for reference)
 */
class Role extends Model
{
    protected static string $table = 'roles';

    protected static array $fillable = [
        'name',
        'permissions',
    ];

    protected static array $casts = [
        'id' => 'string',
        'permissions' => 'json',
    ];
}
