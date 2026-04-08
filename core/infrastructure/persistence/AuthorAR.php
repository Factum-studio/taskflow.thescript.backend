<?php
namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $phrase
 * @property string $badges (JSON)
 * @property string $created_at
 * @property string $updated_at
 */
class AuthorAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%author}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'phrase', 'badges'], 'required'],
            [['user_id'], 'integer'],
            [['phrase'], 'string', 'min' => 10, 'max' => 50],
            [['badges'], 'safe'],
            [['user_id'], 'unique'],
        ];
    }
}