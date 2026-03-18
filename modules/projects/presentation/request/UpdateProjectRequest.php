<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class UpdateProjectRequest extends Model
{
    public ?string $name = null;
    public ?array $settings = null;

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 255],
            ['settings', 'safe'],
        ];
    }
}