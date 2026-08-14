<?php

namespace modules\tasks\application\query;

class GetTaskStickersQuery
{
    public int $taskId;
    public int $userId;

    public function __construct(
        int $taskId,
        int $userId
    ) {
        $this->taskId = $taskId;
        $this->userId = $userId;
    }
}