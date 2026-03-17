<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class UpdateBoardColumnRequest extends Model
{
    public ?string $name = null;
    public ?string $label = null;
    public ?int $sortOrder = null;
    public ?bool $isActive = null;
    public ?bool $isFinal = null;
    public ?string $color = null;
    public ?int $workflowId = null;

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 50],
            ['label', 'string', 'max' => 255],
            ['sortOrder', 'integer'],
            ['isActive', 'boolean'],
            ['isFinal', 'boolean'],
            ['color', 'string', 'max' => 20],
            ['workflowId', 'integer'],
        ];
    }
}