<?php

declare(strict_types=1);

namespace core\application\command;

class DeleteRoleCommand
{
    public function __construct(public int $roleId)
    {
    }
}
