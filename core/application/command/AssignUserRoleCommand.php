<?php

declare(strict_types=1);

namespace core\application\command;

class AssignUserRoleCommand
{
    public function __construct(
        public int $userId,
        public int $roleId,
        public ?int $assignedBy = null,
    ) {
    }
}
