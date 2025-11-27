<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    public function __construct(string $message = "Database error", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}