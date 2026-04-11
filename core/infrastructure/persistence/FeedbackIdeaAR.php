<?php
namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $comment
 * @property bool $is_implemented
 * @property string $created_at
 * @property string $updated_at
 */
class FeedbackIdeaAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%feedback_idea}}';
    }
}