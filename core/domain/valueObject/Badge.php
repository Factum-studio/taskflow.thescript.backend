<?php
namespace core\domain\valueObject;

final class Badge
{
    private string $label;

    public function __construct(string $label)
    {
        $label = trim($label);
        if ($label === '') {
            throw new \InvalidArgumentException('Badge label cannot be empty');
        }
        if (mb_strlen($label) > 16) {
            throw new \InvalidArgumentException('Badge label too long (max 16)');
        }
        $this->label = $label;
    }

    public function value(): string { return $this->label; }
    public function equals(self $other): bool { return $this->label === $other->label; }
}