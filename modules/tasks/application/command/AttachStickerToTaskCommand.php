<?php

namespace modules\tasks\application\command;

class AttachStickerToTaskCommand
{
    public int $taskId;
    public int $stickerId;
    public int $attachedBy;

    public function __construct(int $taskId, int $stickerId, int $attachedBy)
    {
        $this->taskId       = $taskId;
        $this->stickerId    = $stickerId;
        $this->attachedBy   = $attachedBy;
    }
}