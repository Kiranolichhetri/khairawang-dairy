<?php

declare(strict_types=1);

namespace Core\Exceptions;

class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(401, $message, $previous);
    }
}
