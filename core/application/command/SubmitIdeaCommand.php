<?php
namespace core\application\command;

class SubmitIdeaCommand
{
    public function __construct(
        public int $userId,
        public string $type,
        public string $comment
    ) {}
}