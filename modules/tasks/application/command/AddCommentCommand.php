<?php

namespace modules\tasks\application\command;

class AddCommentCommand
{
    public int $taskId;
    public int $userId;
    public string $content;

    public function __construct(int $taskId, int $userId, string $content)
    {
        $this->taskId   = $taskId;
        $this->userId   = $userId;
        $this->content  = $content;
    }
}