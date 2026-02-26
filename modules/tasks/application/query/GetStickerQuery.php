<?php

namespace modules\tasks\application\query;

class GetStickerQuery
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}