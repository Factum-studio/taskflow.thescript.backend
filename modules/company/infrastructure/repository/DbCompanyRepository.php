<?php

declare(strict_types=1);

namespace modules\company\infrastructure\repository;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\entity\Company;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyStatus;
use modules\company\infrastructure\persistence\CompanyAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use Exception;
use yii\db\StaleObjectException;

class DbCompanyRepository implements ICompanyRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     * @throws ValidationException
     */
    public function save(Company $company): Company
    {
        $ar = $this->findARById($company->getId()) ?? new CompanyAR();
        $this->mapEntityToAR($company, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save company: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$company->getId() || $company->getId()->value() !== (int)$ar->id) {
            $company->setId(new CompanyId((int)$ar->id));
        }

        return $company;
    }

    /**
     * @throws Exception
     */
    public function findById(CompanyId $id): ?Company
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findByName(string $name): ?Company
    {
        $ar = CompanyAR::find()->where(['name' => $name])->one();
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findByOwner(UserId $ownerId): array
    {
        $ars = CompanyAR::find()
            ->where(['owner_id' => $ownerId->value()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Exception
     */
    public function findByUser(UserId $userId): array
    {
        $ars = CompanyAR::find()
            ->leftJoin('{{%company_member}}', 'company.id = company_member.company_id')
            ->where(['or',
                ['company.owner_id' => $userId->value()],
                ['company_member.user_id' => $userId->value()],
            ])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Exception
     */
    public function findAll(?string $search = null, ?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $query = CompanyAR::find();

        if ($search !== null && $search !== '') {
            $query->andWhere(['like', 'name', $search]);
        }

        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => $status]);
        }

        $ars = $query->limit($limit)->offset($offset)->orderBy(['created_at' => SORT_DESC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function countAll(?string $search = null, ?string $status = null): int
    {
        $query = CompanyAR::find();

        if ($search !== null && $search !== '') {
            $query->andWhere(['like', 'name', $search]);
        }

        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => $status]);
        }

        return (int)$query->count();
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Company $company): void
    {
        $ar = $this->findARById($company->getId());
        $ar?->delete();
    }

    private function findARById(CompanyId $id): ?CompanyAR
    {
        return CompanyAR::findOne($id->value());
    }

    private function mapEntityToAR(Company $company, CompanyAR $ar): void
    {
        $ar->name = $company->getName();
        $ar->owner_id = $company->getOwnerId()->value();
        $ar->status = $company->getStatus()->value();
        $ar->verified = $company->isVerified();
        $ar->email = $company->getEmail();
        $ar->website = $company->getWebsite();
        $ar->phone = $company->getPhone();
        $ar->country = $company->getCountry();
        $ar->city = $company->getCity();
        $ar->address = $company->getAddress();
        $ar->tax_num = $company->getTaxNum();
        $ar->registration_num = $company->getRegistrationNum();
        $ar->description = $company->getDescription();
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(CompanyAR $ar): Company
    {
        return new Company(
            new CompanyId((int)$ar->id),
            $ar->name,
            new UserId((int)$ar->owner_id),
            new CompanyStatus($ar->status),
            (bool)$ar->verified,
            $ar->email,
            $ar->website,
            $ar->phone,
            $ar->country,
            $ar->city,
            $ar->address,
            $ar->tax_num,
            $ar->registration_num,
            $ar->description,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at),
        );
    }
}
