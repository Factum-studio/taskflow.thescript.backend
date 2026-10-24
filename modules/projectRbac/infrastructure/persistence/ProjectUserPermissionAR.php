<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $project_id
 * @property int $user_id
 * @property string $permission_code
 * @property bool $is_granted
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class ProjectUserPermissionAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_user_permissions}}';
    }
}
