<?php

declare(strict_types=1);

namespace modules\tasks\application\query;

class ListCommentsQuery
{
    public int $taskId;
    public int $userId;

    public function __construct(
        int $taskId,
        int $userId,
    ) {
        $this->taskId = $taskId;
        $this->userId = $userId;
    }
}
