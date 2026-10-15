<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class AttachStickerRequest extends Model
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
