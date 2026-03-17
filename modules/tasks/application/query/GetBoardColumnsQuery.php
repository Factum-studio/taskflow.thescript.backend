<?php

namespace modules\tasks\application\query;

class GetBoardColumnsQuery
{
    public int $boardId;

    public function __construct(int $boardId)
    {
        $this->boardId = $boardId;
    }
}