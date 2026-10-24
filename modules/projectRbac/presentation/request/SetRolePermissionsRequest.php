<?php

declare(strict_types=1);

namespace modules\projectRbac\presentation\request;

use yii\base\Model;

/**
 * PUT /project/{id}/role/{roleId}/permission
 * Body: { "permissions": ["task.create", "task.view", ...] }
 */
class SetRolePermissionsRequest extends Model
{
    /**
     * @var string[]
     */
    public ?array $permissions = null;

    public function rules(): array
    {
        return [
            [['permissions'], 'required'],
            ['permissions', 'array'],
        ];
    }
}
