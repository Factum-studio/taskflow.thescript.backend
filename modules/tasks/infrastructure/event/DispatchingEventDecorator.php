<?php

namespace modules\tasks\infrastructure\event;

use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\tasks\domain\event\IEventDispatcher as LocalEventDispatcher;
use modules\tasks\domain\event\ITaskDomainEvent;

class DispatchingEventDecorator implements LocalEventDispatcher
{
    private LocalEventDispatcher $localDispatcher;
    private GlobalEventDispatcher $globalDispatcher;
    private array $forwardEvents = [];

    public function __construct(
        LocalEventDispatcher $localDispatcher,
        GlobalEventDispatcher $globalDispatcher,
        array $forwardEvents = []
    ) {
        $this->localDispatcher  = $localDispatcher;
        $this->globalDispatcher = $globalDispatcher;
        $this->forwardEvents    = $forwardEvents;
    }

    public function dispatch(ITaskDomainEvent $event): void
    {
        $this->localDispatcher->dispatch($event);

        $eventClass = get_class($event);
        if (in_array($eventClass, $this->forwardEvents, true)) {
            $this->globalDispatcher->dispatch($event);
        }
    }
}