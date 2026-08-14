<?php
namespace core\domain\valueObject;

use InvalidArgumentException;

final class Rating
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0 || $value > 5) {
            throw new InvalidArgumentException('Rating must be between 0 and 5');
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function isMax(): bool
    {
        return $this->value >= 4;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}