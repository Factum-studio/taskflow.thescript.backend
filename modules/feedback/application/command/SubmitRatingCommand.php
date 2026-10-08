<?php

declare(strict_types=1);

namespace modules\feedback\application\command;

class SubmitRatingCommand
{
    public function __construct(
        public int $userId,
        public int $speed,
        public int $functionality,
        public int $design,
        public int $usability,
    ) {
    }
}
