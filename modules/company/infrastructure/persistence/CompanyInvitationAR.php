<?php

declare(strict_types=1);

namespace modules\company\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $company_id
 * @property string $email
 * @property int $invited_by
 * @property string $token
 * @property string $status
 * @property string $created_at
 * @property string $expires_at
 * @property string $updated_at
 */
class CompanyInvitationAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%company_invitation}}';
    }

    public function getCompany()
    {
        return $this->hasOne(CompanyAR::class, ['id' => 'company_id']);
    }
}
