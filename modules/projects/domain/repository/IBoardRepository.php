<?php

namespace modules\projects\domain\repository;

use modules\projects\domain\entity\Board;
use modules\projects\domain\valueObject\BoardId;
use modules\projects\domain\valueObject\ProjectId;

interface IBoardRepository
{
    public function save(Board $board): Board;

    public function findById(BoardId $id): ?Board;

    /**
     * @return Board[]
     */
    public function findByProject(ProjectId $projectId): array;

    public function remove(Board $board): void;
    public function countByProject(ProjectId $projectId): int;
}