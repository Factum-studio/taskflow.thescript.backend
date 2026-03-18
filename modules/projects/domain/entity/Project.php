<?php

namespace modules\projects\domain\entity;

use DateTimeImmutable;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\ProjectType;
use modules\projects\domain\valueObject\Settings;
use InvalidArgumentException;

class Project
{
    private ProjectId $id;
    private string $name;
    private ProjectType $type;
    private UserId $ownerId;
    private Settings $settings;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ProjectId $id,
        string $name,
        ProjectType $type,
        UserId $ownerId,
        ?Settings $settings = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->setName($name);
        $this->id           = $id;
        $this->type         = $type;
        $this->ownerId      = $ownerId;
        $this->settings     = $settings ?? new Settings();
        $this->createdAt    = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): ProjectId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ProjectType
    {
        return $this->type;
    }

    public function getOwnerId(): UserId
    {
        return $this->ownerId;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $newName): void
    {
        $this->setName($newName);
        $this->touch();
    }

    public function changeSettings(array $newSettings): void
    {
        $this->settings = $this->settings->merge($newSettings);
        $this->touch();
    }

    public function isOwner(UserId $userId): bool
    {
        return $this->ownerId->getValue() === $userId->getValue();
    }

    /**
     * @internal For repository use only
     */
    public function setId(ProjectId $id): void
    {
        $this->id = $id;
    }

    private function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Project name cannot be empty');
        }
        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Project name must not exceed 255 characters');
        }
        $this->name = $name;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}