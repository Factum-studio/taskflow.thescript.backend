<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $project_id
 * @property array|null $data
 * @property string|null $color
 * @property int $created_by
 * @property string $created_at
 * @property string $updated_at
 */
class StickerAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%stickers}}';
    }
}