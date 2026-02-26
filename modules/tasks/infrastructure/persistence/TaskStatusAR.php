<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property int $sort_order
 * @property int|null $workflow_id
 * @property string $created_at
 * @property string $updated_at
 */
class TaskStatusAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_statuses}}';
    }
}