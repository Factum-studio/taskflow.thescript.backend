<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\TaskStatus;
use modules\tasks\domain\valueObject\StatusId;

interface ITaskStatusRepository
{
    public function findById(StatusId $id): ?TaskStatus;
    /**
     * @return TaskStatus[]
     */
    public function findAll(): array;
}