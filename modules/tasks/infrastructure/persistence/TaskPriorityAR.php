<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $value
 * @property string $label
 * @property string|null $color
 * @property string $created_at
 * @property string $updated_at
 */
class TaskPriorityAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_priorities}}';
    }
}