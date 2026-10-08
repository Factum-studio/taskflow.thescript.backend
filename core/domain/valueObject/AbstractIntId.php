<?php

declare(strict_types=1);

namespace core\domain\valueObject;

use core\domain\exception\ValidationException;

abstract class AbstractIntId
{
    protected int $value;

    /**
     * @throws ValidationException
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new ValidationException(static::class . ' must be non-negative');
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $other::class === static::class && $other->value === $this->value;
    }

    public function isNew(): bool
    {
        return $this->value === 0;
    }
}
