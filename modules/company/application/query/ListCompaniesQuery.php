<?php

declare(strict_types=1);

namespace modules\company\application\query;

class ListCompaniesQuery
{
    public int $userId;
    public ?string $search;
    public ?string $status;
    public int $page;
    public int $limit;

    public function __construct(
        int $userId,
        ?string $search = null,
        ?string $status = null,
        int $page = 1,
        int $limit = 20,
    ) {
        $this->userId = $userId;
        $this->search = $search;
        $this->status = $status;
        $this->page = $page;
        $this->limit = $limit;
    }
}
