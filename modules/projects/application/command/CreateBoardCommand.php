<?php

namespace modules\projects\application\command;

class CreateBoardCommand
{
    public int $projectId;
    public string $name;
    public ?string $description;
    public int $createdBy;
    public array $settings = [];

    public function __construct(
        int $projectId,
        string $name,
        int $createdBy,
        ?string $description = null,
        array $settings = []
    ) {
        $this->projectId    = $projectId;
        $this->name         = $name;
        $this->createdBy    = $createdBy;
        $this->description  = $description;
        $this->settings     = $settings;
    }
}