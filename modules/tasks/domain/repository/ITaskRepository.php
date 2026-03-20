<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\Task;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\TaskId;

interface ITaskRepository
{
    public function save(Task $task): Task;
    public function findById(TaskId $id): ?Task;
    /**
     * @param array $criteria
     * @return Task[]
     */
    public function findAll(array $criteria = []): array;
    public function remove(Task $task): void;
    public function countByColumn(ColumnId $columnId): int;
}