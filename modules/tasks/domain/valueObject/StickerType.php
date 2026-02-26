<?php

namespace modules\tasks\domain\valueObject;

use InvalidArgumentException;

final class StickerType
{
    public const SYSTEM = 'system';
    public const USER = 'user';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::SYSTEM, self::USER], true)) {
            throw new InvalidArgumentException("Invalid sticker type: $value");
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isSystem(): bool
    {
        return $this->value === self::SYSTEM;
    }

    public function isUser(): bool
    {
        return $this->value === self::USER;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}