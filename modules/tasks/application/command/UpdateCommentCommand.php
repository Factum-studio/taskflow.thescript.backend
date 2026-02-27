<?php

namespace modules\tasks\application\command;

class UpdateCommentCommand
{
    public int $commentId;
    public int $userId;
    public string $content;

    public function __construct(int $commentId, int $userId, string $content)
    {
        $this->commentId    = $commentId;
        $this->userId       = $userId;
        $this->content      = $content;
    }
}