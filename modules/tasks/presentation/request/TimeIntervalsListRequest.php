<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class TimeIntervalsListRequest extends Model
{
    public ?int $taskId = null;
    public ?int $userId = null;
    public ?string $from = null;
    public ?string $to = null;
    public ?bool $activeOnly = false;

    public function rules(): array
    {
        return [
            [['taskId', 'userId'], 'integer'],
            [['from', 'to'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            ['activeOnly', 'boolean'],
        ];
    }
}