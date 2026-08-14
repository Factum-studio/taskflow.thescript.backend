<?php

namespace modules\tasks\application\command;

class DeleteCommentCommand
{
    public int $commentId;
    public int $userId;

    public function __construct(int $commentId, int $userId)
    {
        $this->commentId    = $commentId;
        $this->userId       = $userId;
    }
}