<?php

declare(strict_types=1);

namespace core\domain\entity;

use core\domain\valueObject\UserId;
use core\domain\valueObject\UserRoleId;
use core\domain\valueObject\RoleId;
use DateTimeImmutable;

class UserRole
{
    private UserRoleId $id;
    private UserId $userId;
    private RoleId $roleId;
    private DateTimeImmutable $assignedAt;
    private ?UserId $assignedBy;

    public function __construct(
        UserRoleId $id,
        UserId $userId,
        RoleId $roleId,
        DateTimeImmutable $assignedAt,
        ?UserId $assignedBy = null,
    ) {
        $this->id          = $id;
        $this->userId      = $userId;
        $this->roleId      = $roleId;
        $this->assignedAt  = $assignedAt;
        $this->assignedBy  = $assignedBy;
    }

    public function getId(): UserRoleId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getRoleId(): RoleId
    {
        return $this->roleId;
    }

    public function getAssignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function getAssignedBy(): ?UserId
    {
        return $this->assignedBy;
    }

    /**
     * @internal Используется только репозиторием для установки ID после сохранения
     */
    public function setId(UserRoleId $id): void
    {
        $this->id = $id;
    }
}
