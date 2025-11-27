<?php

declare(strict_types=1);

namespace Core\Exceptions;

class AuthorizationException extends HttpException
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct(403, $message);
    }
}