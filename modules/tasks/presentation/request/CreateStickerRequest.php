<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class CreateStickerRequest extends Model
{
    public string $name;
    public string $type;
    public ?int $projectId = null;
    public ?array $data = null;
    public ?string $color = null;

    public function rules(): array
    {
        return [
            [['name', 'type'], 'required'],
            ['name', 'string', 'max' => 100],
            ['type', 'in', 'range' => ['system', 'user']],
            ['projectId', 'integer'],
            ['data', 'safe'],
            ['color', 'string', 'max' => 20],
            // если type = user - projectId обязателен
            ['projectId', 'required', 'when' => function($model) {
                return $model->type === 'user';
            }, 'whenClient' => "function (attribute, value) {
                return $('#createstickerrequest-type').val() === 'user';
            }"],
            // eсли system - projectId должен быть null
            ['projectId', 'default', 'value' => null],
        ];
    }
}