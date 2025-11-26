<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Request Wrapper
 * 
 * Provides sanitized access to request data, file uploads, and JSON body parsing.
 */
class Request
{
    private string $method;
    private string $uri;
    private string $path;
    
    /** @var array<string, string> */
    private array $query = [];
    
    /** @var array<string, mixed> */
    private array $post = [];
    
    /** @var array<string, mixed> */
    private array $cookies = [];
    
    /** @var array<string, array> */
    private array $files = [];
    
    /** @var array<string, string> */
    private array $headers = [];
    
    /** @var array<string, mixed> */
    private array $json = [];
    
    /** @var array<string, string> */
    private array $routeParameters = [];

    private ?string $rawBody = null;

    /**
     * Create request from PHP globals
     */
    public static function capture(): self
    {
        $request = new self();
        
        $request->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $request->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $request->path = parse_url($request->uri, PHP_URL_PATH) ?: '/';
        
        $request->query = self::sanitizeArray($_GET);
        $request->post = self::sanitizeArray($_POST);
        $request->cookies = self::sanitizeArray($_COOKIE);
        $request->files = $_FILES;
        $request->headers = self::parseHeaders();
        
        // Parse JSON body if content type is JSON
        if ($request->isJson()) {
            $request->rawBody = file_get_contents('php://input') ?: '';
            $decoded = json_decode($request->rawBody, true);
            $request->json = is_array($decoded) ? self::sanitizeArray($decoded) : [];
        }
        
        return $request;
    }

    /**
     * Create request for testing
     * 
     * @param array<string, mixed> $data
     */
    public static function create(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): self {
        $request = new self();
        
        $request->method = strtoupper($method);
        $request->uri = $uri;
        $request->path = parse_url($uri, PHP_URL_PATH) ?: '/';
        
        if ($method === 'GET') {
            $request->query = self::sanitizeArray($data);
        } else {
            $request->post = self::sanitizeArray($data);
        }
        
        $request->headers = $headers;
        
        return $request;
    }

    /**
     * Parse HTTP headers from $_SERVER
     * 
     * @return array<string, string>
     */
    private static function parseHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[ucwords(strtolower($header), '-')] = $value;
            }
        }
        
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        
        return $headers;
    }

    /**
     * Sanitize input array recursively
     * 
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $key = is_string($key) ? htmlspecialchars($key, ENT_QUOTES, 'UTF-8') : $key;
            
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                // Trim and sanitize string values
                $sanitized[$key] = trim($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Check if request method matches
     */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get request path (without query string)
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Get query string parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get all query parameters
     * 
     * @return array<string, string>
     */
    public function queryAll(): array
    {
        return $this->query;
    }

    /**
     * Get POST parameter
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all POST parameters
     * 
     * @return array<string, mixed>
     */
    public function postAll(): array
    {
        return $this->post;
    }

    /**
     * Get input from query, post, or JSON body
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] 
            ?? $this->post[$key] 
            ?? $this->json[$key] 
            ?? $default;
    }

    /**
     * Get all input data
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json);
    }

    /**
     * Get only specified keys from input
     * 
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        $result = [];
        
        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $result[$key] = $all[$key];
            }
        }
        
        return $result;
    }

    /**
     * Get all input except specified keys
     * 
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        
        return $all;
    }

    /**
     * Check if input key exists
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * Check if input key exists and is not empty
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * Get JSON body data
     */
    public function json(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? $default;
    }

    /**
     * Get all JSON body data
     * 
     * @return array<string, mixed>
     */
    public function jsonAll(): array
    {
        return $this->json;
    }

    /**
     * Check if request is JSON
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    /**
     * Check if request expects JSON response
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json') || $this->isJson();
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Get header value
     */
    public function header(string $key, ?string $default = null): ?string
    {
        $key = ucwords(strtolower($key), '-');
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     * 
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get cookie value
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get uploaded file
     * 
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        if (!isset($this->files[$key]) || $this->files[$key]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        return $this->files[$key];
    }

    /**
     * Check if file was uploaded
     */
    public function hasFile(string $key): bool
    {
        return $this->file($key) !== null;
    }

    /**
     * Get all uploaded files
     * 
     * @return array<string, array>
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Get route parameter
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    /**
     * Get all route parameters
     * 
     * @return array<string, string>
     */
    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * Set route parameters (called by router)
     * 
     * @param array<string, string> $parameters
     */
    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    /**
     * Get raw request body
     */
    public function getContent(): string
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input') ?: '';
        }
        return $this->rawBody;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '127.0.0.1';
    }

    /**
     * Get user agent
     */
    public function userAgent(): ?string
    {
        return $this->header('User-Agent');
    }

    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443
            || ($this->header('X-Forwarded-Proto') === 'https');
    }

    /**
     * Get the full URL
     */
    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->uri;
    }

    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');
        
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        
        return null;
    }

    /**
     * Validate input against rules
     * 
     * @param array<string, string|array> $rules
     * @return array<string, array<string>> Validation errors
     */
    public function validate(array $rules): array
    {
        $validator = new Validator($this->all(), $rules);
        return $validator->validate();
    }
}
