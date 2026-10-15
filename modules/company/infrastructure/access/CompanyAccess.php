<?php

declare(strict_types=1);

namespace modules\company\infrastructure\access;

use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\entity\Company;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\rbac\application\port\IAuthorizationService;

class CompanyAccess implements ICompanyAccess
{
    private const PERMISSION_MANAGE_COMPANIES = 'system.manage';

    public function __construct(
        private ICompanyRepository $companyRepository,
        private ICompanyMemberRepository $companyMemberRepository,
        private IAuthorizationService $authorizationService,
    ) {
    }

    public function canViewCompany(int $userId, int $companyId): bool
    {
        return $this->isGlobalAdmin($userId) || $this->isCompanyMember($userId, $companyId);
    }

    public function canManageCompany(int $userId, int $companyId): bool
    {
        return $this->isGlobalAdmin($userId) || $this->isCompanyAdminOrOwner($userId, $companyId);
    }

    public function canArchiveCompany(int $userId, int $companyId): bool
    {
        return $this->isGlobalAdmin($userId) || $this->isCompanyAdminOrOwner($userId, $companyId);
    }

    public function canDeleteCompany(int $userId, int $companyId): bool
    {
        return $this->isGlobalAdmin($userId);
    }

    public function canVerifyCompany(int $userId): bool
    {
        return $this->isGlobalAdmin($userId);
    }

    public function canInviteUser(int $userId, int $companyId): bool
    {
        return $this->isCompanyAdminOrOwner($userId, $companyId);
    }

    /**
     * @throws ValidationException
     */
    public function canRemoveMember(int $userId, int $targetUserId, int $companyId): bool
    {
        if ($this->isGlobalAdmin($userId)) {
            return true;
        }

        if (!$this->isCompanyAdminOrOwner($userId, $companyId)) {
            return false;
        }

        // Нельзя удалить владельца компании
        $company = $this->companyRepository->findById(new CompanyId($companyId));
        if ($company && $company->getOwnerId()->value() === $targetUserId) {
            return false;
        }

        // Проверка последнего администратора
        $targetMember = $this->companyMemberRepository->find(
            new CompanyId($companyId),
            new UserId($targetUserId),
        );

        if ($targetMember && $targetMember->isAtLeastAdmin()) {
            $adminsCount = $this->companyMemberRepository->countByRole(new CompanyId($companyId), 'admin')
                + $this->companyMemberRepository->countByRole(new CompanyId($companyId), 'owner');
            if ($adminsCount <= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function canChangeMemberRole(int $userId, int $targetUserId, int $companyId): bool
    {
        if (!$this->isCompanyAdminOrOwner($userId, $companyId)) {
            return false;
        }

        // Нельзя менять роль владельца
        $company = $this->companyRepository->findById(new CompanyId($companyId));
        if ($company && $company->getOwnerId()->value() === $targetUserId) {
            return false;
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    public function canTransferOwnership(int $userId, int $companyId): bool
    {
        $company = $this->companyRepository->findById(new CompanyId($companyId));
        return $company && $company->getOwnerId()->value() === $userId;
    }

    private function isGlobalAdmin(int $userId): bool
    {
        return $this->authorizationService->can($userId, self::PERMISSION_MANAGE_COMPANIES);
    }

    /**
     * @throws ValidationException
     */
    private function isCompanyMember(int $userId, int $companyId): bool
    {
        $member = $this->companyMemberRepository->find(
            new CompanyId($companyId),
            new UserId($userId),
        );
        return $member !== null;
    }

    /**
     * @throws ValidationException
     */
    private function isCompanyAdminOrOwner(int $userId, int $companyId): bool
    {
        $member = $this->companyMemberRepository->find(
            new CompanyId($companyId),
            new UserId($userId),
        );
        return $member !== null && $member->isAtLeastAdmin();
    }
}
