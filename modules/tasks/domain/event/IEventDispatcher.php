<?php

declare(strict_types=1);

namespace modules\tasks\domain\event;

interface IEventDispatcher
{
    public function dispatch(ITaskDomainEvent $event): void;
}
