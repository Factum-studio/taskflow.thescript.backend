<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class ChangeTaskStatusRequest extends Model
{
    public int $statusId;

    public function rules(): array
    {
        return [
            [['statusId'], 'required'],
            [['statusId'], 'integer'],
        ];
    }
}