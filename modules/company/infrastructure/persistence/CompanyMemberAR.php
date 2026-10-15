<?php

declare(strict_types=1);

namespace modules\company\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $company_id
 * @property int $user_id
 * @property string $role
 * @property int|null $invited_by
 * @property string|null $invited_at
 * @property string|null $accepted_at
 * @property string $joined_at
 */
class CompanyMemberAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%company_member}}';
    }

    public function getCompany()
    {
        return $this->hasOne(CompanyAR::class, ['id' => 'company_id']);
    }
}
