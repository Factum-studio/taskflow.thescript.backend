<?php

namespace modules\projects\domain\valueObject;

use InvalidArgumentException;

final class UserRole
{
    public const ADMIN = 'admin';
    public const MEMBER = 'member';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::ADMIN, self::MEMBER], true)) {
            throw new InvalidArgumentException("Invalid user role: $value");
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isMember(): bool
    {
        return $this->value === self::MEMBER;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}