<?php

namespace modules\projects\application\query;

class ListProjectMembersQuery
{
    public int $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }
}