<?php

namespace modules\projects\domain\repository;

use modules\projects\domain\entity\Project;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;

interface IProjectRepository
{
    public function save(Project $project): Project;

    public function findById(ProjectId $id): ?Project;

    /**
     * @return Project[]
     */
    public function findByOwner(UserId $ownerId): array;

    /**
     * @return Project[]
     */
    public function findByUser(UserId $userId): array; // проекты, где пользователь участник (включая владельца)
    public function countByOwner(UserId $ownerId): int;
    public function remove(Project $project): void;
}