<?php

declare(strict_types=1);

namespace core\application\dto;

class UserFiltersDto
{
    public function __construct(
        public ?string $ids             = null,
        public ?int $limit              = null,
        public ?int $offset             = null,
        public ?string $orderBy         = null,
        public ?string $email           = null,
        public ?string $surname         = null,
        public ?string $name            = null,
        public ?string $patronymic      = null,
        public ?bool $isOwner           = null,
        public ?int $passportId         = null,
        public ?string $post            = null,
        public ?string $syncFrom        = null,
        public ?string $syncTo          = null,
        public ?string $createdFrom     = null,
        public ?string $createdTo       = null,
        public ?string $updatedFrom     = null,
        public ?string $updatedTo       = null,
    ) {
    }
}
