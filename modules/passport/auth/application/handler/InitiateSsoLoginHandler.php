<?php

declare(strict_types=1);

namespace modules\passport\auth\application\handler;

use modules\passport\auth\application\command\InitiateSsoLoginCommand;
use modules\passport\auth\application\port\PassportAuthPort;

final class InitiateSsoLoginHandler
{
    public function __construct(private readonly PassportAuthPort $passport)
    {
    }

    public function handle(InitiateSsoLoginCommand $command): string
    {
        return $this->passport->buildAuthorizationUrl($command->state);
    }
}
