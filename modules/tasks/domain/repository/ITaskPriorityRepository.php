<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\TaskPriority;
use modules\tasks\domain\valueObject\PriorityId;

interface ITaskPriorityRepository
{
    public function findById(PriorityId $id): ?TaskPriority;
    /**
     * @return TaskPriority[]
     */
    public function findAll(): array;
}