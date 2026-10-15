<?php

declare(strict_types=1);

namespace modules\company\infrastructure\repository;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\entity\CompanyMember;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyMemberRole;
use modules\company\infrastructure\persistence\CompanyMemberAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use Exception;
use yii\db\StaleObjectException;

class DbCompanyMemberRepository implements ICompanyMemberRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(CompanyMember $member): CompanyMember
    {
        $ar = $this->findAR($member->getCompanyId(), $member->getUserId()) ?? new CompanyMemberAR();
        $this->mapEntityToAR($member, $ar);

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save company member: ' . implode(', ', $ar->getFirstErrors()));
        }

        return $member;
    }

    /**
     * @throws Exception
     */
    public function find(CompanyId $companyId, UserId $userId): ?CompanyMember
    {
        $ar = $this->findAR($companyId, $userId);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws Exception
     */
    public function findByCompany(CompanyId $companyId): array
    {
        $ars = CompanyMemberAR::find()
            ->where(['company_id' => $companyId->value()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Exception
     */
    public function findByUser(UserId $userId): array
    {
        $ars = CompanyMemberAR::find()
            ->where(['user_id' => $userId->value()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function countByCompany(CompanyId $companyId): int
    {
        return (int)CompanyMemberAR::find()
            ->where(['company_id' => $companyId->value()])
            ->count();
    }

    public function countByRole(CompanyId $companyId, string $role): int
    {
        return (int)CompanyMemberAR::find()
            ->where(['company_id' => $companyId->value(), 'role' => $role])
            ->count();
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(CompanyMember $member): void
    {
        $ar = $this->findAR($member->getCompanyId(), $member->getUserId());
        $ar?->delete();
    }

    private function findAR(CompanyId $companyId, UserId $userId): ?CompanyMemberAR
    {
        return CompanyMemberAR::find()
            ->where(['company_id' => $companyId->value(), 'user_id' => $userId->value()])
            ->one();
    }

    private function mapEntityToAR(CompanyMember $member, CompanyMemberAR $ar): void
    {
        $ar->company_id = $member->getCompanyId()->value();
        $ar->user_id = $member->getUserId()->value();
        $ar->role = $member->getRole()->value();
        $ar->invited_by = $member->getInvitedBy()?->value();
        $ar->invited_at = $member->getInvitedAt()?->format('Y-m-d H:i:s');
        $ar->accepted_at = $member->getAcceptedAt()?->format('Y-m-d H:i:s');
        $ar->joined_at = $member->getJoinedAt()->format('Y-m-d H:i:s');
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(CompanyMemberAR $ar): CompanyMember
    {
        return new CompanyMember(
            new CompanyId((int)$ar->company_id),
            new UserId((int)$ar->user_id),
            new CompanyMemberRole($ar->role),
            $ar->invited_by !== null ? new UserId((int)$ar->invited_by) : null,
            $ar->invited_at !== null ? new DateTimeImmutable($ar->invited_at) : null,
            $ar->accepted_at !== null ? new DateTimeImmutable($ar->accepted_at) : null,
            new DateTimeImmutable($ar->joined_at),
        );
    }
}
