<?php

declare(strict_types=1);

/**
 * Helper Functions
 * 
 * Global utility functions used throughout the application.
 */

if (!function_exists('env')) {
    /**
     * Get environment variable with default fallback
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app(): ?\Core\Application
    {
        return \Core\Application::getInstance();
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        $app = app();
        return $app?->config($key, $default) ?? $default;
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = ''): string
    {
        $baseUrl = config('app.url', '');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('route')) {
    /**
     * Generate route URL by name
     * 
     * @param array<string, mixed> $parameters
     */
    function route(string $name, array $parameters = []): string
    {
        $app = app();
        return $app?->getRouter()->url($name, $parameters) ?? '#';
    }
}

if (!function_exists('redirect')) {
    /**
     * Create redirect response
     */
    function redirect(string $url, int $statusCode = 302): \Core\Response
    {
        return \Core\Response::redirect($url, $statusCode);
    }
}

if (!function_exists('session')) {
    /**
     * Get session instance or value
     */
    function session(string $key = null, mixed $default = null): mixed
    {
        $app = app();
        $session = $app?->session();
        
        if ($session === null) {
            return $default;
        }
        
        if ($key === null) {
            return $session;
        }
        
        return $session->get($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     */
    function csrf_token(): string
    {
        $app = app();
        return $app?->session()->getCsrfToken() ?? '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden field
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate method spoofing hidden field
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . htmlspecialchars(strtoupper($method), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get class basename without namespace
     */
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}
