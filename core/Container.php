<?php

declare(strict_types=1);

namespace Core;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Core\Exceptions\ContainerException;

/**
 * Dependency Injection Container
 * 
 * Provides service registration, resolution, and auto-wiring capabilities.
 */
class Container
{
    /** @var array<string, callable|string> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    /**
     * Register a binding in the container
     */
    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * Register a singleton binding
     */
    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = true;
    }

    /**
     * Register an existing instance
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Check if a binding exists
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Resolve a binding from the container
     */
    public function resolve(string $abstract): mixed
    {
        // Return existing instance if available
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete implementation
        $concrete = $this->bindings[$abstract] ?? $abstract;

        // Build the instance
        $instance = $this->build($concrete);

        // Store as singleton if registered as such
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Alias for resolve
     */
    public function make(string $abstract): mixed
    {
        return $this->resolve($abstract);
    }

    /**
     * Build an instance of the given concrete
     */
    private function build(callable|string $concrete): mixed
    {
        // If concrete is a closure, execute it
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // If it's a callable array, execute it
        if (is_callable($concrete) && !is_string($concrete)) {
            return $concrete($this);
        }

        // If it's a class name, instantiate it
        if (is_string($concrete) && class_exists($concrete)) {
            return $this->instantiate($concrete);
        }

        throw new ContainerException("Cannot resolve: {$concrete}");
    }

    /**
     * Instantiate a class with dependency injection
     */
    private function instantiate(string $class): object
    {
        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class {$class} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     * 
     * @param ReflectionParameter[] $parameters
     * @return array<mixed>
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->resolve($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new ContainerException(
                    "Cannot resolve parameter: {$parameter->getName()}"
                );
            }
        }

        return $dependencies;
    }

    /**
     * Call a method with dependency injection
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback);
            $callback = [$this->resolve($class), $method];
        }

        if (is_array($callback)) {
            $reflector = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflector = new ReflectionFunction($callback);
        }

        $dependencies = $this->resolveMethodDependencies(
            $reflector->getParameters(),
            $parameters
        );

        return $callback(...$dependencies);
    }

    /**
     * Resolve method dependencies with provided parameters
     * 
     * @param ReflectionParameter[] $parameters
     * @param array<string, mixed> $provided
     * @return array<mixed>
     */
    private function resolveMethodDependencies(array $parameters, array $provided): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // Check if parameter is provided
            if (array_key_exists($name, $provided)) {
                $dependencies[] = $provided[$name];
                continue;
            }

            // Try to resolve from container if it's a class type
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->resolve($type->getName());
                continue;
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            // Allow null if nullable
            if ($type !== null && $type->allowsNull()) {
                $dependencies[] = null;
                continue;
            }

            throw new ContainerException(
                "Cannot resolve parameter: {$name}"
            );
        }

        return $dependencies;
    }

    /**
     * Remove a binding from the container
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract], $this->singletons[$abstract]);
    }

    /**
     * Clear all bindings and instances
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
    }
}
