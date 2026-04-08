<?php
namespace core\domain\valueObject;

use InvalidArgumentException;

final class AuthorId
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Author ID must be positive');
        }
        $this->value = $value;
    }

    public function value(): int { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}