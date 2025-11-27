<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Core\Application;
use Core\Database;

/**
 * Profile Service
 * 
 * Handles user profile management operations.
 */
class ProfileService
{
    private ?Database $db = null;

    /**
     * Get database connection
     */
    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->db = $app->db();
            }
        }
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection not available');
        }
        
        return $this->db;
    }

    /**
     * Get user profile data
     * 
     * @return array<string, mixed>|null
     */
    public function getProfile(int $userId): ?array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return null;
        }
        
        return [
            'id' => $user->getKey(),
            'name' => $user->attributes['name'],
            'email' => $user->attributes['email'],
            'phone' => $user->attributes['phone'],
            'avatar' => $user->getAvatarUrl(),
            'email_verified' => $user->isEmailVerified(),
            'created_at' => $user->attributes['created_at'],
            'updated_at' => $user->attributes['updated_at'],
        ];
    }

    /**
     * Update user profile
     * 
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string}
     */
    public function updateProfile(int $userId, array $data): array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Update basic info
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }
        
        // Handle email change
        if (isset($data['email']) && $data['email'] !== $user->attributes['email']) {
            // Check if email already exists
            $existingUser = User::findByEmail($data['email']);
            if ($existingUser !== null) {
                return ['success' => false, 'message' => 'Email is already in use'];
            }
            
            $user->email = $data['email'];
            // Reset email verification
            $user->email_verified_at = null;
            
            // Optionally trigger verification email here
        }
        
        $user->save();
        
        // Update session data
        $this->updateSessionUser($user);
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }

    /**
     * Change user password
     * 
     * @return array{success: bool, message: string}
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters'];
        }
        
        // Update password
        $user->password = $newPassword;
        $user->save();
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    /**
     * Upload user avatar
     * 
     * @param array<string, mixed> $file
     * @return array{success: bool, message: string, avatar?: string}
     */
    public function uploadAvatar(int $userId, array $file): array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }
        
        // Check file size (max 2MB)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size must be less than 2MB'];
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP'];
        }
        
        // Generate unique filename
        $extension = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        
        // Create upload directory if not exists
        $uploadDir = Application::getInstance()?->basePath() . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . '/' . $filename;
        
        // Delete old avatar if exists
        $oldAvatar = $user->attributes['avatar'] ?? null;
        if ($oldAvatar && file_exists($uploadDir . '/' . $oldAvatar)) {
            unlink($uploadDir . '/' . $oldAvatar);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save avatar'];
        }
        
        // Update user
        $user->avatar = $filename;
        $user->save();
        
        // Update session
        $this->updateSessionUser($user);
        
        return [
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'avatar' => $user->getAvatarUrl(),
        ];
    }

    /**
     * Delete user avatar
     * 
     * @return array{success: bool, message: string}
     */
    public function deleteAvatar(int $userId): array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $avatar = $user->attributes['avatar'] ?? null;
        
        if ($avatar) {
            // Delete file
            $uploadDir = Application::getInstance()?->basePath() . '/public/uploads/avatars';
            $avatarPath = $uploadDir . '/' . $avatar;
            
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }
        
        // Clear avatar in database
        $user->avatar = null;
        $user->save();
        
        // Update session
        $this->updateSessionUser($user);
        
        return ['success' => true, 'message' => 'Avatar removed successfully'];
    }

    /**
     * Delete user account
     * 
     * @return array{success: bool, message: string}
     */
    public function deleteAccount(int $userId, string $password): array
    {
        $user = User::find($userId);
        
        if ($user === null) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Verify password
        if (!$user->verifyPassword($password)) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }
        
        // Delete avatar if exists
        $avatar = $user->attributes['avatar'] ?? null;
        if ($avatar) {
            $uploadDir = Application::getInstance()?->basePath() . '/public/uploads/avatars';
            $avatarPath = $uploadDir . '/' . $avatar;
            
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }
        
        // Delete user (cascade will handle related records)
        $user->delete();
        
        return ['success' => true, 'message' => 'Account deleted successfully'];
    }

    /**
     * Get account statistics
     * 
     * @return array<string, mixed>
     */
    public function getAccountStats(int $userId): array
    {
        // Get order stats
        $orderStats = $this->db()->selectOne(
            "SELECT COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_spent 
             FROM orders WHERE user_id = ?",
            [$userId]
        );
        
        // Get wishlist count
        $wishlistCount = $this->db()->selectOne(
            "SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?",
            [$userId]
        );
        
        // Get review count
        $reviewCount = $this->db()->selectOne(
            "SELECT COUNT(*) as count FROM reviews WHERE user_id = ?",
            [$userId]
        );
        
        // Get address count
        $addressCount = $this->db()->selectOne(
            "SELECT COUNT(*) as count FROM addresses WHERE user_id = ?",
            [$userId]
        );
        
        return [
            'total_orders' => (int) ($orderStats['total_orders'] ?? 0),
            'total_spent' => (float) ($orderStats['total_spent'] ?? 0),
            'wishlist_count' => (int) ($wishlistCount['count'] ?? 0),
            'review_count' => (int) ($reviewCount['count'] ?? 0),
            'address_count' => (int) ($addressCount['count'] ?? 0),
        ];
    }

    /**
     * Update session user data
     */
    private function updateSessionUser(User $user): void
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return;
        }
        
        $session->set('user', [
            'id' => $user->getKey(),
            'name' => $user->getFullName(),
            'email' => $user->attributes['email'],
            'avatar' => $user->getAvatarUrl(),
            'is_admin' => $user->isAdmin(),
            'is_staff' => $user->isStaff(),
        ]);
    }
}
