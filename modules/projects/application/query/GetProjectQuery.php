<?php

namespace modules\projects\application\query;

class GetProjectQuery
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}