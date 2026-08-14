<?php
namespace core\domain\valueObject;

use InvalidArgumentException;

final class CompanyName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException('Company name cannot be empty');
        }
        if (mb_strlen($value) > 255) {
            throw new InvalidArgumentException('Company name too long');
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}