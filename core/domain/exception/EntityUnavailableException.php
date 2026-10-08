<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class EntityUnavailableException extends DomainException
{
    public function __construct(
        string     $message     = 'Entity unavailable',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 400, $code, $previous);
    }
}
