<?php

declare(strict_types=1);

namespace core\application\port;

interface IEventDispatcher
{
    public function dispatch(object $event): void;
}
