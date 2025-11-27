<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Exception;

class HttpException extends Exception
{
    protected int $statusCode;

    public function __construct(int $statusCode = 500, string $message = "HTTP Error", ?\Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}