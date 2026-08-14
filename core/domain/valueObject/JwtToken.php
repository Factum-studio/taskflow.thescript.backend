<?php

namespace core\domain\valueObject;

use core\domain\exception\InvalidJwtException;

final class JwtToken
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidJwtException('JWT token cannot be empty');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(JwtToken $other): bool
    {
        return $this->value === $other->value;
    }
}