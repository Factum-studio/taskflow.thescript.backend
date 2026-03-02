<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class DailySummaryRequest extends Model
{
    public int $userId;
    public string $date;

    public function rules(): array
    {
        return [
            [['userId', 'date'], 'required'],
            ['userId', 'integer'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }
}