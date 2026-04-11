<?php
namespace core\application\dto;

use core\domain\entity\FeedbackIdea;

class FeedbackIdeaDto implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $type,
        public string $comment,
        public bool $isImplemented,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(FeedbackIdea $idea): self
    {
        return new self(
            $idea->getId(),
            $idea->getUserId(),
            $idea->getType()->value(),
            $idea->getComment(),
            $idea->isImplemented(),
            $idea->getCreatedAt()->format('Y-m-d H:i:s'),
            $idea->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'userId'        => $this->userId,
            'type'          => $this->type,
            'comment'       => $this->comment,
            'isImplemented' => $this->isImplemented,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }
}