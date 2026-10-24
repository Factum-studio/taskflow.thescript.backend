<?php

declare(strict_types=1);

namespace modules\projectRbac\presentation\request;

use yii\base\Model;

/**
 * PUT /project/{id}/member/{memberId}/permission
 * Body: { "permission_code": "task.create", "is_granted": true }
 */
class SetUserPermissionRequest extends Model
{
    public ?string $permission_code = null;
    public ?bool $is_granted = null;

    public function rules(): array
    {
        return [
            [['permission_code', 'is_granted'], 'required'],
            ['permission_code', 'string', 'length' => ['min' => 1, 'max' => 100]],
            ['is_granted', 'boolean'],
        ];
    }
}
