<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property string $slug
 * @property int $rank
 * @property bool $is_system
 * @property bool $is_preset
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class ProjectRoleAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_roles}}';
    }
}
