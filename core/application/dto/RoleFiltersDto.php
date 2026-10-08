<?php

declare(strict_types=1);

namespace core\application\dto;

class RoleFiltersDto
{
    public function __construct(
        public ?string $ids         = null,
        public ?int $limit          = null,
        public ?int $offset         = null,
        public ?string $orderBy     = null,
        public ?string $name        = null,
        public ?string $description = null,
        public ?string $createdFrom = null,
        public ?string $createdTo   = null,
    ) {
    }
}
