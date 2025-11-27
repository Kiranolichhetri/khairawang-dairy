<?php

declare(strict_types=1);

namespace Core\Attributes;

use Attribute;

/**
 * Route Attribute for Attribute-based Routing
 * 
 * Can be applied to both classes (for prefix) and methods (for specific routes).
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @param string $path The route path/URI
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string|null $name Named route identifier
     * @param array<string> $middleware Middleware classes to apply
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method = 'GET',
        public readonly ?string $name = null,
        public readonly array $middleware = []
    ) {}
}
