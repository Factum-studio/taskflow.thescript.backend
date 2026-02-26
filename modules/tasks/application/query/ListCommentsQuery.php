<?php

namespace modules\tasks\application\query;

class ListCommentsQuery
{
    public int $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }
}