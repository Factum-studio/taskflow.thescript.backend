<?php

declare(strict_types=1);

namespace modules\passport\auth\application\command;

final class HandleSsoCallbackCommand
{
    public function __construct(
        public readonly string $code,
        public readonly string $state,
        public readonly string $expectedState,
    ) {
    }
}
