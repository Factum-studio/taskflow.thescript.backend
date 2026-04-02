<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class AddProjectMemberRequest extends Model
{
    public int $userId;
    public string $role;

    public function rules(): array
    {
        return [
            [['userId', 'role'], 'required'],
            ['userId', 'integer'],
            ['role', 'in', 'range' => ['admin', 'member']],
        ];
    }
}