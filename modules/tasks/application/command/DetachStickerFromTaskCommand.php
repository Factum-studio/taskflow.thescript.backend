<?php

namespace modules\tasks\application\command;

class DetachStickerFromTaskCommand
{
    public int $taskId;
    public int $stickerId;
    public int $detachedBy;

    public function __construct(int $taskId, int $stickerId, int $detachedBy)
    {
        $this->taskId       = $taskId;
        $this->stickerId    = $stickerId;
        $this->detachedBy   = $detachedBy;
    }
}