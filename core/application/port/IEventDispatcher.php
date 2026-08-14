<?php

namespace core\application\port;

interface IEventDispatcher
{
    public function dispatch(object $event): void;
}