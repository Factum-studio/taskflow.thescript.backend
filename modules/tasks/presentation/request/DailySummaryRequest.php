<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class DailySummaryRequest extends Model
{
    public ?int $userId = null;
    public ?string $date = null;

    public function rules(): array
    {
        return [
            [['userId', 'date'], 'required'],
            ['userId', 'integer'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
        ];
    }
}
