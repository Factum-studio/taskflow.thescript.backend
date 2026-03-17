<?php

namespace modules\tasks\application\command;

class MoveTaskToColumnCommand
{
    public int $id;
    public int $columnId;
    public int $movedBy;

    public function __construct(
        int $id,
        int $columnId,
        int $movedBy
    ) {
        $this->id       = $id;
        $this->columnId = $columnId;
        $this->movedBy  = $movedBy;
    }
}