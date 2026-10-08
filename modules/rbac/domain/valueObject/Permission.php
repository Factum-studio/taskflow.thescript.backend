<?php

declare(strict_types=1);

namespace modules\rbac\domain\valueObject;

use InvalidArgumentException;

final class Permission
{
    public function __construct(
        private readonly int $id,
        private readonly string $code,
    ) {
        if ($this->id < 0) {
            throw new InvalidArgumentException('Permission ID cannot be negative.');
        }

        if ($this->code === '') {
            throw new InvalidArgumentException('Permission code cannot be empty.');
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }
}
