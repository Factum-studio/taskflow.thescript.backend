<?php

declare(strict_types=1);

namespace core\application\port;

use core\application\dto\UserFiltersDto;
use core\domain\entity\User;
use core\domain\valueObject\UserId;
use core\domain\valueObject\Email;
use core\domain\exception\UserNotFoundException;

interface IUserRepository
{
    /**
     * @throws UserNotFoundException
     */
    public function findById(UserId $id): User;

    /**
     * @throws UserNotFoundException
     */
    public function findByEmail(Email $email): User;

    /**
     * Проверка уникальности
     */
    public function existsByEmail(Email $email, ?UserId $excludeUserId = null): bool;

    /**
     * @return User[]
     */
    public function findWithFilters(UserFiltersDto $filters): array;

    public function countWithFilters(UserFiltersDto $filters): int;

    public function save(User $user): void;

    public function delete(UserId $id): void;
}
