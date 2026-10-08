<?php

declare(strict_types=1);

namespace modules\rbac\application\port;

interface IRoleAssignmentPolicy
{
    public function canAssign(int $actorUserId, int $targetUserId, int $targetRoleId): bool;

    public function canRemove(int $actorUserId, int $userRoleId): bool;
}
