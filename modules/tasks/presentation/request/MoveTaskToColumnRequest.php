<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class MoveTaskToColumnRequest extends Model
{
    public int $columnId;

    public function rules(): array
    {
        return [
            ['columnId', 'required'],
            ['columnId', 'integer'],
        ];
    }
}