<?php

namespace modules\tasks\application\command;

class UpdateStickerCommand
{
    public int $id;
    public ?string $name;
    public ?array $data;
    public ?string $color;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $name = null,
        ?array $data = null,
        ?string $color = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->name         = $name;
        $this->data         = $data;
        $this->color        = $color;
    }
}