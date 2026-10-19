<?php

declare(strict_types=1);

namespace modules\tasks\presentation\request;

use yii\base\Model;

class AddCommentRequest extends Model
{
    public ?string $content = null;

    public function rules(): array
    {
        return [
            ['content', 'required'],
            ['content', 'string', 'min' => 1],
        ];
    }
}
