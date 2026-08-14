<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class CreateBoardRequest extends Model
{
    public string $name;
    public ?string $description = null;
    public ?array $settings = null;

    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 255],
            ['description', 'string'],
            ['settings', 'safe'],
        ];
    }
}