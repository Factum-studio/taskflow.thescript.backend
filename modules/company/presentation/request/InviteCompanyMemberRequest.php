<?php

declare(strict_types=1);

namespace modules\company\presentation\request;

use yii\base\Model;

class InviteCompanyMemberRequest extends Model
{
    public ?string $email = null;

    public function rules(): array
    {
        return [
            [['email'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
        ];
    }
}
