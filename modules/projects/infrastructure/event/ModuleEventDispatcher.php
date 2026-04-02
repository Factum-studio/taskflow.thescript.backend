<?php

namespace modules\projects\infrastructure\event;

use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\IProjectDomainEvent;

class ModuleEventDispatcher implements IEventDispatcher
{
    private array $listeners = [];

    public function __construct(array $listeners = [])
    {
        $this->listeners = $listeners;
    }

    public function dispatch(IProjectDomainEvent $event): void
    {
        $eventClass = get_class($event);

        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                if (is_callable($listener)) {
                    $listener($event);
                } elseif (is_object($listener) && method_exists($listener, 'handle')) {
                    $listener->handle($event);
                }
            }
        }
    }
}