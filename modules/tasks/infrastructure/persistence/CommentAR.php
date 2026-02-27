<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 */
class CommentAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task_comments}}';
    }
}