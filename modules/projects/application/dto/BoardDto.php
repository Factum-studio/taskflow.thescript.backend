<?php

namespace modules\projects\application\dto;

class BoardDto
{
    public int $id;
    public int $projectId;
    public string $name;
    public ?string $description;
    public int $createdBy;
    public array $settings;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->projectId    = $data['projectId'];
        $this->name         = $data['name'];
        $this->description  = $data['description'] ?? null;
        $this->createdBy    = $data['createdBy'];
        $this->settings     = $data['settings'] ?? [];
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}