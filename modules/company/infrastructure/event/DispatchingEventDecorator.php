<?php

declare(strict_types=1);

namespace modules\company\infrastructure\event;

use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\company\domain\event\ICompanyDomainEvent;
use modules\company\domain\event\IEventDispatcher as CompanyEventDispatcher;

class DispatchingEventDecorator implements CompanyEventDispatcher
{
    private CompanyEventDispatcher $innerDispatcher;
    private GlobalEventDispatcher $globalDispatcher;
    /** @var string[] */
    private array $forwardedEvents;

    /**
     * @param string[] $forwardedEvents
     */
    public function __construct(
        CompanyEventDispatcher $innerDispatcher,
        GlobalEventDispatcher $globalDispatcher,
        array $forwardedEvents = [],
    ) {
        $this->innerDispatcher = $innerDispatcher;
        $this->globalDispatcher = $globalDispatcher;
        $this->forwardedEvents = $forwardedEvents;
    }

    public function dispatch(ICompanyDomainEvent $event): void
    {
        // Сначала локальные обработчики
        $this->innerDispatcher->dispatch($event);

        // Затем глобальные (если событие в списке форварда)
        if (in_array(get_class($event), $this->forwardedEvents, true)) {
            $this->globalDispatcher->dispatch($event);
        }
    }
}