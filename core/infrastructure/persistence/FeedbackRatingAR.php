<?php
namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $speed
 * @property int $functionality
 * @property int $design
 * @property int $usability
 * @property string $created_at
 * @property string $updated_at
 */
class FeedbackRatingAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%feedback_rating}}';
    }
}