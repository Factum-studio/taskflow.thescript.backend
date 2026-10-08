<?php

declare(strict_types=1);

namespace core\application\query;

class GetRoleQuery
{
    public function __construct(public int $roleId)
    {
    }
}
