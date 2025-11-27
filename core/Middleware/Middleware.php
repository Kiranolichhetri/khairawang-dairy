<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Request;
use Core\Response;

/**
 * Middleware Interface
 * 
 * Defines the contract for middleware components in the pipeline.
 */
interface Middleware
{
    /**
     * Handle the request
     * 
     * @param Request $request The incoming request
     * @param callable $next The next middleware in the pipeline
     * @return Response The response
     */
    public function handle(Request $request, callable $next): Response;
}
