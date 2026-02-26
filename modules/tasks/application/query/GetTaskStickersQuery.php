<?php

namespace modules\tasks\application\query;

class GetTaskStickersQuery
{
    public int $taskId;

    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }
}