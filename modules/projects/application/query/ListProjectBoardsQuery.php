<?php

namespace modules\projects\application\query;

class ListProjectBoardsQuery
{
    public int $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }
}