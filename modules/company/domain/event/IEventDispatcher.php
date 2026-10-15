<?php

declare(strict_types=1);

namespace modules\company\domain\event;

interface IEventDispatcher
{
    public function dispatch(ICompanyDomainEvent $event): void;
}