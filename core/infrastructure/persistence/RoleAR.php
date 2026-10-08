<?php

declare(strict_types=1);

namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $created_at
 */
class RoleAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%roles}}';
    }
}
