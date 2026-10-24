<?php

declare(strict_types=1);

namespace modules\projectRbac\domain\valueObject;

use InvalidArgumentException;

/**
 * Value object representing a single permission code (e.g. "task.create").
 */
final class PermissionCode
{
    private string $code;

    public function __construct(string $code)
    {
        $trimmed = trim($code);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Permission code cannot be empty');
        }
        if (mb_strlen($trimmed) > 100) {
            throw new InvalidArgumentException('Permission code must not exceed 100 characters');
        }
        $this->code = $trimmed;
    }

    public function value(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
