<?php

namespace modules\projects\domain\entity;

use DateTimeImmutable;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use InvalidArgumentException;

class ProjectUser
{
    private ProjectId $projectId;
    private UserId $userId;
    private UserRole $role;
    private ?UserId $invitedBy;
    private ?DateTimeImmutable $invitedAt;
    private ?DateTimeImmutable $acceptedAt;
    private DateTimeImmutable $joinedAt;

    public function __construct(
        ProjectId $projectId,
        UserId $userId,
        UserRole $role,
        ?UserId $invitedBy = null,
        ?DateTimeImmutable $invitedAt = null,
        ?DateTimeImmutable $acceptedAt = null,
        ?DateTimeImmutable $joinedAt = null
    ) {
        $this->projectId    = $projectId;
        $this->userId       = $userId;
        $this->role         = $role;
        $this->invitedBy    = $invitedBy;
        $this->invitedAt    = $invitedAt;
        $this->acceptedAt   = $acceptedAt;
        $this->joinedAt     = $joinedAt ?? new DateTimeImmutable();
    }

    public function getProjectId(): ProjectId
    {
        return $this->projectId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getInvitedBy(): ?UserId
    {
        return $this->invitedBy;
    }

    public function getInvitedAt(): ?DateTimeImmutable
    {
        return $this->invitedAt;
    }

    public function getAcceptedAt(): ?DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getJoinedAt(): DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function changeRole(UserRole $newRole): void
    {
        $this->role = $newRole;
    }

    public function accept(): void
    {
        if ($this->acceptedAt !== null) {
            throw new InvalidArgumentException('User already accepted');
        }
        $this->acceptedAt = new DateTimeImmutable();
    }
}