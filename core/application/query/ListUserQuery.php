<?php

declare(strict_types=1);

namespace core\application\query;

use core\application\dto\UserFiltersDto;

class ListUserQuery
{
    public function __construct(public UserFiltersDto $filters)
    {
    }
}
