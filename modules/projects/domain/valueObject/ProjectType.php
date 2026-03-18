<?php

namespace modules\projects\domain\valueObject;

use InvalidArgumentException;

final class ProjectType
{
    public const PERSONAL = 'personal';
    public const COLLABORATIVE = 'collaborative';
    public const CORPORATE = 'corporate';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::PERSONAL, self::COLLABORATIVE, self::CORPORATE], true)) {
            throw new InvalidArgumentException("Invalid project type: $value");
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPersonal(): bool
    {
        return $this->value === self::PERSONAL;
    }

    public function isCollaborative(): bool
    {
        return $this->value === self::COLLABORATIVE;
    }

    public function isCorporate(): bool
    {
        return $this->value === self::CORPORATE;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}