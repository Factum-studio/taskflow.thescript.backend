<?php

declare(strict_types=1);

namespace modules\projectRbac\presentation\request;

use yii\base\Model;

/**
 * POST /project/{id}/role
 */
class CreateRoleRequest extends Model
{
    public ?string $name = null;
    public ?int $rank = null;

    public function rules(): array
    {
        return [
            [['name', 'rank'], 'required'],
            ['name', 'string', 'length' => ['min' => 1, 'max' => 100]],
            ['rank', 'integer', 'range' => [1, 999]],
        ];
    }
}
