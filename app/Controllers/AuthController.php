<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Services\GoogleAuthService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Authentication Controller
 * 
 * Handles user login, registration, logout, password reset, email verification, and OAuth.
 */
class AuthController
{
    private AuthService $authService;
    private PasswordResetService $passwordResetService;
    private GoogleAuthService $googleAuth;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->passwordResetService = new PasswordResetService();
        $this->googleAuth = new GoogleAuthService();
    }

    /**
     * Show login page
     */
    public function showLogin(Request $request): Response
    {
        return Response::view('auth.login', [
            'title' => 'Login',
        ]);
    }

    /**
     * Process login
     */
    public function login(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/login');
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = (bool) $request->input('remember', false);

        $result = $this->authService->attempt($email, $password, $remember);

        if (!$result['success']) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error($result['message']);
            $session?->flashInput($request->all());
            
            return Response::redirect('/login');
        }

        $app = Application::getInstance();
        $session = $app?->session();
        
        // Regenerate session for security
        $session?->regenerate();
        
        // Redirect to intended URL or default
        $intendedUrl = $session?->getIntendedUrl('/') ?? '/';
        
        // Check if user is admin and redirect to admin dashboard
        $user = $result['user'] ?? null;
        if ($user && $user->isStaff()) {
            $intendedUrl = '/admin';
        }
        
        $session?->success('Welcome back, ' . htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') . '!');
        
        return Response::redirect($intendedUrl);
    }

    /**
     * Show registration page
     */
    public function showRegister(Request $request): Response
    {
        return Response::view('auth.register', [
            'title' => 'Register',
        ]);
    }

    /**
     * Process registration
     */
    public function register(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'phone' => 'required|min:10|max:20',
            'password' => 'required|min:8',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/register');
        }

        // Check if passwords match
        if ($request->input('password') !== $request->input('password_confirmation')) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['password_confirmation' => ['Passwords do not match.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/register');
        }

        // Check if email already exists
        if (User::findByEmail($request->input('email'))) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['email' => ['This email is already registered.']]);
            $session?->flashInput($request->all());
            
            return Response::redirect('/register');
        }

        $result = $this->authService->register([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => $request->input('password'),
        ]);

        if (!$result['success']) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error($result['message']);
            $session?->flashInput($request->all());
            
            return Response::redirect('/register');
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Registration successful! Please login to continue.');
        
        return Response::redirect('/login');
    }

    /**
     * Logout user
     */
    public function logout(Request $request): Response
    {
        $this->authService->logout();
        
        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('You have been logged out successfully.');
        
        return Response::redirect('/');
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(Request $request): Response
    {
        return Response::view('auth.forgot-password', [
            'title' => 'Forgot Password',
        ]);
    }

    /**
     * Process forgot password request
     */
    public function forgotPassword(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/forgot-password');
        }

        $email = $request->input('email');
        
        // Always show success message for security (don't reveal if email exists)
        $this->passwordResetService->sendResetLink($email);
        
        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('If an account exists with that email, you will receive a password reset link.');
        
        return Response::redirect('/forgot-password');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(Request $request, string $token): Response
    {
        // Verify token is valid
        if (!$this->passwordResetService->isValidToken($token)) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('This password reset link is invalid or has expired.');
            
            return Response::redirect('/forgot-password');
        }
        
        return Response::view('auth.reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    /**
     * Process password reset
     */
    public function resetPassword(Request $request): Response
    {
        $validator = new Validator($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors($validator->errors());
            $token = $request->input('token', '');
            
            return Response::redirect('/reset-password/' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
        }

        // Check if passwords match
        if ($request->input('password') !== $request->input('password_confirmation')) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->flashErrors(['password_confirmation' => ['Passwords do not match.']]);
            $token = $request->input('token', '');
            
            return Response::redirect('/reset-password/' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
        }

        $result = $this->passwordResetService->reset(
            $request->input('email'),
            $request->input('token'),
            $request->input('password')
        );

        if (!$result['success']) {
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error($result['message']);
            
            return Response::redirect('/forgot-password');
        }

        $app = Application::getInstance();
        $session = $app?->session();
        $session?->success('Your password has been reset. Please login with your new password.');
        
        return Response::redirect('/login');
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request, string $token): Response
    {
        $result = $this->authService->verifyEmail($token);
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success('Your email has been verified successfully!');
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/login');
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request): Response
    {
        return Response::redirect($this->googleAuth->getAuthUrl());
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request): Response
    {
        $code = $request->query('code');
        
        if (empty($code)) {
            $app = Application::getInstance();
            $app?->session()->error('Google authentication failed.');
            return Response::redirect('/login');
        }

        try {
            // Get access token
            $tokenData = $this->googleAuth->getAccessToken($code);
            
            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Failed to get access token');
            }

            // Get user info
            $googleUser = $this->googleAuth->getUserInfo($tokenData['access_token']);
            
            if (!isset($googleUser['email'])) {
                throw new \Exception('Failed to get user info');
            }

            // Validate Google ID exists
            $googleId = $googleUser['id'] ?? null;
            if (empty($googleId)) {
                throw new \Exception('Invalid Google user data');
            }

            // Find or create user
            $user = User::findByEmail($googleUser['email']);
            
            if (!$user) {
                // Create new user with random secure password for OAuth users
                $result = $this->authService->register([
                    'name' => $googleUser['name'] ?? $googleUser['email'],
                    'email' => $googleUser['email'],
                    'phone' => '',
                    'password' => bin2hex(random_bytes(32)),
                ]);
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
                
                $user = User::findByEmail($googleUser['email']);
                
                if ($user) {
                    // Update with Google-specific info
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $googleUser['picture'] ?? null,
                        'email_verified_at' => date('Y-m-d H:i:s'), // Auto-verify
                    ]);
                }
            } else {
                // Update google_id if not set
                if (empty($user->attributes['google_id'])) {
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $user->attributes['avatar'] ?? $googleUser['picture'] ?? null,
                    ]);
                }
            }

            if ($user === null) {
                throw new \Exception('Failed to create or find user');
            }

            // Log in the user
            $this->authService->loginUser($user);

            $app = Application::getInstance();
            $session = $app?->session();
            $session?->regenerate();
            $session?->success('Welcome, ' . htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') . '!');

            return Response::redirect('/');
            
        } catch (\Exception $e) {
            $app = Application::getInstance();
            $app?->session()->error('Google authentication failed: ' . $e->getMessage());
            return Response::redirect('/login');
        }
    }
}
