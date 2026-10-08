<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class BadRequestException extends DomainException
{
    public function __construct(
        string     $message     = 'Bad request exception',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 400, $code, $previous);
    }

}
