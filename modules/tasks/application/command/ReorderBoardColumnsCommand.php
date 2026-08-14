<?php

namespace modules\tasks\application\command;

class ReorderBoardColumnsCommand
{
    public int $boardId;
    /** @var int[] */
    public array $orderedIds;
    public int $updatedBy;

    public function __construct(int $boardId, array $orderedIds, int $updatedBy)
    {
        $this->boardId      = $boardId;
        $this->orderedIds   = $orderedIds;
        $this->updatedBy    = $updatedBy;
    }
}