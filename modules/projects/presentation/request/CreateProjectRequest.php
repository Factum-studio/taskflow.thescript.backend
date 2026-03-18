<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class CreateProjectRequest extends Model
{
    public string $name;
    public string $type;
    public ?array $settings = null;

    public function rules(): array
    {
        return [
            [['name', 'type'], 'required'],
            ['name', 'string', 'max' => 255],
            ['type', 'in', 'range' => ['personal', 'collaborative', 'corporate']],
            ['settings', 'safe'],
        ];
    }
}