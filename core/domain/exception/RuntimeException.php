<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class RuntimeException extends DomainException
{
    private array $errors;

    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(
        string     $message     = 'Runtime exception',
        array      $errors      = [],
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        $this->errors = $errors;
        parent::__construct($message, 500, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
