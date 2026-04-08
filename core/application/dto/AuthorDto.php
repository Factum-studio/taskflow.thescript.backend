<?php
namespace core\application\dto;

use core\domain\entity\Author;
use core\domain\valueObject\Badge;

class AuthorDto implements \JsonSerializable
{
    private function __construct(
        private readonly array $data
    ) {}

    public static function fromEntity(Author $author, string $avatar, string $uiName): self
    {
        $badges = array_map(fn(Badge $b) => $b->value(), $author->getBadges());

        return new self([
            'id'        => $author->getId()->value(),
            'userId'    => $author->getUserId(),
            'uiName'    => $uiName,
            'avatar'    => $avatar,
            'phrase'    => $author->getPhrase()->value(),
            'badges'    => $badges,
            'createdAt' => $author->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $author->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}