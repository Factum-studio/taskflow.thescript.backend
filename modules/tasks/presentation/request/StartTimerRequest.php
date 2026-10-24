<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class StartTimerRequest extends Model
{
    public ?int $taskId = null;
    public ?string $comment = null;

    public function rules(): array
    {
        return [
            ['taskId', 'required'],
            ['taskId', 'integer'],
            ['comment', 'string'],
        ];
    }
}
