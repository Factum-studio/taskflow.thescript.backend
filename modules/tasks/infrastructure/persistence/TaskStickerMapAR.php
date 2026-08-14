<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $task_id
 * @property int $sticker_id
 * @property string $created_at
 */
class TaskStickerMapAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_sticker_map}}';
    }
}