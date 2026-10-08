<?php

declare(strict_types=1);

namespace core\presentation\request;

use yii\base\Model;

class RoleRequest extends Model
{
    public ?string $name = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 50],
            ['name', 'filter', 'filter' => 'mb_strtolower'],
            ['description', 'string', 'max' => 255],
        ];
    }
}
