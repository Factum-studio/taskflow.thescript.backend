<?php

namespace modules\projects\application\command;

class UpdateProjectCommand
{
    public int $id;
    public ?string $name = null;
    public ?array $settings = null;
    public int $updatedBy;

    public function __construct(
        int $id,
        int $updatedBy,
        ?string $name = null,
        ?array $settings = null
    ) {
        $this->id           = $id;
        $this->updatedBy    = $updatedBy;
        $this->name         = $name;
        $this->settings     = $settings;
    }
}