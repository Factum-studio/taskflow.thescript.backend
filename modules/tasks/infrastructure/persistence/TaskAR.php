<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $status_id
 * @property int $priority_id
 * @property string|null $due_date
 * @property int $created_by
 * @property int|null $assigned_to
 * @property int $board_id
 * @property int|null $parent_id
 * @property int $overdue
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
class TaskAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%tasks}}';
    }
}