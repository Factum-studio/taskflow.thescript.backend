<?php

declare(strict_types=1);

namespace core\application\query;

class GetUserQuery
{
    public function __construct(public int $userId)
    {
    }
}
