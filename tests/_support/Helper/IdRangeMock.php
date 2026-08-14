<?php

namespace tests\_support\Helper;

use core\domain\valueObject\IdRange;

class IdRangeMock extends IdRange
{
    public static function fromString(?string $input): IdRange
    {
        $mock = new self($input ?? '');

        return $mock;
    }
}