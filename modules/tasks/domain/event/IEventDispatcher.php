<?php

namespace modules\tasks\domain\event;

interface IEventDispatcher
{
    public function dispatch(ITaskDomainEvent $event): void;
}