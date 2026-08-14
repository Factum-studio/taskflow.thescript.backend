<?php

namespace modules\projects\domain\event;

interface IEventDispatcher
{
    public function dispatch(IProjectDomainEvent $event): void;
}