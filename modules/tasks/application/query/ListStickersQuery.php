<?php

namespace modules\tasks\application\query;

class ListStickersQuery
{
    public ?string $type; // 'system', 'user', null = все
    public ?int $projectId; // может быть указан, если тип: user
    public ?int $createdBy;

    public function __construct(
        ?string $type = null,
        ?int $projectId = null,
        ?int $createdBy = null
    ) {
        $this->type         = $type;
        $this->projectId    = $projectId;
        $this->createdBy    = $createdBy;
    }
}