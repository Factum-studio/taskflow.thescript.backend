<?php

declare(strict_types=1);

namespace modules\projects\presentation\request;

use yii\base\Model;

class AddProjectMemberRequest extends Model
{
    public ?int $userId = null;
    public ?string $role = null;

    public function rules(): array
    {
        return [
            [['userId', 'role'], 'required'],
            ['userId', 'integer'],
            // TODO check logic
            ['role', 'in', 'range' => ['admin', 'member']],
        ];
    }
}
