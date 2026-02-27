<?php

namespace modules\tasks\application\command;

class DeleteStickerCommand
{
    public int $id;
    public int $deletedBy;

    public function __construct(int $id, int $deletedBy)
    {
        $this->id           = $id;
        $this->deletedBy    = $deletedBy;
    }
}