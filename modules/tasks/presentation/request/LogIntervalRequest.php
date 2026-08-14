<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class LogIntervalRequest extends Model
{
    public int $taskId;
    public string $startTime;
    public string $endTime;
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