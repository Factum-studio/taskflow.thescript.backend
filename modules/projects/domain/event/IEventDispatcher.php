<?php

declare(strict_types=1);

namespace modules\projects\domain\event;

interface IEventDispatcher
{
    public function dispatch(IProjectDomainEvent $event): void;
}
