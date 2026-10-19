<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class LogIntervalRequest extends Model
{
    public ?int $taskId = null;
    public ?string $startTime = null;
    public ?string $endTime = null;
    public ?string $comment = null;

    public function rules(): array
    {
        return [
            [['taskId', 'startTime', 'endTime'], 'required'],
            ['taskId', 'integer'],
            [['startTime', 'endTime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            ['comment', 'string'],
        ];
    }
}
