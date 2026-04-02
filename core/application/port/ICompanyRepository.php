<?php
namespace core\application\port;

use core\domain\entity\Company;
use core\domain\valueObject\CompanyId;
use core\domain\valueObject\CompanyName;

interface ICompanyRepository
{
    public function save(Company $company): Company;
    public function findById(CompanyId $id): ?Company;
    /**
     * @return Company[]
     */
    public function findAll(): array;
    public function findByName(CompanyName $name): ?Company;
    /**
     * Удалить компании, у которых нет пользователей.
     * Возвращает количество удалённых записей.
     */
    public function removeEmptyCompanies(): int;
}