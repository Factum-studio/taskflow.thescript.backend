<?php

namespace modules\projects\domain\repository;

use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;

interface IProjectUserRepository
{
    public function save(ProjectUser $projectUser): void;

    public function remove(ProjectUser $projectUser): void;

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