<?php

namespace modules\projects\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $project_id
 * @property int $user_id
 * @property string $role
 * @property int|null $invited_by
 * @property string|null $invited_at
 * @property string|null $accepted_at
 * @property string $joined_at
 */
class ProjectUserAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_user}}';
    }

    public function getProject()
    {
        return $this->hasOne(ProjectAR::class, ['id' => 'project_id']);
    }
}