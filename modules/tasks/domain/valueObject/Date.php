<?php

namespace modules\tasks\domain\valueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class Date
{
    private DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value->setTime(0, 0);
    }

    public static function fromString(string $date): self
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$dt) {
            throw new InvalidArgumentException("Invalid date format, expected Y-m-d");
        }
        return new self($dt);
    }

    public function getValue(): DateTimeImmutable
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value->format('Y-m-d');
    }

    public function equals(self $other): bool
    {
        return $this->toString() === $other->toString();
    }
}