<?php

namespace modules\projects\application\command;

class CreateProjectCommand
{
    public string $name;
    public string $type;
    public int $ownerId;
    public array $settings = [];

    public function __construct(
        string $name,
        string $type,
        int $ownerId,
        array $settings = []
    ) {
        $this->name     = $name;
        $this->type     = $type;
        $this->ownerId  = $ownerId;
        $this->settings = $settings;
    }
}