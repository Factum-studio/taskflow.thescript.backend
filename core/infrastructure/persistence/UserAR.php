<?php

declare(strict_types=1);

namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $passport_id
 * @property string $email
 * @property string $surname
 * @property string $name
 * @property string|null $patronymic
 * @property string|null $post
 * @property bool $is_owner
 * @property string|null $synced_at
 * @property string $created_at
 * @property string $updated_at
 */
class UserAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }
}
