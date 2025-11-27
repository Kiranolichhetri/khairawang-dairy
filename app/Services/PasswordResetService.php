<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Core\Application;
use Core\Database;

/**
 * Password Reset Service
 * 
 * Handles password reset tokens and password reset functionality.
 */
class PasswordResetService
{
    private ?Database $db = null;

    /**
     * Token expiry in hours
     */
    private const TOKEN_EXPIRY_HOURS = 2;

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
     * Generate and store password reset token
     */
    public function createToken(string $email): ?string
    {
        $user = User::findByEmail($email);
        
        if ($user === null) {
            return null;
        }
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        // Delete any existing tokens for this email
        $this->db()->delete('password_resets', ['email' => $email]);
        
        // Insert new token
        $this->db()->insert('password_resets', [
            'email' => $email,
            'token' => $hashedToken,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        
        return $token;
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(string $email): bool
    {
        $token = $this->createToken($email);
        
        if ($token === null) {
            // Don't reveal if email exists
            return true;
        }
        
        // In a real application, send email here
        // For now, we just return true
        // The reset link would be: /reset-password/{$token}
        
        return true;
    }

    /**
     * Check if token is valid
     */
    public function isValidToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);
        
        $record = $this->db()->selectOne(
            "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)",
            [$hashedToken, self::TOKEN_EXPIRY_HOURS]
        );
        
        return $record !== null;
    }

    /**
     * Get email by token
     */
    public function getEmailByToken(string $token): ?string
    {
        $hashedToken = hash('sha256', $token);
        
        $record = $this->db()->selectOne(
            "SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)",
            [$hashedToken, self::TOKEN_EXPIRY_HOURS]
        );
        
        return $record['email'] ?? null;
    }

    /**
     * Reset password
     * 
     * @return array{success: bool, message: string}
     */
    public function reset(string $email, string $token, string $password): array
    {
        $hashedToken = hash('sha256', $token);
        
        // Verify token
        $record = $this->db()->selectOne(
            "SELECT * FROM password_resets WHERE email = ? AND token = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)",
            [$email, $hashedToken, self::TOKEN_EXPIRY_HOURS]
        );
        
        if ($record === null) {
            return [
                'success' => false,
                'message' => 'This password reset token is invalid or has expired.',
            ];
        }
        
        // Find user
        $user = User::findByEmail($email);
        
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }
        
        // Update password
        $user->password = $password; // Will be hashed by model mutator
        $user->save();
        
        // Delete the used token
        $this->db()->delete('password_resets', ['email' => $email]);
        
        return [
            'success' => true,
            'message' => 'Password reset successfully.',
        ];
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpiredTokens(): int
    {
        $result = $this->db()->query(
            "DELETE FROM password_resets WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)",
            [self::TOKEN_EXPIRY_HOURS]
        );
        
        return $result->rowCount();
    }
}
