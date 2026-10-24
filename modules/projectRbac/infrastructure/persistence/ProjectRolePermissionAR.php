<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $role_id
 * @property string $permission_code
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class ProjectRolePermissionAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_role_permissions}}';
    }
}
