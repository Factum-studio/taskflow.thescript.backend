<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class LogicException extends DomainException
{
    public function __construct(
        string     $message     = 'Logic exception',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 500, $code, $previous);
    }

}
