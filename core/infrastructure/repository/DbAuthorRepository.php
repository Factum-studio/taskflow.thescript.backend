<?php
namespace core\infrastructure\repository;

use core\application\port\IAuthorRepository;
use core\domain\entity\Author;
use core\domain\valueObject\AuthorId;
use core\domain\valueObject\AuthorPhrase;
use core\domain\valueObject\Badge;
use core\infrastructure\persistence\AuthorAR;
use RuntimeException;
use DateTimeImmutable;
use yii\db\Exception;

class DbAuthorRepository implements IAuthorRepository
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function save(Author $author): Author
    {
        $ar = $author->getId()->value() > 0
            ? AuthorAR::findOne($author->getId()->value())
            : new AuthorAR();

        $ar->user_id = $author->getUserId();
        $ar->phrase = $author->getPhrase()->value();
        $ar->badges = array_map(fn(Badge $b) => $b->value(), $author->getBadges());

        if (!$ar->save()) {
            throw new RuntimeException('Failed to save author: ' . implode(', ', $ar->getFirstErrors()));
        }

        if (!$author->getId()->value()) {
            $author = new Author(
                new AuthorId((int)$ar->id),
                $ar->user_id,
                new AuthorPhrase($ar->phrase),
                array_map(fn(string $label) => new Badge($label), $this->decodeBadges($ar->badges)),
                new DateTimeImmutable($ar->created_at),
                new DateTimeImmutable($ar->updated_at)
            );
        }
        return $author;
    }

    /**
     * @throws \Exception
     */
    public function findById(AuthorId $id): ?Author
    {
        $ar = AuthorAR::findOne($id->value());
        return $ar ? $this->mapToEntity($ar) : null;
    }

    public function findAll(): array
    {
        $ars = AuthorAR::find()->orderBy(['id' => SORT_ASC])->all();
        return array_map([$this, 'mapToEntity'], $ars);
    }

    /**
     * @throws \Exception
     */
    public function findByUserId(int $userId): ?Author
    {
        $ar = AuthorAR::find()->where(['user_id' => $userId])->one();
        return $ar ? $this->mapToEntity($ar) : null;
    }

    public function delete(AuthorId $id): void
    {
        AuthorAR::deleteAll(['id' => $id->value()]);
    }

    /**
     * @throws \Exception
     */
    private function mapToEntity(AuthorAR $ar): Author
    {
        $badges = $this->decodeBadges($ar->badges);
        $badgeObjects = array_map(fn(string $label) => new Badge($label), $badges);

        return new Author(
            new AuthorId((int)$ar->id),
            (int)$ar->user_id,
            new AuthorPhrase($ar->phrase),
            $badgeObjects,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }

    /**
     * Декодирует поле badges из базы.
     *
     * @param string|array|null $raw
     * @return array
     */
    private function decodeBadges($raw): array
    {
        if ($raw === null) {
            return [];
        }
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}