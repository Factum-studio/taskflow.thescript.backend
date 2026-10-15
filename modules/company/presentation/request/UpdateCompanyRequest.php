<?php

declare(strict_types=1);

namespace modules\company\presentation\request;

use yii\base\Model;

class UpdateCompanyRequest extends Model
{
    public ?string $name = null;
    public ?string $email = null;
    public ?string $website = null;
    public ?string $phone = null;
    public ?string $country = null;
    public ?string $city = null;
    public ?string $address = null;
    public ?string $taxNum = null;
    public ?string $registrationNum = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [['website', 'phone', 'country', 'city', 'address', 'taxNum', 'registrationNum'], 'string', 'max' => 255],
            ['description', 'string'],
        ];
    }
}
