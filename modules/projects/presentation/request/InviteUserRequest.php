<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class InviteUserRequest extends Model
{
    public ?string $email   = null;
    public ?int $userId     = null;

    public function rules(): array
    {
        return [
            [['email', 'userId'], 'required', 'message' => 'Either email or userId must be provided', 'when' => function($model) {
                return empty($model->email) && empty($model->userId);
            }],
            ['email', 'email'],
            ['userId', 'integer'],
        ];
    }
}