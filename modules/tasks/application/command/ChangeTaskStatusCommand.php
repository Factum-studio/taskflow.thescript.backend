<?php

namespace modules\tasks\application\command;

class ChangeTaskStatusCommand
{
    public int $id;
    public int $statusId;
    public int $changedBy;

    public function __construct(
        int $id,
        int $statusId,
        int $changedBy
    ) {
        $this->id           = $id;
        $this->statusId     = $statusId;
        $this->changedBy    = $changedBy;
    }
}