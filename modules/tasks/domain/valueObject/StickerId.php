<?php

namespace modules\tasks\domain\valueObject;

use InvalidArgumentException;

final class StickerId
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Sticker ID must be non-negative integer');
        }
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}