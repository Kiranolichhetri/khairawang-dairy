<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\NewsletterService;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Newsletter Controller
 * 
 * Handles newsletter subscription and unsubscription.
 */
class NewsletterController
{
    private NewsletterService $newsletterService;

    public function __construct(?NewsletterService $newsletterService = null)
    {
        $this->newsletterService = $newsletterService ?? new NewsletterService();
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request): Response
    {
        $email = trim($request->input('email', '') ?? '');
        $name = trim($request->input('name', '') ?? '');
        
        if (empty($email)) {
            if ($request->expectsJson()) {
                return Response::error('Email address is required', 400);
            }
            
            $app = Application::getInstance();
            $session = $app?->session();
            $session?->error('Please enter your email address.');
            
            return Response::redirect('/');
        }
        
        $result = $this->newsletterService->subscribe($email, $name ?: null);
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 400);
        }
        
        $app = Application::getInstance();
        $session = $app?->session();
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        $referer = $request->server('HTTP_REFERER', '/');
        return Response::redirect($referer);
    }

    /**
     * Unsubscribe from newsletter using token
     */
    public function unsubscribe(Request $request, string $token): Response
    {
        if (empty($token)) {
            return Response::view('newsletter.unsubscribe', [
                'title' => 'Unsubscribe',
                'success' => false,
                'message' => 'Invalid unsubscribe link',
            ]);
        }
        
        $result = $this->newsletterService->unsubscribe($token);
        
        return Response::view('newsletter.unsubscribe', [
            'title' => 'Unsubscribe',
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Show subscription preferences (for authenticated users)
     */
    public function preferences(Request $request): Response
    {
        $app = Application::getInstance();
        $session = $app?->session();
        $user = $session?->get('user');
        
        if ($user === null) {
            return Response::redirect('/login');
        }
        
        return Response::view('newsletter.preferences', [
            'title' => 'Newsletter Preferences',
            'user' => $user,
        ]);
    }

    /**
     * Update subscription preferences
     */
    public function updatePreferences(Request $request): Response
    {
        $app = Application::getInstance();
        $session = $app?->session();
        $user = $session?->get('user');
        
        if ($user === null) {
            return Response::redirect('/login');
        }
        
        $email = $user['email'] ?? '';
        $subscribe = $request->input('subscribe', false);
        
        if ($subscribe) {
            $result = $this->newsletterService->subscribe($email, $user['name'] ?? null);
        } else {
            $result = $this->newsletterService->unsubscribeByEmail($email);
        }
        
        if ($request->expectsJson()) {
            return Response::json($result);
        }
        
        if ($result['success']) {
            $session?->success($result['message']);
        } else {
            $session?->error($result['message']);
        }
        
        return Response::redirect('/account/notifications/preferences');
    }
}
