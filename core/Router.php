<?php

declare(strict_types=1);

namespace Core;

use Core\Attributes\Route;
use Core\Middleware\Middleware;
use Core\Exceptions\NotFoundException;
use Core\Exceptions\MethodNotAllowedException;
use ReflectionClass;
use ReflectionMethod;

/**
 * HTTP Router with Attribute-based Routing
 * 
 * Supports route groups, middleware, named routes, and route parameters.
 */
class Router
{
    private Container $container;
    
    /** @var array<string, array<string, array>> Routes organized by method */
    private array $routes = [];
    
    /** @var array<string, array> Named routes */
    private array $namedRoutes = [];
    
    /** @var array<string|callable> Global middleware */
    private array $globalMiddleware = [];
    
    /** @var array<string|callable> Group middleware stack */
    private array $groupMiddleware = [];
    
    /** @var string Current group prefix */
    private string $groupPrefix = '';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a GET route
     */
    public function get(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->addRoute('GET', $uri, $action, $name);
    }

    /**
     * Register a POST route
     */
    public function post(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->addRoute('POST', $uri, $action, $name);
    }

    /**
     * Register a PUT route
     */
    public function put(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->addRoute('PUT', $uri, $action, $name);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->addRoute('PATCH', $uri, $action, $name);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $uri, $action, $name);
    }

    /**
     * Register multiple methods for a route
     * 
     * @param array<string> $methods
     */
    public function match(array $methods, string $uri, callable|array|string $action, ?string $name = null): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $uri, $action, $name);
        }
        return $this;
    }

    /**
     * Register a route for all HTTP methods
     */
    public function any(string $uri, callable|array|string $action, ?string $name = null): self
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action, $name);
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $uri, callable|array|string $action, ?string $name = null): self
    {
        $uri = $this->groupPrefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');
        
        $middleware = array_merge($this->globalMiddleware, $this->groupMiddleware);

        $route = [
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware,
            'name' => $name,
            'pattern' => $this->compilePattern($uri),
        ];

        $this->routes[$method][$uri] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    /**
     * Compile URI pattern to regex
     */
    private function compilePattern(string $uri): string
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $uri);
        // Replace {param?} with optional named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\?\}/', '(?P<$1>[^/]*)?', $pattern);
        return '#^' . $pattern . '$#';
    }

    /**
     * Add global middleware
     * 
     * @param string|array<string>|callable $middleware
     */
    public function middleware(string|array|callable $middleware): self
    {
        if (is_array($middleware)) {
            $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        } else {
            $this->globalMiddleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Create a route group
     * 
     * @param array{prefix?: string, middleware?: array<string>} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->groupPrefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge(
                $this->groupMiddleware,
                (array) $attributes['middleware']
            );
        }

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Register routes from controller attributes
     */
    public function registerController(string $controller): void
    {
        $reflection = new ReflectionClass($controller);
        
        // Check for class-level route attribute
        $classAttributes = $reflection->getAttributes(Route::class);
        $classPrefix = '';
        $classMiddleware = [];
        
        if (!empty($classAttributes)) {
            $classRoute = $classAttributes[0]->newInstance();
            $classPrefix = $classRoute->path;
            $classMiddleware = $classRoute->middleware;
        }

        // Process method-level route attributes
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodAttributes = $method->getAttributes(Route::class);
            
            foreach ($methodAttributes as $attribute) {
                $route = $attribute->newInstance();
                $uri = $classPrefix . '/' . ltrim($route->path, '/');
                $uri = '/' . trim($uri, '/');
                
                $this->addRoute(
                    $route->method,
                    $uri,
                    [$controller, $method->getName()],
                    $route->name
                );
                
                // Add route-specific middleware
                if (!empty($route->middleware) || !empty($classMiddleware)) {
                    $this->routes[$route->method][$uri]['middleware'] = array_merge(
                        $this->routes[$route->method][$uri]['middleware'],
                        $classMiddleware,
                        $route->middleware
                    );
                }
            }
        }
    }

    /**
     * Generate URL for a named route
     * 
     * @param array<string, mixed> $parameters
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not found.");
        }

        $uri = $this->namedRoutes[$name]['uri'];
        
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
            $uri = str_replace('{' . $key . '?}', (string) $value, $uri);
        }

        // Remove any remaining optional parameters
        $uri = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\?\}/', '', $uri);

        return $uri;
    }

    /**
     * Dispatch the request
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->path();

        // Handle method override for forms (only allow specific methods)
        if ($method === 'POST' && $request->input('_method')) {
            $overrideMethod = strtoupper($request->input('_method'));
            // Only allow PUT, PATCH, DELETE method overrides
            if (in_array($overrideMethod, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $overrideMethod;
            }
        }

        // Try to match a route
        $route = $this->matchRoute($method, $uri);

        if ($route === null) {
            // Check if route exists for different method
            foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $m) {
                if ($m !== $method && $this->matchRoute($m, $uri) !== null) {
                    throw new MethodNotAllowedException($method);
                }
            }
            throw new NotFoundException("Route not found: {$uri}");
        }

        // Extract parameters from URI
        $parameters = $this->extractParameters($route, $uri);
        $request->setRouteParameters($parameters);

        // Run through middleware pipeline
        return $this->runMiddleware(
            $route['middleware'],
            $request,
            fn(Request $req) => $this->executeAction($route['action'], $req, $parameters)
        );
    }

    /**
     * Match a route against the URI
     * 
     * @return array<string, mixed>|null
     */
    private function matchRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Extract parameters from URI
     * 
     * @param array<string, mixed> $route
     * @return array<string, string>
     */
    private function extractParameters(array $route, string $uri): array
    {
        $matches = [];
        preg_match($route['pattern'], $uri, $matches);

        $parameters = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Run through middleware pipeline
     * 
     * @param array<string|callable> $middleware
     */
    private function runMiddleware(array $middleware, Request $request, callable $final): Response
    {
        if (empty($middleware)) {
            return $final($request);
        }

        $middlewareClass = array_shift($middleware);
        
        $middlewareInstance = $this->resolveMiddleware($middlewareClass);

        return $middlewareInstance->handle(
            $request,
            fn(Request $req) => $this->runMiddleware($middleware, $req, $final)
        );
    }

    /**
     * Resolve middleware instance
     */
    private function resolveMiddleware(string|callable $middleware): Middleware
    {
        if (is_callable($middleware)) {
            return new class($middleware) implements Middleware {
                public function __construct(private $callback) {}
                
                public function handle(Request $request, callable $next): Response
                {
                    return ($this->callback)($request, $next);
                }
            };
        }

        return $this->container->resolve($middleware);
    }

    /**
     * Execute the route action
     * 
     * @param callable|array<int, string|object>|string $action
     * @param array<string, mixed> $parameters
     */
    private function executeAction(callable|array|string $action, Request $request, array $parameters): Response
    {
        // Add request to parameters
        $parameters['request'] = $request;

        if (is_callable($action) && !is_array($action) && !is_string($action)) {
            $result = $this->container->call($action, $parameters);
        } elseif (is_array($action)) {
            [$controller, $method] = $action;
            
            if (is_string($controller)) {
                $controller = $this->container->resolve($controller);
            }
            
            $result = $this->container->call([$controller, $method], $parameters);
        } elseif (is_string($action) && str_contains($action, '@')) {
            $result = $this->container->call($action, $parameters);
        } else {
            throw new \InvalidArgumentException('Invalid route action');
        }

        return $this->prepareResponse($result);
    }

    /**
     * Convert action result to Response
     */
    private function prepareResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        if (is_string($result) || is_null($result)) {
            return new Response((string) $result);
        }

        if (is_object($result) && method_exists($result, '__toString')) {
            return new Response((string) $result);
        }

        return new Response('');
    }

    /**
     * Get all registered routes
     * 
     * @return array<string, array<string, array>>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
