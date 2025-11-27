<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Guest Middleware
 * 
 * Ensures the user is NOT authenticated before accessing certain routes (login, register).
 */
class GuestMiddleware implements Middleware
{
    /**
     * URL to redirect authenticated users to
     */
    private string $redirectTo;

    public function __construct(string $redirectTo = '/')
    {
        $this->redirectTo = $redirectTo;
    }

    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            return $next($request);
        }
        
        $session = $app->session();
        
        // If user is authenticated, redirect them away from guest pages
        if ($session->has('user_id')) {
            // Check if user is admin and redirect to admin dashboard
            $user = $session->get('user', []);
            $redirectUrl = ($user['is_staff'] ?? false) ? '/admin' : $this->redirectTo;
            
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Already authenticated',
                    'redirect' => $redirectUrl,
                ], 302);
            }
            
            return Response::redirect($redirectUrl);
        }
        
        return $next($request);
    }
}
