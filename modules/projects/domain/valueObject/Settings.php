<?php

namespace modules\projects\domain\valueObject;

final class Settings
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function merge(array $data): self
    {
        return new self(array_merge($this->data, $data));
    }
}