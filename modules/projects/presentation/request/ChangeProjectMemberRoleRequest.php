<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class ChangeProjectMemberRoleRequest extends Model
{
    public string $role;

    public function rules(): array
    {
        return [
            ['role', 'required'],
            ['role', 'in', 'range' => ['admin', 'member']],
        ];
    }
}