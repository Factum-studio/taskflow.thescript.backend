<?php

declare(strict_types=1);

namespace modules\projects\domain\repository;

use core\domain\valueObject\UserId;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\valueObject\ProjectId;

interface IProjectUserRepository
{
    public function save(ProjectUser $projectUser): void;

    public function remove(ProjectUser $projectUser): void;

    /** Mark a membership as left without deleting its history. */
    public function markLeft(ProjectId $projectId, UserId $userId): void;

    public function find(ProjectId $projectId, UserId $userId): ?ProjectUser;

    /**
     * @return ProjectUser[]
     */
    public function findByProject(ProjectId $projectId): array;

    /**
     * @return ProjectUser[]
     */
    public function findByUser(UserId $userId): array;
}
