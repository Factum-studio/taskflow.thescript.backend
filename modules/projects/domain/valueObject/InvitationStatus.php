<?php

namespace modules\projects\domain\valueObject;

use InvalidArgumentException;

final class InvitationStatus
{
    public const PENDING = 'pending';
    public const ACCEPTED = 'accepted';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::PENDING, self::ACCEPTED, self::EXPIRED, self::CANCELLED], true)) {
            throw new InvalidArgumentException("Invalid invitation status: $value");
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->value === self::ACCEPTED;
    }

    public function isExpired(): bool
    {
        return $this->value === self::EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}