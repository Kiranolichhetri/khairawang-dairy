<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Response Builder
 * 
 * Provides fluent interface for building HTTP responses with JSON support,
 * redirects, headers management, and status codes.
 */
class Response
{
    private string $content = '';
    private int $statusCode = 200;
    
    /** @var array<string, string> */
    private array $headers = [];
    
    /** @var array<array{name: string, value: string, options: array}> */
    private array $cookies = [];

    /** @var array<int, string> HTTP status messages */
    private const STATUS_TEXTS = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        410 => 'Gone',
        415 => 'Unsupported Media Type',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    /**
     * Create a new response
     */
    public function __construct(string $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Create a JSON response
     * 
     * @param array<mixed>|object $data
     */
    public static function json(array|object $data, int $statusCode = 200): self
    {
        $response = new self();
        $response->statusCode = $statusCode;
        $response->content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $response->header('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        $response = new self('', $statusCode);
        $response->header('Location', $url);
        return $response;
    }

    /**
     * Create a file download response
     */
    public static function download(string $filePath, ?string $filename = null): self
    {
        if (!file_exists($filePath)) {
            return new self('File not found', 404);
        }

        $filename = $filename ?? basename($filePath);
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return new self('Unable to read file', 500);
        }
        
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $response = new self($content);
        $response->header('Content-Type', $mimeType);
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->header('Content-Length', (string) strlen($content));
        
        return $response;
    }

    /**
     * Create a view response (for API compatibility)
     * 
     * @param array<string, mixed> $data
     */
    public static function view(string $template, array $data = []): self
    {
        $view = new View();
        $content = $view->render($template, $data);
        return new self($content);
    }

    /**
     * Set response content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set a header
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     * 
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }
        return $this;
    }

    /**
     * Get all headers
     * 
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set a cookie
     * 
     * @param array<string, mixed> $options
     */
    public function cookie(
        string $name,
        string $value,
        int $minutes = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $expires = $minutes > 0 ? time() + ($minutes * 60) : 0;
        
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'options' => [
                'expires' => $expires,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'samesite' => $sameSite,
            ],
        ];
        
        return $this;
    }

    /**
     * Remove a cookie
     */
    public function forgetCookie(string $name, string $path = '/', string $domain = ''): self
    {
        return $this->cookie($name, '', -60, $path, $domain);
    }

    /**
     * Set cache headers for no caching
     */
    public function noCache(): self
    {
        $this->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');
        return $this;
    }

    /**
     * Set cache headers
     */
    public function cache(int $minutes): self
    {
        $seconds = $minutes * 60;
        $this->header('Cache-Control', "max-age={$seconds}, public");
        $this->header('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        return $this;
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Send status code
        $statusText = self::STATUS_TEXTS[$this->statusCode] ?? 'Unknown Status';
        header("HTTP/1.1 {$this->statusCode} {$statusText}");
        
        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Send cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['options']
            );
        }
        
        // Send content
        echo $this->content;
    }

    /**
     * Prepare response for sending (without actually sending)
     */
    public function prepare(): self
    {
        // Set content length
        $this->header('Content-Length', (string) strlen($this->content));
        
        return $this;
    }

    /**
     * Check if response is redirect
     */
    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Check if response is successful
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if response is client error
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if response is server error
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }

    /**
     * Convert response to string (for debugging)
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Create success JSON response
     * 
     * @param array<string, mixed> $data
     */
    public static function success(array $data = [], string $message = 'Success'): self
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create error JSON response
     * 
     * @param array<string, mixed> $errors
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): self
    {
        $data = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        
        return self::json($data, $statusCode);
    }

    /**
     * Create validation error response
     * 
     * @param array<string, array<string>> $errors
     */
    public static function validationError(array $errors): self
    {
        return self::json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }
}
