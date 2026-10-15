<?php

declare(strict_types=1);

namespace modules\company\domain\valueObject;

use InvalidArgumentException;

final class CompanyMemberRole
{
    public const OWNER = 'owner';
    public const ADMIN = 'admin';
    public const MEMBER = 'member';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::OWNER, self::ADMIN, self::MEMBER], true)) {
            throw new InvalidArgumentException("Invalid company member role: $value");
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isOwner(): bool
    {
        return $this->value === self::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isMember(): bool
    {
        return $this->value === self::MEMBER;
    }

    public function isAtLeastAdmin(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
