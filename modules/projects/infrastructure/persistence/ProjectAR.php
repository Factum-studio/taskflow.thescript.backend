<?php

namespace modules\projects\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $owner_id
 * @property array|null $settings
 * @property string $created_at
 * @property string $updated_at
 */
class ProjectAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project}}';
    }
}