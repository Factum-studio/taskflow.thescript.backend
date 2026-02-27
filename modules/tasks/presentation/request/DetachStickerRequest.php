<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class DetachStickerRequest extends Model
{
    public int $stickerId;

    public function rules(): array
    {
        return [
            ['stickerId', 'required'],
            ['stickerId', 'integer'],
        ];
    }
}