<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Application;
use Core\Exceptions\ValidationException;

/**
 * CSRF Protection Middleware
 * 
 * Validates CSRF tokens on POST, PUT, PATCH, DELETE requests.
 */
class CsrfMiddleware implements Middleware
{
    /**
     * URIs that should be excluded from CSRF verification
     * 
     * @var array<string>
     */
    private array $except = [
        '/api/webhook/*',
        '/api/payment/callback',
    ];

    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        // Skip CSRF check for safe methods
        if ($this->isReadingMethod($request)) {
            return $next($request);
        }
        
        // Skip for excluded URIs
        if ($this->isExcluded($request)) {
            return $next($request);
        }
        
        // Validate CSRF token
        if (!$this->validateToken($request)) {
            if ($request->expectsJson()) {
                return Response::error('CSRF token mismatch', 419);
            }
            
            throw new ValidationException(
                ['_csrf_token' => ['CSRF token mismatch']],
                'CSRF token mismatch'
            );
        }
        
        return $next($request);
    }

    /**
     * Check if request method is a reading method (GET, HEAD, OPTIONS)
     */
    private function isReadingMethod(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    /**
     * Check if URI is excluded from CSRF verification
     */
    private function isExcluded(Request $request): bool
    {
        $uri = $request->path();
        
        foreach ($this->except as $pattern) {
            // Convert wildcard pattern to regex
            if (str_contains($pattern, '*')) {
                $pattern = str_replace('*', '.*', preg_quote($pattern, '#'));
                
                if (preg_match('#^' . $pattern . '$#', $uri)) {
                    return true;
                }
            } elseif ($uri === $pattern) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate the CSRF token
     */
    private function validateToken(Request $request): bool
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            return false;
        }
        
        $session = $app->session();
        
        // Get token from request (form field or header)
        $token = $request->input('_csrf_token')
            ?? $request->header('X-CSRF-TOKEN')
            ?? $request->header('X-XSRF-TOKEN');
        
        if ($token === null) {
            return false;
        }
        
        return $session->verifyCsrfToken($token);
    }

    /**
     * Add URI to excluded list
     */
    public function exclude(string $uri): self
    {
        $this->except[] = $uri;
        return $this;
    }

    /**
     * Set excluded URIs
     * 
     * @param array<string> $uris
     */
    public function setExcluded(array $uris): self
    {
        $this->except = $uris;
        return $this;
    }
}
