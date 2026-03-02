<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $start_time
 * @property string|null $end_time
 * @property int|null $duration
 * @property string|null $comment
 * @property string $created_at
 * @property string $updated_at
 */
class TimeIntervalAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_time_intervals}}';
    }
}