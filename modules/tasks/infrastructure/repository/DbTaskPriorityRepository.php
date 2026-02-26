<?php

namespace modules\tasks\infrastructure\repository;

use modules\tasks\domain\entity\TaskPriority;
use modules\tasks\domain\repository\ITaskPriorityRepository;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\infrastructure\persistence\TaskPriorityAR;
use yii\db\Connection;

class DbTaskPriorityRepository implements ITaskPriorityRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findById(PriorityId $id): ?TaskPriority
    {
        $ar = TaskPriorityAR::findOne($id->getValue());
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findAll(): array
    {
        $ars = TaskPriorityAR::find()->orderBy(['value' => SORT_DESC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    private function mapARToEntity(TaskPriorityAR $ar): TaskPriority
    {
        return new TaskPriority(
            new PriorityId((int)$ar->id),
            (int)$ar->value,
            $ar->label,
            $ar->color
        );
    }
}