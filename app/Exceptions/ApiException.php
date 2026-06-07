<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public array $payload;

    public function __construct(
        string $message = "Something went wrong",
        int $code = 500,
        array $payload = []
    ) {
        parent::__construct($message, $code);
        $this->payload = $payload;
    }
}
