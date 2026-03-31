<?php

namespace modules\projects\presentation\request;

use yii\base\Model;

class InviteUserRequest extends Model
{
    public string $email;

    public function rules(): array
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
        ];
    }
}