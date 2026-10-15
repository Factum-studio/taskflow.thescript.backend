<?php

declare(strict_types=1);

namespace modules\company\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property string $status
 * @property bool $verified
 * @property string|null $email
 * @property string|null $website
 * @property string|null $phone
 * @property string|null $country
 * @property string|null $city
 * @property string|null $address
 * @property string|null $tax_num
 * @property string|null $registration_num
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 */
class CompanyAR extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%company}}';
    }

    public function getMembers()
    {
        return $this->hasMany(CompanyMemberAR::class, ['company_id' => 'id']);
    }

    public function getInvitations()
    {
        return $this->hasMany(CompanyInvitationAR::class, ['company_id' => 'id']);
    }
}
