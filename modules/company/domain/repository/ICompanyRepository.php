<?php

declare(strict_types=1);

namespace modules\company\domain\repository;

use modules\company\domain\entity\Company;
use modules\company\domain\valueObject\CompanyId;
use core\domain\valueObject\UserId;

interface ICompanyRepository
{
    public function save(Company $company): Company;

    public function findById(CompanyId $id): ?Company;

    public function findByName(string $name): ?Company;

    /**
     * @return Company[]
     */
    public function findByOwner(UserId $ownerId): array;

    /**
     * @return Company[]
     */
    public function findByUser(UserId $userId): array;

    /**
     * @return Company[]
     */
    public function findAll(?string $search = null, ?string $status = null, int $limit = 20, int $offset = 0): array;

    public function countAll(?string $search = null, ?string $status = null): int;

    public function remove(Company $company): void;
}
