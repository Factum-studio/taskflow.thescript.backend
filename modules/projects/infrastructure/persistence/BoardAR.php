<?php

namespace modules\projects\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string|null $description
 * @property int $created_by
 * @property array|null $settings
 * @property string $created_at
 * @property string $updated_at
 */
class BoardAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%board}}';
    }

    public function getProject()
    {
        return $this->hasOne(ProjectAR::class, ['id' => 'project_id']);
    }
}