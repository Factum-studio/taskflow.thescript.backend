<?php

namespace modules\projects\application\command;

class DeleteProjectCommand
{
    public int $id;
    public int $deletedBy;

    public function __construct(int $id, int $deletedBy)
    {
        $this->id           = $id;
        $this->deletedBy    = $deletedBy;
    }
}