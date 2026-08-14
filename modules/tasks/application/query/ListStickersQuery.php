<?php

namespace modules\tasks\application\query;

class ListStickersQuery
{
    public ?string $type; // 'system', 'user', null = все
    public ?int $projectId; // может быть указан, если тип: user
    public ?int $createdBy;
    public int $userId;

    public function __construct(
        int $userId,
        ?string $type = null,
        ?int $projectId = null,
        ?int $createdBy = null
    ) {
        $this->userId       = $userId;
        $this->type         = $type;
        $this->projectId    = $projectId;
        $this->createdBy    = $createdBy;
    }
}