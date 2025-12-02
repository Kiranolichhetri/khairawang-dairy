<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Exceptions\ForbiddenException;
use App\Models\User;
use App\Enums\UserRole;

/**
 * Admin Middleware
 * 
 * Ensures the user has admin/staff privileges before accessing protected routes. 
 */
class AdminMiddleware implements Middleware
{
    /**
     * Minimum role level required (default: staff)
     */
    private UserRole $requiredRole;

    public function __construct()
    {
        $this->requiredRole = UserRole::STAFF;
    }

    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            throw new ForbiddenException('Application not initialized');
        }
        
        $session = $app->session();
        
        // Check if user is authenticated
        if (! $session->has('user_id')) {
            if ($request->expectsJson()) {
                return Response::error('Unauthorized', 401);
            }
            
            return Response::redirect('/login');
        }
        
        // Get user and check role
        $userId = $session->get('user_id');
        $user = User::find($userId);
        
        if ($user === null) {
            $session->destroy();
            
            if ($request->expectsJson()) {
                return Response::error('User not found', 401);
            }
            
            return Response::redirect('/login');
        }
        
        // Check if user has required role
        $role = $user->getRole();
        
        if ($role === null || !$role->canAccess($this->requiredRole)) {
            if ($request->expectsJson()) {
                return Response::error('Access denied', 403);
            }
            
            // Flash error message
            $session->error('You do not have permission to access this area.');
            
            return Response::redirect('/');
        }
        
        // Check if user is active
        if (!$user->isActive()) {
            $session->destroy();
            
            if ($request->expectsJson()) {
                return Response::error('Account is inactive', 403);
            }
            
            return Response::redirect('/login');
        }
        
        return $next($request);
    }

    /**
     * Set required role
     */
    public function setRequiredRole(UserRole $role): self
    {
        $this->requiredRole = $role;
        return $this;
    }

    /**
     * Create middleware instance requiring admin role
     */
    public static function admin(): self
    {
        $instance = new self();
        $instance->requiredRole = UserRole::ADMIN;
        return $instance;
    }

    /**
     * Create middleware instance requiring manager role
     */
    public static function manager(): self
    {
        $instance = new self();
        $instance->requiredRole = UserRole::MANAGER;
        return $instance;
    }

    /**
     * Create middleware instance requiring staff role
     */
    public static function staff(): self
    {
        $instance = new self();
        $instance->requiredRole = UserRole::STAFF;
        return $instance;
    }
}
