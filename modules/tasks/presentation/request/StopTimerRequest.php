<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class StopTimerRequest extends Model
{
    public int $intervalId;
    public ?string $comment = null;

    public function rules(): array
    {
        return [
            ['intervalId', 'required'],
            ['intervalId', 'integer'],
            ['comment', 'string'],
        ];
    }
}