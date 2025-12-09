<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Enums\UserRole;
use Core\Application;
use Core\Database;

/**
 * Authentication Service
 *
 * Handles user authentication, registration, session management, and email verification.
 * Supports both MySQL and MongoDB backends.
 */
class AuthService
{
    private ?Database $db = null;

    private function db(): Database
    {
        if ($this->db === null) {
            $app = Application::getInstance();
            if ($app !== null) $this->db = $app->db();
        }
        if ($this->db === null) throw new \RuntimeException('Database connection not available');
        return $this->db;
    }

    /**
     * Attempt login with email & password
     */
    public function attempt(string $email, string $password, bool $remember = false): array
    {
        $user = User::authenticate($email, $password);
        if ($user === null) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        $this->login($user, $remember);
        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
    }

    /**
     * Log user in and store session info
     */
    public function login(User $user, bool $remember = false): void
    {
        $app = Application::getInstance();
        $session = $app?->session();
        if ($session === null) return;

        $session->set('user_id', $user->getKey());
        $session->set('user', [
            'id' => $user->getKey(),
            'name' => $user->getFullName(),
            'email' => $user->attributes['email'] ?? '',
            'avatar' => $user->getAvatarUrl(),
            'is_admin' => $user->isAdmin(),
            'is_staff' => $user->isStaff(),
        ]);

        if ($remember) {
            $token = $user->generateRememberToken();
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

    public function loginUser(User $user): void
    {
        $this->login($user, false);
    }

    public function register(array $data): array
    {
        $roleData = null;

        $roleData = $this->db()->selectOne(
            "SELECT id FROM roles WHERE name = ?",
            [UserRole::CUSTOMER->value]
        );

        if ($roleData === null) {
            return ['success' => false, 'message' => 'Registration is not available at this time.'];
        }

        $user = User::create([
            'role_id' => (string) ($roleData['id'] ?? $roleData['_id'] ?? ''),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // Model should hash it
            'status' => 'active',
        ]);

        return ['success' => true, 'message' => 'Registration successful.', 'user' => $user];
    }

    public function logout(): void
    {
        $app = Application::getInstance();
        $session = $app?->session();
        if ($session === null) return;

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
        }

        $session->destroy();
    }

    public function check(): bool
    {
        $app = Application::getInstance();
        $session = $app?->session();
        return $session?->has('user_id') ?? false;
    }

    public function user(): ?User
    {
        $app = Application::getInstance();
        $session = $app?->session();
        if ($session === null || !$session->has('user_id')) return null;

        $userId = $session->get('user_id');
        return User::find($userId);
    }

    public function id(): int|string|null
    {
        $app = Application::getInstance();
        $session = $app?->session();
        if ($session === null) return null;

        return $session->get('user_id');
    }

    public function verifyEmail(string $token): array
    {
        $verification = $this->db()->selectOne(
            "SELECT * FROM email_verifications WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$token]
        );

        if ($verification === null) {
            return ['success' => false, 'message' => 'This verification link is invalid or has expired.'];
        }

        $user = User::find($verification['user_id'] ?? null);
        if ($user === null) return ['success' => false, 'message' => 'User not found.'];

        $user->markEmailAsVerified();

        $this->db()->delete('email_verifications', ['user_id' => $verification['user_id']]);

        return ['success' => true, 'message' => 'Email verified successfully.'];
    }

    public function sendVerificationEmail(User $user): bool
    {
        $token = bin2hex(random_bytes(32));

        $this->db()->delete('email_verifications', ['user_id' => $user->getKey()]);
        $this->db()->insert('email_verifications', [
            'user_id' => $user->getKey(),
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function checkRememberToken(): bool
    {
        if (!isset($_COOKIE['remember_token'])) return false;
        $token = $_COOKIE['remember_token'];

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
