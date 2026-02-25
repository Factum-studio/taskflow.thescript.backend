<?php

namespace modules\tasks\application\query;

class GetTaskQuery
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}