<?php

namespace modules\tasks\application\command;

class AssignTaskCommand
{
    public int $id;
    public int $assignedTo;
    public int $assignedBy;

    public function __construct(
        int $id,
        int $assignedTo,
        int $assignedBy,
    ) {
        $this->id           = $id;
        $this->assignedTo   = $assignedTo;
        $this->assignedBy   = $assignedBy;
    }
}