<?php

namespace modules\tasks\domain\valueObject;

use InvalidArgumentException;

final class Title
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Title cannot be empty');
        }
        if (mb_strlen($value) > 255) {
            throw new InvalidArgumentException('Title must not exceed 255 characters');
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}