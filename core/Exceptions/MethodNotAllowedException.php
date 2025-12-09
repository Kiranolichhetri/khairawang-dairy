<?php

declare(strict_types=1);

namespace Core\Exceptions;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $method = '', int $code = 0, ?\Throwable $previous = null)
    {
        $message = $method ? "Method [{$method}] not allowed" : 'Method Not Allowed';
        parent::__construct(405, $message, $previous);
    }
}
