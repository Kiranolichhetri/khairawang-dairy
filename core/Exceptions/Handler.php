<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Core\Response;
use Throwable;

/**
 * Exception Handler
 * 
 * Handles exceptions and converts them to appropriate HTTP responses.
 * Supports debug and production modes with different output levels.
 */
class Handler
{
    private bool $debug;
    
    /** @var array<string, int> Exception to HTTP status code mapping */
    private array $statusCodes = [
        NotFoundException::class => 404,
        MethodNotAllowedException::class => 405,
        ValidationException::class => 422,
        UnauthorizedException::class => 401,
        ForbiddenException::class => 403,
        DatabaseException::class => 500,
        ModelException::class => 500,
        ContainerException::class => 500,
    ];

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Handle an exception and return a response
     */
    public function handle(Throwable $e): Response
    {
        // Log the exception
        $this->log($e);
        
        // Get status code
        $statusCode = $this->getStatusCode($e);
        
        // Check if JSON response is expected
        if ($this->shouldReturnJson()) {
            return $this->jsonResponse($e, $statusCode);
        }
        
        return $this->htmlResponse($e, $statusCode);
    }

    /**
     * Log the exception
     */
    private function log(Throwable $e): void
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        error_log($message);
    }

    /**
     * Get HTTP status code for exception
     */
    private function getStatusCode(Throwable $e): int
    {
        $class = get_class($e);
        
        if (isset($this->statusCodes[$class])) {
            return $this->statusCodes[$class];
        }
        
        // Check for parent classes
        foreach ($this->statusCodes as $exceptionClass => $code) {
            if ($e instanceof $exceptionClass) {
                return $code;
            }
        }
        
        // Check if exception has a getCode method that returns a valid HTTP code
        $code = $e->getCode();
        if ($code >= 400 && $code < 600) {
            return $code;
        }
        
        return 500;
    }

    /**
     * Check if JSON response should be returned
     */
    private function shouldReturnJson(): bool
    {
        if (!isset($_SERVER['HTTP_ACCEPT'])) {
            return false;
        }
        
        return str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
            || str_contains($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/json');
    }

    /**
     * Create JSON error response
     */
    private function jsonResponse(Throwable $e, int $statusCode): Response
    {
        $data = [
            'success' => false,
            'message' => $this->debug ? $e->getMessage() : $this->getPublicMessage($statusCode),
            'code' => $statusCode,
        ];
        
        if ($this->debug) {
            $data['exception'] = get_class($e);
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = array_slice(
                array_map(fn($t) => [
                    'file' => $t['file'] ?? '',
                    'line' => $t['line'] ?? 0,
                    'function' => ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''),
                ], $e->getTrace()),
                0,
                10
            );
        }
        
        // Add validation errors if applicable
        if ($e instanceof ValidationException) {
            $data['errors'] = $e->getErrors();
        }
        
        return Response::json($data, $statusCode);
    }

    /**
     * Create HTML error response
     */
    private function htmlResponse(Throwable $e, int $statusCode): Response
    {
        if ($this->debug) {
            return $this->debugHtmlResponse($e, $statusCode);
        }
        
        return $this->productionHtmlResponse($statusCode);
    }

    /**
     * Create debug HTML response with full error details
     */
    private function debugHtmlResponse(Throwable $e, int $statusCode): Response
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error: {$e->getMessage()}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .error-box { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .error-header { background: #dc3545; color: #fff; padding: 1.5rem; }
        .error-header h1 { font-size: 1.25rem; font-weight: 600; }
        .error-body { padding: 1.5rem; }
        .error-message { font-size: 1.5rem; color: #333; margin-bottom: 1rem; word-break: break-word; }
        .error-details { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
        .trace { background: #f8f9fa; border-radius: 4px; padding: 1rem; overflow-x: auto; }
        .trace-item { padding: 0.5rem 0; border-bottom: 1px solid #eee; font-family: monospace; font-size: 0.85rem; }
        .trace-item:last-child { border-bottom: none; }
        .trace-file { color: #007bff; }
        .trace-line { color: #28a745; }
        .trace-function { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-box">
            <div class="error-header">
                <h1>{$this->escape(get_class($e))} (HTTP {$statusCode})</h1>
            </div>
            <div class="error-body">
                <div class="error-message">{$this->escape($e->getMessage())}</div>
                <div class="error-details">
                    <strong>File:</strong> {$this->escape($e->getFile())} <strong>Line:</strong> {$e->getLine()}
                </div>
                <div class="trace">
HTML;
        
        foreach (array_slice($e->getTrace(), 0, 15) as $i => $trace) {
            $file = $trace['file'] ?? '';
            $line = $trace['line'] ?? 0;
            $class = $trace['class'] ?? '';
            $type = $trace['type'] ?? '';
            $function = $trace['function'] ?? '';
            
            $html .= <<<HTML
                    <div class="trace-item">
                        <span class="trace-num">#{$i}</span>
                        <span class="trace-file">{$this->escape($file)}</span>:<span class="trace-line">{$line}</span>
                        <span class="trace-function">{$this->escape($class)}{$type}{$this->escape($function)}()</span>
                    </div>
HTML;
        }
        
        $html .= <<<HTML
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        
        return new Response($html, $statusCode);
    }

    /**
     * Create production HTML response
     */
    private function productionHtmlResponse(int $statusCode): Response
    {
        $title = $this->getPublicTitle($statusCode);
        $message = $this->getPublicMessage($statusCode);
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #F7EFDF; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .error-page { text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: 700; color: #201916; line-height: 1; }
        .error-title { font-size: 2rem; color: #201916; margin: 1rem 0; }
        .error-message { color: #666; margin-bottom: 2rem; }
        .back-link { 
            display: inline-block; 
            background: #FD7C44; 
            color: #fff; 
            padding: 0.75rem 2rem; 
            border-radius: 4px; 
            text-decoration: none; 
            font-weight: 500;
            transition: background 0.2s;
        }
        .back-link:hover { background: #e86e3a; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">{$statusCode}</div>
        <h1 class="error-title">{$this->escape($title)}</h1>
        <p class="error-message">{$this->escape($message)}</p>
        <a href="/" class="back-link">Go to Homepage</a>
    </div>
</body>
</html>
HTML;
        
        return new Response($html, $statusCode);
    }

    /**
     * Get public-facing error title
     */
    private function getPublicTitle(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Access Denied',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            422 => 'Validation Error',
            429 => 'Too Many Requests',
            500 => 'Server Error',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }

    /**
     * Get public-facing error message
     */
    private function getPublicMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'The request could not be understood by the server.',
            401 => 'You need to log in to access this page.',
            403 => 'You don\'t have permission to access this resource.',
            404 => 'The page you\'re looking for doesn\'t exist.',
            405 => 'The request method is not supported for this route.',
            422 => 'The submitted data is invalid.',
            429 => 'You\'ve made too many requests. Please try again later.',
            500 => 'Something went wrong on our end. Please try again later.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            default => 'An unexpected error occurred.',
        };
    }

    /**
     * Escape HTML
     */
    private function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Register custom exception handler
     */
    public function registerStatusCode(string $exceptionClass, int $statusCode): void
    {
        $this->statusCodes[$exceptionClass] = $statusCode;
    }
}

/**
 * Base HTTP Exception
 */
class HttpException extends \Exception
{
    protected int $statusCode;
    
    /** @var array<string, string> */
    protected array $headers = [];

    public function __construct(int $statusCode = 500, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}

/**
 * 404 Not Found Exception
 */
class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Not Found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}

/**
 * 405 Method Not Allowed Exception
 */
class MethodNotAllowedException extends HttpException
{
    public function __construct(string $method = '', int $code = 0, ?\Throwable $previous = null)
    {
        $message = $method ? "Method [{$method}] not allowed" : 'Method Not Allowed';
        parent::__construct(405, $message, $code, $previous);
    }
}

/**
 * 422 Validation Exception
 */
class ValidationException extends HttpException
{
    /** @var array<string, array<string>> */
    private array $errors;

    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        $this->errors = $errors;
        parent::__construct(422, $message);
    }

    /** @return array<string, array<string>> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

/**
 * 401 Unauthorized Exception
 */
class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}

/**
 * 403 Forbidden Exception
 */
class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Forbidden', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}

/**
 * Database Exception
 */
class DatabaseException extends \Exception {}

/**
 * Model Exception
 */
class ModelException extends \Exception {}

/**
 * Container Exception
 */
class ContainerException extends \Exception {}
