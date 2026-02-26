<?php

namespace modules\tasks\infrastructure\repository;

use modules\tasks\domain\entity\TaskStatus;
use modules\tasks\domain\repository\ITaskStatusRepository;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\infrastructure\persistence\TaskStatusAR;
use yii\db\Connection;

class DbTaskStatusRepository implements ITaskStatusRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findById(StatusId $id): ?TaskStatus
    {
        $ar = TaskStatusAR::findOne($id->getValue());
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findAll(): array
    {
        $ars = TaskStatusAR::find()->orderBy(['sort_order' => SORT_ASC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    private function mapARToEntity(TaskStatusAR $ar): TaskStatus
    {
        return new TaskStatus(
            new StatusId((int)$ar->id),
            $ar->name,
            $ar->label,
            (int)$ar->sort_order,
            $ar->workflow_id ? (int)$ar->workflow_id : null
        );
    }
}