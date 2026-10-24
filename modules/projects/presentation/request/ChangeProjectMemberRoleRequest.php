<?php

declare(strict_types=1);

namespace modules\projects\presentation\request;

use yii\base\Model;

class ChangeProjectMemberRoleRequest extends Model
{
    public ?string $role = null;

    public function rules(): array
    {
        return [
            ['role', 'required'],
            // TODO check logic
            ['role', 'in', 'range' => ['admin', 'member']],
        ];
    }
}
