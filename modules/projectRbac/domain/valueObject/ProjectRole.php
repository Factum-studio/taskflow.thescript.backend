<?php

declare(strict_types=1);

namespace modules\projectRbac\domain\valueObject;

/**
 * Value object representing a project role.
 */
final class ProjectRole
{
    private int $id;
    private int $projectId;
    private string $name;
    private string $slug;
    private ProjectRoleRank $rank;
    private bool $isSystem;
    private bool $isPreset;

    public function __construct(
        int $id,
        int $projectId,
        string $name,
        string $slug,
        ProjectRoleRank $rank,
        bool $isSystem = false,
        bool $isPreset = false,
    ) {
        $this->id        = $id;
        $this->projectId = $projectId;
        $this->name      = $name;
        $this->slug      = $slug;
        $this->rank      = $rank;
        $this->isSystem  = $isSystem;
        $this->isPreset  = $isPreset;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getRank(): ProjectRoleRank
    {
        return $this->rank;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function isPreset(): bool
    {
        return $this->isPreset;
    }

}
