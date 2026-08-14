<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class TaskTimeSummaryRequest extends Model
{
    public ?int $userId = null;
    public ?string $from = null;
    public ?string $to = null;
    public string $granularity = 'minute';
    public string $mode = 'merged';

    public function rules(): array
    {
        return [
            [['userId'], 'integer'],
            [['from', 'to'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            ['granularity', 'in', 'range' => ['minute', 'ten_minutes', 'hour']],
            ['mode', 'in', 'range' => ['merged', 'separate', 'overlap']],
        ];
    }
}