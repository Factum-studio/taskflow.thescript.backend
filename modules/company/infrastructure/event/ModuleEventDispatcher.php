<?php

declare(strict_types=1);

namespace modules\company\infrastructure\event;

use modules\company\domain\event\ICompanyDomainEvent;
use modules\company\domain\event\IEventDispatcher;
use SplObjectStorage;
use SplStack;

class ModuleEventDispatcher implements IEventDispatcher
{
    /** @var array<string, callable[]> */
    private array $listeners = [];
    private SplStack $events;
    private SplObjectStorage $processed;

    public function __construct(array $listeners = [])
    {
        $this->events = new SplStack();
        $this->processed = new SplObjectStorage();

        foreach ($listeners as $eventClass => $handlers) {
            foreach ($handlers as $handler) {
                $this->addListener($eventClass, $handler);
            }
        }
    }

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(ICompanyDomainEvent $event): void
    {
        if ($this->processed->contains($event)) {
            return;
        }

        $this->events->push($event);
        $this->processed->attach($event);

        $eventClass = get_class($event);
        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                $listener($event);
            }
        }

        $this->events->pop();
    }
}
