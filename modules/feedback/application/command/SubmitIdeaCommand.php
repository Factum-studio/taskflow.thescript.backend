<?php

declare(strict_types=1);

namespace modules\feedback\application\command;

class SubmitIdeaCommand
{
    public function __construct(
        public int $userId,
        public string $type,
        public string $comment,
    ) {
    }
}
