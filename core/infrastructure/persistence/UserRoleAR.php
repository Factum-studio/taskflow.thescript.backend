<?php

declare(strict_types=1);

namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property string $assigned_at
 * @property int|null $assigned_by
 */
class UserRoleAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_roles}}';
    }
}
