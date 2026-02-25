<?php

namespace modules\tasks\application\command;

class RestoreTaskCommand
{
    public int $id;
    public int $restoredBy;

    public function __construct(int $id, int $restoredBy)
    {
        $this->id           = $id;
        $this->restoredBy   = $restoredBy;
    }
}