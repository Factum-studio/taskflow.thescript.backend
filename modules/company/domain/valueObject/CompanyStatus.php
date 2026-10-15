<?php

declare(strict_types=1);

namespace modules\company\domain\valueObject;

use InvalidArgumentException;

final class CompanyStatus
{
    public const ACTIVE = 'active';
    public const ARCHIVED = 'archived';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::ACTIVE, self::ARCHIVED], true)) {
            throw new InvalidArgumentException("Invalid company status: $value");
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
