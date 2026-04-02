<?php

namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $email
 * @property int $company_id
 * @property string|null $post
 * @property string $created_at
 * @property string $updated_at
 */
class UserAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }
}