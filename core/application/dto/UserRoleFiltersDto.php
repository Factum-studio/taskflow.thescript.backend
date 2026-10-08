<?php

declare(strict_types=1);

namespace core\application\dto;

class UserRoleFiltersDto
{
    public function __construct(
        public ?string $ids         = null,
        public ?int $limit          = null,
        public ?int $offset         = null,
        public ?string $orderBy     = null,
        public ?int $userId         = null,
        public ?int $roleId         = null,
        public ?string $assignedFrom = null,
        public ?string $assignedTo  = null,
        public ?int $assignedBy     = null,
    ) {
    }
}
