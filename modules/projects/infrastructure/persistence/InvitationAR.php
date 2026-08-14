<?php

namespace modules\projects\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $project_id
 * @property string $email
 * @property int $invited_by
 * @property string $token
 * @property string $status
 * @property string $created_at
 * @property string $expires_at
 * @property string $updated_at
 */
class InvitationAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_invitation}}';
    }
}