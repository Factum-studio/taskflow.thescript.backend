<?php

namespace modules\tasks\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $board_id
 * @property string $name
 * @property string $label
 * @property int $sort_order
 * @property int $is_active
 * @property int $is_final
 * @property string|null $color
 * @property int|null $workflow_id
 * @property string $created_at
 * @property string $updated_at
 */
class BoardColumnAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%board_columns}}';
    }
}