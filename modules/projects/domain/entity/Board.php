<?php

namespace modules\projects\domain\entity;

use DateTimeImmutable;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\Settings;
use InvalidArgumentException;

class Board
{
    private BoardId $id;
    private ProjectId $projectId;
    private string $name;
    private ?string $description;
    private UserId $createdBy;
    private Settings $settings;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        BoardId $id,
        ProjectId $projectId,
        string $name,
        UserId $createdBy,
        ?string $description = null,
        ?Settings $settings = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->setName($name);
        $this->id           = $id;
        $this->projectId    = $projectId;
        $this->description  = $description;
        $this->createdBy    = $createdBy;
        $this->settings     = $settings ?? new Settings();
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): BoardId { return $this->id; }
    public function getProjectId(): ProjectId { return $this->projectId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedBy(): UserId { return $this->createdBy; }
    public function getSettings(): Settings { return $this->settings; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function rename(string $newName): void
    {
        $this->setName($newName);
        $this->touch();
    }

    public function changeDescription(?string $newDescription): void
    {
        $this->description = $newDescription;
        $this->touch();
    }

    public function changeSettings(array $newSettings): void
    {
        $this->settings = $this->settings->merge($newSettings);
        $this->touch();
    }

    /**
     * @internal For repository use only
     */
    public function setId(BoardId $id): void
    {
        $this->id = $id;
    }

    private function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Board name cannot be empty');
        }
        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Board name must not exceed 255 characters');
        }
        $this->name = $name;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}