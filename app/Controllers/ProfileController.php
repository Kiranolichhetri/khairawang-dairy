<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProfileService;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Validator;

/**
 * Profile Controller
 * 
 * Handles user profile management.
 */
class ProfileController
{
    private ProfileService $profileService;

    public function __construct()
    {
        $this->profileService = new ProfileService();
    }

    /**
     * Get current user ID from session
     */
    private function getUserId(): ?int
    {
        $session = Application::getInstance()?->session();
        $userId = $session?->get('user_id');
        return $userId ? (int) $userId : null;
    }

    /**
     * Show account dashboard
     */
    public function dashboard(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        $profile = $this->profileService->getProfile($userId);
        $stats = $this->profileService->getAccountStats($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'profile' => $profile,
                    'stats' => $stats,
                ],
            ]);
        }
        
        return Response::view('account.dashboard', [
            'title' => 'Dashboard',
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Show profile page
     */
    public function show(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        $profile = $this->profileService->getProfile($userId);
        $stats = $this->profileService->getAccountStats($userId);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => true,
                'data' => [
                    'profile' => $profile,
                    'stats' => $stats,
                ],
            ]);
        }
        
        return Response::view('account.profile.show', [
            'title' => 'My Profile',
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Show edit profile form
     */
    public function edit(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        $profile = $this->profileService->getProfile($userId);
        
        return Response::view('account.profile.edit', [
            'title' => 'Edit Profile',
            'profile' => $profile,
        ]);
    }

    /**
     * Update profile
     */
    public function update(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'phone' => 'min:10|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            $session?->flashInput($request->all());
            
            return Response::redirect('/account/profile/edit');
        }
        
        $result = $this->profileService->updateProfile($userId, [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ]);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
            return Response::redirect('/account/profile/edit');
        }
        
        return Response::redirect('/account/profile');
    }

    /**
     * Show change password form
     */
    public function changePassword(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            return Response::redirect('/login');
        }
        
        return Response::view('account.profile.password', [
            'title' => 'Change Password',
        ]);
    }

    /**
     * Process password change
     */
    public function updatePassword(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $validator = new Validator($request->all(), [
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            $session = Application::getInstance()?->session();
            $session?->flashErrors($validator->errors());
            
            return Response::redirect('/account/password');
        }
        
        // Check password confirmation
        if ($request->input('new_password') !== $request->input('new_password_confirmation')) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Password confirmation does not match',
                ], 400);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Password confirmation does not match');
            
            return Response::redirect('/account/password');
        }
        
        $result = $this->profileService->changePassword(
            $userId,
            $request->input('current_password'),
            $request->input('new_password')
        );
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
            return Response::redirect('/account/profile');
        } else {
            $session?->error($result['message']);
            return Response::redirect('/account/password');
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $file = $_FILES['avatar'] ?? null;
        
        if ($file === null) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'No file uploaded',
                ], 400);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('No file uploaded');
            
            return Response::redirect('/account/profile/edit');
        }
        
        $result = $this->profileService->uploadAvatar($userId, $file);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/profile/edit');
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $result = $this->profileService->deleteAvatar($userId);
        
        if ($request->expectsJson()) {
            return Response::json($result);
        }
        
        $session = Application::getInstance()?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/profile/edit');
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request): Response
    {
        $userId = $this->getUserId();
        
        if ($userId === null) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            return Response::redirect('/login');
        }
        
        $password = $request->input('password');
        
        if (empty($password)) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Password is required to delete account',
                ], 400);
            }
            
            $session = Application::getInstance()?->session();
            $session?->error('Password is required to delete account');
            
            return Response::redirect('/account/profile');
        }
        
        $result = $this->profileService->deleteAccount($userId, $password);
        
        if ($request->expectsJson()) {
            return Response::json($result, $result['success'] ? 200 : 400);
        }
        
        if ($result['success']) {
            // Logout and redirect to home
            $session = Application::getInstance()?->session();
            $session?->destroy();
            
            return Response::redirect('/');
        }
        
        $session = Application::getInstance()?->session();
        $session?->error($result['message']);
        
        return Response::redirect('/account/profile');
    }
}
