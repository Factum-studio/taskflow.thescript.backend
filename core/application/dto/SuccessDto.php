<?php

namespace core\application\dto;

class SuccessDto implements \JsonSerializable
{
    public function __construct(
        public mixed $data = null,
        public string $message = 'OK'
    ) {}

    public function jsonSerialize(): array
    {
        if ($this->data === null) {
            return ['message' => $this->message];
        }
        return $this->data;
    }
}