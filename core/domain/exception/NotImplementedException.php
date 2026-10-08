<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class NotImplementedException extends DomainException
{
    public function __construct(
        string     $message     = 'Not implemented exception',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 501, $code, $previous);
    }

}
