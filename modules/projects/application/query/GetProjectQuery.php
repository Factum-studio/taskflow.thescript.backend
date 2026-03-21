<?php

namespace modules\projects\application\query;

class GetProjectQuery
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