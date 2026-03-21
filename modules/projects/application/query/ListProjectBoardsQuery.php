<?php

namespace modules\projects\application\query;

class ListProjectBoardsQuery
{
    public int $projectId;
    public int $userId;

    public function __construct(
        int $projectId,
        int $userId
    ) {
        $this->projectId    = $projectId;
        $this->userId       = $userId;
    }
}