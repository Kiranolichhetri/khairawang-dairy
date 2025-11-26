<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware\Middleware;
use Core\Request;
use Core\Response;
use Core\Application;

/**
 * Rate Limiting Middleware
 * 
 * Limits the number of requests a client can make within a time window.
 */
class RateLimitMiddleware implements Middleware
{
    /**
     * Maximum number of requests allowed
     */
    private int $maxAttempts;

    /**
     * Time window in seconds
     */
    private int $decaySeconds;

    /**
     * Key prefix for storage
     */
    private string $prefix;

    /**
     * Storage driver (session, file, cache)
     */
    private string $driver;

    public function __construct(
        int $maxAttempts = 60,
        int $decaySeconds = 60,
        string $prefix = 'rate_limit',
        string $driver = 'session'
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
        $this->prefix = $prefix;
        $this->driver = $driver;
    }

    /**
     * Handle the request
     */
    public function handle(Request $request, callable $next): Response
    {
        $key = $this->resolveRequestKey($request);
        
        // Get current attempts
        $attempts = $this->getAttempts($key);
        $resetTime = $this->getResetTime($key);
        
        // Check if rate limit exceeded
        if ($attempts >= $this->maxAttempts) {
            return $this->buildTooManyRequestsResponse($request, $resetTime);
        }
        
        // Increment attempts
        $this->incrementAttempts($key);
        
        // Execute request
        $response = $next($request);
        
        // Add rate limit headers
        return $this->addRateLimitHeaders($response, $attempts + 1, $resetTime);
    }

    /**
     * Resolve the unique key for the request
     */
    private function resolveRequestKey(Request $request): string
    {
        // Use IP address + path as key
        $ip = $request->ip();
        $path = $request->path();
        
        // Hash for privacy
        return $this->prefix . ':' . sha1($ip . '|' . $path);
    }

    /**
     * Get current number of attempts
     */
    private function getAttempts(string $key): int
    {
        $data = $this->getData($key);
        
        // Check if window has expired
        if ($data === null || $data['reset_at'] <= time()) {
            return 0;
        }
        
        return $data['attempts'];
    }

    /**
     * Get reset timestamp
     */
    private function getResetTime(string $key): int
    {
        $data = $this->getData($key);
        
        if ($data === null || $data['reset_at'] <= time()) {
            return time() + $this->decaySeconds;
        }
        
        return $data['reset_at'];
    }

    /**
     * Increment attempts counter
     */
    private function incrementAttempts(string $key): void
    {
        $data = $this->getData($key);
        
        // Reset if window expired
        if ($data === null || $data['reset_at'] <= time()) {
            $data = [
                'attempts' => 0,
                'reset_at' => time() + $this->decaySeconds,
            ];
        }
        
        $data['attempts']++;
        $this->setData($key, $data);
    }

    /**
     * Get stored data
     * 
     * @return array{attempts: int, reset_at: int}|null
     */
    private function getData(string $key): ?array
    {
        return match($this->driver) {
            'session' => $this->getSessionData($key),
            'file' => $this->getFileData($key),
            default => null,
        };
    }

    /**
     * Set stored data
     * 
     * @param array{attempts: int, reset_at: int} $data
     */
    private function setData(string $key, array $data): void
    {
        match($this->driver) {
            'session' => $this->setSessionData($key, $data),
            'file' => $this->setFileData($key, $data),
            default => null,
        };
    }

    /**
     * Get data from session
     * 
     * @return array{attempts: int, reset_at: int}|null
     */
    private function getSessionData(string $key): ?array
    {
        $app = Application::getInstance();
        
        if ($app === null) {
            return null;
        }
        
        return $app->session()->get($key);
    }

    /**
     * Set data in session
     * 
     * @param array{attempts: int, reset_at: int} $data
     */
    private function setSessionData(string $key, array $data): void
    {
        $app = Application::getInstance();
        
        if ($app !== null) {
            $app->session()->set($key, $data);
        }
    }

    /**
     * Get data from file storage
     * 
     * @return array{attempts: int, reset_at: int}|null
     */
    private function getFileData(string $key): ?array
    {
        $filePath = sys_get_temp_dir() . '/' . $key . '.rate';
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return null;
        }
        
        return json_decode($content, true);
    }

    /**
     * Set data in file storage
     * 
     * @param array{attempts: int, reset_at: int} $data
     */
    private function setFileData(string $key, array $data): void
    {
        $filePath = sys_get_temp_dir() . '/' . $key . '.rate';
        file_put_contents($filePath, json_encode($data), LOCK_EX);
    }

    /**
     * Build 429 Too Many Requests response
     */
    private function buildTooManyRequestsResponse(Request $request, int $resetTime): Response
    {
        $retryAfter = $resetTime - time();
        
        if ($request->expectsJson()) {
            return Response::json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }
        
        return (new Response(
            'Too many requests. Please try again in ' . $retryAfter . ' seconds.',
            429
        ))->header('Retry-After', (string) $retryAfter);
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(Response $response, int $attempts, int $resetTime): Response
    {
        return $response
            ->header('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->header('X-RateLimit-Remaining', (string) max(0, $this->maxAttempts - $attempts))
            ->header('X-RateLimit-Reset', (string) $resetTime);
    }

    /**
     * Create instance for API endpoints
     */
    public static function api(int $maxAttempts = 60, int $decaySeconds = 60): self
    {
        return new self($maxAttempts, $decaySeconds, 'rate_limit_api', 'file');
    }

    /**
     * Create instance for login attempts
     */
    public static function login(int $maxAttempts = 5, int $decaySeconds = 300): self
    {
        return new self($maxAttempts, $decaySeconds, 'rate_limit_login', 'file');
    }

    /**
     * Create instance for form submissions
     */
    public static function form(int $maxAttempts = 10, int $decaySeconds = 60): self
    {
        return new self($maxAttempts, $decaySeconds, 'rate_limit_form', 'session');
    }

    /**
     * Clear rate limit for a key
     */
    public static function clear(string $ip, string $path = ''): void
    {
        $prefix = 'rate_limit';
        $key = $prefix . ':' . sha1($ip . '|' . $path);
        $filePath = sys_get_temp_dir() . '/' . $key . '.rate';
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
