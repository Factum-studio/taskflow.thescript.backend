<?php

declare(strict_types=1);

namespace core\application\command;

class SyncUserCommand
{
    public function __construct(
        public int $passportId,
        public string $surname,
        public string $name,
        public string $email,
        public bool $isOwner = false,
        public ?string $patronymic = null,
        public ?string $post = null,
    ) {
    }
}
