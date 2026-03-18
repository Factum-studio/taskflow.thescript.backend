<?php

namespace modules\projects\application\command;

class UpdateBoardCommand
{
    public int $id;
    public ?string $name = null;
    public ?string $description = null;
    public ?array $settings = null;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $name = null,
        ?string $description = null,
        ?array $settings = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->name         = $name;
        $this->description  = $description;
        $this->settings     = $settings;
    }
}