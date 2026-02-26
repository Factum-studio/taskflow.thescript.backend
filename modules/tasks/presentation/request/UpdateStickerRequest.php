<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class UpdateStickerRequest extends Model
{
    public ?string $name = null;
    public ?array $data = null;
    public ?string $color = null;

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 100],
            ['data', 'safe'],
            ['color', 'string', 'max' => 20],
        ];
    }
}