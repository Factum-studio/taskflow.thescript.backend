<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class AssignTaskRequest extends Model
{
    public ?int $assignedTo = null;

    public function rules(): array
    {
        return [
            [['assignedTo'], 'integer'],
        ];
    }
}
