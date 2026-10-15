<?php

declare(strict_types=1);

namespace modules\company\domain\repository;

use core\domain\valueObject\UserId;
use modules\company\domain\entity\CompanyMember;
use modules\company\domain\valueObject\CompanyId;

interface ICompanyMemberRepository
{
    public function save(CompanyMember $member): CompanyMember;

    public function find(CompanyId $companyId, UserId $userId): ?CompanyMember;

    /**
     * @return CompanyMember[]
     */
    public function findByCompany(CompanyId $companyId): array;

    /**
     * @return CompanyMember[]
     */
    public function findByUser(UserId $userId): array;

    public function countByCompany(CompanyId $companyId): int;

    public function countByRole(CompanyId $companyId, string $role): int;

    public function remove(CompanyMember $member): void;
}