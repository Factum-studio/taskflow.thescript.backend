<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $date
 * @property int $total_duration
 * @property string $updated_at
 */
class DailySummaryAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_daily_time_summary}}';
    }
}