<?php

declare(strict_types=1);

namespace core\application\command;

class CreateRoleCommand
{
    public function __construct(public string $name, public ?string $description = null)
    {
    }
}
