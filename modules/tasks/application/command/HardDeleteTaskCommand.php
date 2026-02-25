<?php

namespace modules\tasks\application\command;

class HardDeleteTaskCommand
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}