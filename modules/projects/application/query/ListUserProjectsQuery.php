<?php

namespace modules\projects\application\query;

class ListUserProjectsQuery
{
    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }
}