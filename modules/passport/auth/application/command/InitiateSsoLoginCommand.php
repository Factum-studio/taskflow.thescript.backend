<?php

declare(strict_types=1);

namespace modules\passport\auth\application\command;

final class InitiateSsoLoginCommand
{
    public function __construct(public readonly string $state)
    {
    }
}
