<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class UpdateCommentRequest extends Model
{
    public string $content;

    public function rules(): array
    {
        return [
            ['content', 'required'],
            ['content', 'string', 'min' => 1],
        ];
    }
}