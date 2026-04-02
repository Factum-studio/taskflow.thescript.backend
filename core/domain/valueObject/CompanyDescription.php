<?php
namespace core\domain\valueObject;

final class CompanyDescription
{
    private ?string $value;

    public function __construct(?string $value)
    {
        $this->value = $value !== null ? trim($value) : null;
    }

    public function value(): ?string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}