<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Enums\UserRole;
use Core\Application;
use Core\Database;
use Core\MongoDB;

/**
 * Authentication Service
 * 
 * Handles user authentication, registration, session management, and email verification.
 * Supports both MySQL and MongoDB backends.
 */
class AuthService
{
    private ?Database $db = null;
    private ?MongoDB $mongo = null;

    /**
     * Check if MongoDB is the default connection
     */
    private function isMongoDb(): bool
    {
        $app = Application::getInstance();
        return $app?->isMongoDbDefault() ?? false;
    }

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
     * Get MongoDB connection
     */
    private function mongo(): MongoDB
    {
        if ($this->mongo === null) {
            $app = Application::getInstance();
            if ($app !== null) {
                $this->mongo = $app->mongo();
            }
        }
        
        if ($this->mongo === null) {
            throw new \RuntimeException('MongoDB connection not available');
        }
        
        return $this->mongo;
    }

    /**
     * Attempt to authenticate a user
     * 
     * @return array{success: bool, message: string, user?: User}
     */
    public function attempt(string $email, string $password, bool $remember = false): array
    {
        $user = User::authenticate($email, $password);
        
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.',
            ];
        }
        
        // Login the user
        $this->login($user, $remember);
        
        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => $user,
        ];
    }

    /**
     * Login a user
     */
    public function login(User $user, bool $remember = false): void
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return;
        }
        
        // Store user info in session
        $session->set('user_id', $user->getKey());
        $session->set('user', [
            'id' => $user->getKey(),
            'name' => $user->getFullName(),
            'email' => $user->attributes['email'],
            'avatar' => $user->getAvatarUrl(),
            'is_admin' => $user->isAdmin(),
            'is_staff' => $user->isStaff(),
        ]);
        
        // Handle remember me
        if ($remember) {
            $token = $user->generateRememberToken();
            // Set cookie with 30 days expiration
            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }
    }

    /**
     * Login a user without password verification (for OAuth)
     */
    public function loginUser(User $user): void
    {
        $this->login($user, false);
    }

    /**
     * Register a new user
     * 
     * @param array{name: string, email: string, phone: string, password: string} $data
     * @return array{success: bool, message: string, user?: User}
     */
    public function register(array $data): array
    {
        // Get customer role ID
        $roleData = null;
        
        if ($this->isMongoDb()) {
            $roleData = $this->mongo()->findOne('roles', ['name' => UserRole::CUSTOMER->value]);
            if ($roleData !== null) {
                $roleData['id'] = $roleData['_id'] ?? $roleData['id'];
            }
        } else {
            $roleData = $this->db()->selectOne(
                "SELECT id FROM roles WHERE name = ?",
                [UserRole::CUSTOMER->value]
            );
        }
        
        if ($roleData === null) {
            return [
                'success' => false,
                'message' => 'Registration is not available at this time.',
            ];
        }
        
        // Create user
        $user = User::create([
            'role_id' => (string) $roleData['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // Will be hashed by model mutator
            'status' => 'active',
        ]);
        
        return [
            'success' => true,
            'message' => 'Registration successful.',
            'user' => $user,
        ];
    }

    /**
     * Logout the current user
     */
    public function logout(): void
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return;
        }
        
        // Clear remember token cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie(
                'remember_token',
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }
        
        // Destroy session
        $session->destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        return $session?->has('user_id') ?? false;
    }

    /**
     * Get the currently authenticated user
     */
    public function user(): ?User
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null || !$session->has('user_id')) {
            return null;
        }
        
        $userId = $session->get('user_id');
        return User::find($userId);
    }

    /**
     * Get the current user ID
     * 
     * @return int|string|null
     */
    public function id(): int|string|null
    {
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($session === null) {
            return null;
        }
        
        return $session->get('user_id');
    }

    /**
     * Verify email token
     * 
     * @return array{success: bool, message: string}
     */
    public function verifyEmail(string $token): array
    {
        $verification = null;
        
        if ($this->isMongoDb()) {
            // For MongoDB, use $gt for date comparison
            $cutoffDate = new \DateTime('-24 hours');
            $verification = $this->mongo()->findOne('email_verifications', [
                'token' => $token,
                'created_at' => ['$gt' => $cutoffDate->format('Y-m-d H:i:s')]
            ]);
        } else {
            $verification = $this->db()->selectOne(
                "SELECT * FROM email_verifications WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$token]
            );
        }
        
        if ($verification === null) {
            return [
                'success' => false,
                'message' => 'This verification link is invalid or has expired.',
            ];
        }
        
        $user = User::find($verification['user_id']);
        
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }
        
        // Mark email as verified
        $user->markEmailAsVerified();
        
        // Delete verification record
        if ($this->isMongoDb()) {
            $this->mongo()->deleteOne('email_verifications', ['user_id' => $verification['user_id']]);
        } else {
            $this->db()->delete('email_verifications', ['user_id' => $verification['user_id']]);
        }
        
        return [
            'success' => true,
            'message' => 'Email verified successfully.',
        ];
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail(User $user): bool
    {
        // Generate token
        $token = bin2hex(random_bytes(32));
        
        // Delete any existing verification tokens for this user
        if ($this->isMongoDb()) {
            $this->mongo()->deleteMany('email_verifications', ['user_id' => $user->getKey()]);
            
            // Insert new verification record
            $this->mongo()->insertOne('email_verifications', [
                'user_id' => $user->getKey(),
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db()->delete('email_verifications', ['user_id' => $user->getKey()]);
            
            // Insert new verification record
            $this->db()->insert('email_verifications', [
                'user_id' => $user->getKey(),
                'token' => $token,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        // In a real application, send email here
        // For now, we just return true
        return true;
    }

    /**
     * Check remember token and log user in
     */
    public function checkRememberToken(): bool
    {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        
        // Find user with this remember token
        $users = User::all();
        
        foreach ($users as $user) {
            if ($user->verifyRememberToken($token)) {
                $this->login($user, true);
                return true;
            }
        }
        
        return false;
    }
}
