<?php

declare(strict_types=1);

namespace core\domain\exception;

use Throwable;

final class FileStorageException extends DomainException
{
    public function __construct(
        string     $message     = 'File storage exception',
        int        $code        = 0,
        ?Throwable $previous    = null,
    ) {
        parent::__construct($message, 500, $code, $previous);
    }
}
