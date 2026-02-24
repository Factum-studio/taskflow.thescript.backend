<?php

namespace core\domain\valueObject;


final class Identify
{
    private function __construct(
        private string $id
    ) {}

    public static function fromString(string $id): self
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Identity cannot be empty');
        }

        return new self($id);
    }

    public function value(): string
    {
        return $this->id;
    }
}