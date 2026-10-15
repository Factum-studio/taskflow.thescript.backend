<?php

declare(strict_types=1);

namespace modules\company\domain\entity;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyMemberRole;
use InvalidArgumentException;

class CompanyMember
{
    private CompanyId $companyId;
    private UserId $userId;
    private CompanyMemberRole $role;
    private ?UserId $invitedBy;
    private ?DateTimeImmutable $invitedAt;
    private ?DateTimeImmutable $acceptedAt;
    private DateTimeImmutable $joinedAt;

    public function __construct(
        CompanyId $companyId,
        UserId $userId,
        CompanyMemberRole $role,
        ?UserId $invitedBy = null,
        ?DateTimeImmutable $invitedAt = null,
        ?DateTimeImmutable $acceptedAt = null,
        ?DateTimeImmutable $joinedAt = null,
    ) {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->role = $role;
        $this->invitedBy = $invitedBy;
        $this->invitedAt = $invitedAt;
        $this->acceptedAt = $acceptedAt;
        $this->joinedAt = $joinedAt ?? new DateTimeImmutable();
    }

    // -------- Getters --------

    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getUserId(): UserId { return $this->userId; }
    public function getRole(): CompanyMemberRole { return $this->role; }
    public function getInvitedBy(): ?UserId { return $this->invitedBy; }
    public function getInvitedAt(): ?DateTimeImmutable { return $this->invitedAt; }
    public function getAcceptedAt(): ?DateTimeImmutable { return $this->acceptedAt; }
    public function getJoinedAt(): DateTimeImmutable { return $this->joinedAt; }

    // -------- Mutators --------

    public function changeRole(CompanyMemberRole $newRole): void
    {
        $this->role = $newRole;
    }

    public function accept(): void
    {
        if ($this->acceptedAt !== null) {
            throw new InvalidArgumentException('User already accepted invitation');
        }
        $this->acceptedAt = new DateTimeImmutable();
    }

    public function isOwner(): bool
    {
        return $this->role->isOwner();
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isMember(): bool
    {
        return $this->role->isMember();
    }

    public function isAtLeastAdmin(): bool
    {
        return $this->role->isAtLeastAdmin();
    }
}