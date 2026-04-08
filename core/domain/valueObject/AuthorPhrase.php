<?php
namespace core\domain\valueObject;

use InvalidArgumentException;

final class AuthorPhrase
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        $len = mb_strlen($value);
        if ($len < 10 || $len > 50) {
            throw new InvalidArgumentException('Author phrase must be between 10 and 50 characters');
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}