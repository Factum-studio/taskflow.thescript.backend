<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

class EmailNotVerifiedException extends DomainException
{
    private array $details;

    public function __construct(
        string     $message     = 'Email not verified',
        array      $details     = [],
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        $this->details = $details;
        parent::__construct($message, 403, $code, $previous);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
