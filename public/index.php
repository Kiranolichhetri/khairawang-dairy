<?php

declare(strict_types=1);

/**
 * KHAIRAWANG DAIRY - Application Entry Point
 * 
 * This is the front controller that handles all HTTP requests.
 * All requests are routed through this file via .htaccess rewrite rules.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Load environment variables (if .env file exists)
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Bootstrap the application
use Core\Application;
use Core\Request;
use Core\Response;

try {
    // Create application instance
    $app = new Application(BASE_PATH);
    
    // Load routes
    $routesFile = BASE_PATH . '/routes/web.php';
    if (file_exists($routesFile)) {
        $app->loadRoutes($routesFile);
    }
    
    // Load API routes
    $apiRoutesFile = BASE_PATH . '/routes/api.php';
    if (file_exists($apiRoutesFile)) {
        $app->loadRoutes($apiRoutesFile);
    }
    
    // Run the application
    $app->run();
    
} catch (\Throwable $e) {
    // Emergency fallback error handling
    http_response_code(500);
    
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo '<pre>';
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<p><strong>Trace:</strong></p>';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
        echo '<h1>500 - Server Error</h1>';
        echo '<p>Sorry, something went wrong. Please try again later.</p>';
        echo '</body></html>';
    }
}
