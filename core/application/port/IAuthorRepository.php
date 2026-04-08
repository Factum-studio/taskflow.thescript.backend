<?php
namespace core\application\port;

use core\domain\entity\Author;
use core\domain\valueObject\AuthorId;

interface IAuthorRepository
{
    public function save(Author $author): Author;
    public function findById(AuthorId $id): ?Author;
    /** @return Author[] */
    public function findAll(): array;
    public function findByUserId(int $userId): ?Author;
    public function delete(AuthorId $id): void;
}