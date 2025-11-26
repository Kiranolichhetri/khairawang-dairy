<?php

declare(strict_types=1);

namespace Core;

use Core\Exceptions\Handler;
use Throwable;

/**
 * Main Application Class
 * 
 * Bootstrap the application, manage services, and handle the request/response cycle.
 */
class Application
{
    private static ?Application $instance = null;
    private Container $container;
    private Router $router;
    private array $config = [];
    private string $basePath;
    private bool $debugMode = false;

    /**
     * Create new Application instance
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->container = new Container();
        $this->router = new Router($this->container);
        
        $this->registerBaseBindings();
        $this->loadConfiguration();
        $this->setupErrorHandling();
        
        self::$instance = $this;
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    /**
     * Register base container bindings
     */
    private function registerBaseBindings(): void
    {
        $this->container->singleton(Application::class, fn() => $this);
        $this->container->singleton(Container::class, fn() => $this->container);
        $this->container->singleton(Router::class, fn() => $this->router);
        $this->container->singleton(Session::class, fn() => new Session());
        $this->container->singleton(Database::class, function() {
            $dbConfig = $this->config('database', []);
            return new Database($dbConfig);
        });
    }

    /**
     * Load configuration files
     */
    private function loadConfiguration(): void
    {
        $configPath = $this->basePath . '/config';
        $configFiles = ['app', 'database', 'cache', 'mail', 'payment', 'security'];

        foreach ($configFiles as $file) {
            $filePath = $configPath . '/' . $file . '.php';
            if (file_exists($filePath)) {
                $this->config[$file] = require $filePath;
            }
        }

        $this->debugMode = $this->config('app.debug', false);
    }

    /**
     * Setup error and exception handling
     */
    private function setupErrorHandling(): void
    {
        error_reporting(E_ALL);
        
        set_error_handler(function(int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function(Throwable $e): void {
            $handler = new Handler($this->debugMode);
            $response = $handler->handle($e);
            $response->send();
        });
    }

    /**
     * Get configuration value
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Get the base path
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Register a service provider
     */
    public function register(string $abstract, callable|string $concrete): void
    {
        $this->container->bind($abstract, $concrete);
    }

    /**
     * Register a singleton service
     */
    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract): mixed
    {
        return $this->container->resolve($abstract);
    }

    /**
     * Add middleware to the router
     */
    public function middleware(string|array $middleware): self
    {
        $this->router->middleware($middleware);
        return $this;
    }

    /**
     * Register routes from a file
     */
    public function loadRoutes(string $routeFile): self
    {
        if (file_exists($routeFile)) {
            $router = $this->router;
            require $routeFile;
        }
        return $this;
    }

    /**
     * Handle the incoming request
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (Throwable $e) {
            $handler = new Handler($this->debugMode);
            return $handler->handle($e);
        }
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request = Request::capture();
        $response = $this->handle($request);
        $response->send();
    }

    /**
     * Check if running in debug mode
     */
    public function isDebug(): bool
    {
        return $this->debugMode;
    }

    /**
     * Get database instance
     */
    public function db(): Database
    {
        return $this->container->resolve(Database::class);
    }

    /**
     * Get session instance
     */
    public function session(): Session
    {
        return $this->container->resolve(Session::class);
    }
}
