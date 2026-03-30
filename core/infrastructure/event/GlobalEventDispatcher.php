<?php

namespace core\infrastructure\event;

use core\application\port\IEventDispatcher;
use yii\di\Container;

class GlobalEventDispatcher implements IEventDispatcher
{
    private array $listeners = [];
    private Container $container;

    public function __construct(Container $container, array $listeners = [])
    {
        $this->container = $container;
        foreach ($listeners as $eventClass => $handlers) {
            foreach ($handlers as $handler) {
                $this->addListener($eventClass, $handler);
            }
        }
    }

    public function addListener(string $eventClass, callable|string $listener): void
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $eventClass = get_class($event);
        if (!isset($this->listeners[$eventClass])) {
            return;
        }

        foreach ($this->listeners[$eventClass] as $listener) {
            if (is_string($listener)) {
                $listenerInstance = $this->container->get($listener);
                if (method_exists($listenerInstance, 'handle')) {
                    $listenerInstance->handle($event);
                }
            } elseif (is_callable($listener)) {
                $listener($event);
            }
        }
    }
}