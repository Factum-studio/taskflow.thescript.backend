<?php
namespace core\infrastructure\repository;

use core\application\port\ICompanyRepository;
use core\domain\entity\Company;
use core\domain\valueObject\CompanyId;
use core\domain\valueObject\CompanyName;
use core\domain\valueObject\CompanyDescription;
use core\infrastructure\persistence\CompanyAR;
use RuntimeException;
use DateTimeImmutable;
use yii\db\Connection;
use yii\db\Exception;

class DbCompanyRepository implements ICompanyRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function save(Company $company): Company
    {
        $ar = $company->getId() && $company->getId()->value() > 0
            ? CompanyAR::findOne($company->getId()->value())
            : new CompanyAR();

        $ar->name = $company->getName()->value();
        $ar->description = $company->getDescription()?->value();

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save company: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$company->getId() || $company->getId()->value() !== (int)$ar->id) {
            $company = new Company(
                new CompanyId((int)$ar->id),
                $company->getName(),
                $company->getDescription(),
                $company->getCreatedAt(),
                $company->getUpdatedAt()
            );
        }
        return $company;
    }

    public function findById(CompanyId $id): ?Company
    {
        $ar = CompanyAR::findOne($id->value());
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findAll(): array
    {
        $ars = CompanyAR::find()->orderBy(['name' => SORT_ASC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findByName(CompanyName $name): ?Company
    {
        $ar = CompanyAR::find()->where(['name' => $name->value()])->one();
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function removeEmptyCompanies(): int
    {
        // Удаляем компании, у которых нет пользователей (user.company_id = company.id)
        $sql = 'DELETE FROM {{%company}} c
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM {{%user}} u 
                    WHERE u.company_id = c.id
                )';
        return $this->db->createCommand($sql)->execute();
    }

    /**
     * @throws \Exception
     */
    private function mapARToEntity(CompanyAR $ar): Company
    {
        return new Company(
            new CompanyId((int)$ar->id),
            new CompanyName($ar->name),
            $ar->description !== null ? new CompanyDescription($ar->description) : null,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}