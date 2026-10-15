<?php

declare(strict_types=1);

namespace modules\company\application\query;

class GetCompanyQuery
{
    public int $id;
    public int $userId;

    public function __construct(int $id, int $userId)
    {
        $this->id = $id;
        $this->userId = $userId;
    }
}