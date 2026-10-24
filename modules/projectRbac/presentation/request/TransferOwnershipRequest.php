<?php

declare(strict_types=1);

namespace modules\projectRbac\presentation\request;

use yii\base\Model;

/**
 * POST /project/{id}/transfer
 * Body: { "new_owner_id": int }
 */
class TransferOwnershipRequest extends Model
{
    public ?int $new_owner_id = null;

    public function rules(): array
    {
        return [
            [['new_owner_id'], 'required'],
            ['new_owner_id', 'integer'],
        ];
    }
}
