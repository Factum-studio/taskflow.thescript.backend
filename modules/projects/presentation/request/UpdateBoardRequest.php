<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class UpdateBoardRequest extends Model
{
    public ?string $name = null;
    public ?string $description = null;
    public ?array $settings = null;

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 255],
            ['description', 'string'],
            ['settings', 'safe'],
        ];
    }
}