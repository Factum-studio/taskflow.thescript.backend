<?php

namespace modules\tasks\application\query;

class GetCommentQuery
{
    public int $commentId;
    public int $userId;

    public function __construct(
        int $commentId,
        int $userId
    ) {
        $this->commentId    = $commentId;
        $this->userId       = $userId;
    }
}