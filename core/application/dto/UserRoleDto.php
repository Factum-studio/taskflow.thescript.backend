<?php

declare(strict_types=1);

namespace core\application\dto;

use core\domain\entity\UserRole;
use JsonSerializable;

class UserRoleDto implements JsonSerializable
{
    private int $id;
    private int $userId;
    private int $roleId;
    private string $assignedAt;
    private ?int $assignedBy;

    public function __construct(
        int $id,
        int $userId,
        int $roleId,
        string $assignedAt,
        ?int $assignedBy,
    ) {
        $this->id           = $id;
        $this->userId       = $userId;
        $this->roleId       = $roleId;
        $this->assignedAt   = $assignedAt;
        $this->assignedBy   = $assignedBy;
    }

    public static function fromEntity(UserRole $role): self
    {
        return new self(
            $role->getId()->value(),
            $role->getUserId()->value(),
            $role->getRoleId()->value(),
            $role->getAssignedAt()->format('Y-m-d H:i:s'),
            $role->getAssignedBy()?->value(),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->userId,
            'role_id'       => $this->roleId,
            'assigned_at'   => $this->assignedAt,
            'assigned_by'   => $this->assignedBy,
        ];
    }
}
