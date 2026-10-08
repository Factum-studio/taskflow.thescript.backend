<?php

declare(strict_types=1);

namespace modules\passport\auth\application\dto;

final class PassportUserDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $surname,
        public readonly string $name,
        public readonly ?string $patronymic,
        public readonly string $email,
        public readonly ?string $post,
        public readonly bool $isOwner,
    ) {
    }
}
