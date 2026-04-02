<?php

namespace modules\tasks\application\query;

class GetBoardColumnsQuery
{
    public int $boardId;
    public int $userId;

    public function __construct(
        int $boardId,
        int $userId
    ) {
        $this->boardId  = $boardId;
        $this->userId   = $userId;
    }
}