<?php

declare(strict_types=1);

namespace modules\company\presentation\request;

use yii\base\Model;

class AcceptInvitationRequest extends Model
{
    public string $token;

    public function rules(): array
    {
        return [
            [['token'], 'required'],
            ['token', 'string'],
        ];
    }
}