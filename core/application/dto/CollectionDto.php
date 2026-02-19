<?php

namespace core\application\dto;

class CollectionDto implements \JsonSerializable
{
    public function __construct(
        public array $items,
        public ?int $total = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
        ];
    }
}