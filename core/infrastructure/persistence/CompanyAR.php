<?php
namespace core\infrastructure\persistence;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
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
}