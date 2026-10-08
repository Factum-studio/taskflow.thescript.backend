<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class PermissionDeniedException extends DomainException
{
    private array $errors;

    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(
        string $message = 'Permission denied',
        array $errors = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->errors = $errors;
        parent::__construct($message, 403, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
