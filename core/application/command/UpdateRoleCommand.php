<?php

declare(strict_types=1);

namespace core\application\command;

class UpdateRoleCommand
{
    public function __construct(public int $roleId, public ?string $name = null, public ?string $description = null)
    {
    }
}
