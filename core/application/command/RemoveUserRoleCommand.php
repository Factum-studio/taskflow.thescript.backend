<?php

declare(strict_types=1);

namespace core\application\command;

class RemoveUserRoleCommand
{
    public function __construct(public int $userRoleId)
    {
    }
}
