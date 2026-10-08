<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class GoneException extends DomainException
{
    public function __construct(
        string     $message  = 'Resource is gone',
        int        $code     = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 410, $code, $previous);
    }
}
