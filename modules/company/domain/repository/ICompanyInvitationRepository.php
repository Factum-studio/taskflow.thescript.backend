<?php

declare(strict_types=1);

namespace modules\company\domain\repository;

use modules\company\domain\entity\CompanyInvitation;
use modules\company\domain\valueObject\CompanyId;

interface ICompanyInvitationRepository
{
    public function save(CompanyInvitation $invitation): CompanyInvitation;

    public function findById(int $id): ?CompanyInvitation;

    public function findByToken(string $token): ?CompanyInvitation;

    public function findPendingByCompanyAndEmail(CompanyId $companyId, string $email): ?CompanyInvitation;

    public function findPendingByEmail(string $email): array;

    public function deleteExpired(): int;

    public function remove(CompanyInvitation $invitation): void;
}
