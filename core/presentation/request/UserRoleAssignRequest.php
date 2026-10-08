<?php

declare(strict_types=1);

namespace core\presentation\request;

use yii\base\Model;

class UserRoleAssignRequest extends Model
{
    public ?int $userId = null;
    public ?int $roleId = null;
    public ?int $assignedBy = null;

    public function rules(): array
    {
        return [
            [['userId', 'roleId'], 'required'],
            [['userId', 'roleId', 'assignedBy'], 'integer'],
        ];
    }
}
