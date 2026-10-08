<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class EntityNotFoundException extends DomainException
{
    public function __construct(
        string     $message     = 'Entity not found',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 404, $code, $previous);
    }
}
