<?php

declare(strict_types=1);

namespace modules\projects\application\command;

class DeleteBoardCommand
{
    public int $id;
    public int $deletedBy;

    public function __construct(int $id, int $deletedBy)
    {
        $this->id           = $id;
        $this->deletedBy    = $deletedBy;
    }
}
