<?php

declare(strict_types=1);

namespace modules\company\application\command;

class UnarchiveCompanyCommand
{
    public int $id;
    public int $userId;

    public function __construct(int $id, int $userId)
    {
        $this->id = $id;
        $this->userId = $userId;
    }
}