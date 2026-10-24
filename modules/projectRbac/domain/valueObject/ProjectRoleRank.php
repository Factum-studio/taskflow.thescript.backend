<?php

declare(strict_types=1);

namespace modules\projectRbac\domain\valueObject;

use InvalidArgumentException;

/**
 * Value object for role rank, defining hierarchy.
 *
 * Built-in constants:
 *   ADMIN_RANK   = 100
 *   MANAGER_RANK =  50
 *   MEMBER_RANK  =  10
 */
final class ProjectRoleRank
{
    public const ADMIN_RANK   = 100;
    public const MANAGER_RANK = 50;
    public const MEMBER_RANK  = 10;

    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0 || $value > 999) {
            throw new InvalidArgumentException(
                'Role rank must be between 0 and 999, got ' . $value,
            );
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    /** Can $this assign a role with the given $target rank? (strictly lower rank) */
    public function canAssign(self $target): bool
    {
        return $this->value > $target->value;
    }

    /** Can $this remove a role with the given $target rank? (< own rank) */
    public function canRemove(self $target): bool
    {
        return $this->value > $target->value;
    }

    /** Can $this manage permissions of a role with the given $target rank? (< own rank) */
    public function canManagePermissions(self $target): bool
    {
        return $this->value > $target->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(self $other): bool
    {
        return $this->value < $other->value;
    }
}
