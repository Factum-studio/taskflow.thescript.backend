<?php

namespace modules\tasks\domain\valueObject;

use InvalidArgumentException;

final class StickerName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Sticker name cannot be empty');
        }
        if (mb_strlen($value) > 100) {
            throw new InvalidArgumentException('Sticker name must not exceed 100 characters');
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