<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Exceptions\UnauthorizedException;

/**
 * Authentication Middleware
 * 
 * Ensures the user is authenticated before accessing protected routes.
 */
class AuthMiddleware implements Middleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            throw new UnauthorizedException('Application not initialized');
        }
        
        $session = $app->session();
        
        // Check if user is authenticated
        if (!$session->has('user_id')) {
            // Store intended URL for redirect after login
            if (!$request->isAjax() && !$request->expectsJson()) {
                $session->setIntendedUrl($request->fullUrl());
            }
            
            // Return appropriate response
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            
            return Response::redirect('/login');
        }
        
        // Verify user still exists and is active
        $userId = $session->get('user_id');
        
        // Optionally check user status (can be cached for performance)
        // $user = \App\Models\User::find($userId);
        // if ($user === null || !$user->isActive()) {
        //     $session->destroy();
        //     return Response::redirect('/login');
        // }
        
        return $next($request);
    }
}
