<?php

namespace modules\tasks\application\query;

class GetCommentQuery
{
    public int $commentId;

    public function __construct(int $commentId)
    {
        $this->commentId = $commentId;
    }
}