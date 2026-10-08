<?php

declare(strict_types=1);

namespace modules\feedback\application\dto;

use JsonSerializable;
use modules\feedback\domain\entity\FeedbackRating;

class FeedbackRatingDto implements JsonSerializable
{
    public function __construct(
        public int $userId,
        public int $speed,
        public int $functionality,
        public int $design,
        public int $usability,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(FeedbackRating $rating): self
    {
        return new self(
            $rating->getUserId(),
            $rating->getSpeed()->value(),
            $rating->getFunctionality()->value(),
            $rating->getDesign()->value(),
            $rating->getUsability()->value(),
            $rating->getCreatedAt()->format('Y-m-d H:i:s'),
            $rating->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'userId'        => $this->userId,
            'speed'         => $this->speed,
            'functionality' => $this->functionality,
            'design'        => $this->design,
            'usability'     => $this->usability,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }
}
