<?php

namespace modules\tasks\application\query;

class GetTaskQuery
{
    public int $id;
    public int $userId;

    public function __construct(
        int $id,
        int $userId
    ) {
        $this->id       = $id;
        $this->userId   = $userId;
    }
}