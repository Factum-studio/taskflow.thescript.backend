<?php

declare(strict_types=1);

namespace core\infrastructure\repository;

use core\application\dto\UserFiltersDto;
use core\application\port\IUserRepository;
use core\domain\entity\User;
use core\domain\entity\UserRole;
use core\domain\exception\RuntimeException;
use core\domain\exception\UserNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\Email;
use core\domain\valueObject\IdRange;
use core\domain\valueObject\RoleId;
use core\domain\valueObject\UserId;
use core\domain\valueObject\UserRoleId;
use core\infrastructure\persistence\UserAR;
use core\infrastructure\persistence\UserRoleAR;
use DateTimeImmutable;
use yii\db\Exception;
use yii\db\Query;

class DbUserRepository implements IUserRepository
{
    /**
     * @throws UserNotFoundException
     * @throws ValidationException
     */
    public function findById(UserId $id): User
    {
        $row = (new Query())
            ->from(UserAR::tableName())
            ->where(['id' => $id->value()])
            ->one();

        if (!$row) {
            throw new UserNotFoundException('User not found.');
        }

        return $this->hydrate($row, true);
    }

    /**
     * @throws UserNotFoundException
     * @throws ValidationException
     */
    public function findByEmail(Email $email): User
    {
        $row = (new Query())
            ->from(UserAR::tableName())
            ->where(['email' => $email->value()])
            ->one();

        if (!$row) {
            throw new UserNotFoundException('User not found.');
        }

        return $this->hydrate($row);
    }

    public function existsByEmail(Email $email, ?UserId $excludeUserId = null): bool
    {
        $query = (new Query())
            ->from(UserAR::tableName())
            ->where(['email' => $email->value()]);

        if ($excludeUserId !== null) {
            $query->andWhere(['!=', 'id', $excludeUserId->value()]);
        }

        return $query->exists();
    }

    public function findWithFilters(UserFiltersDto $filters): array
    {
        $query = $this->buildFilterQuery($filters);
        $query->orderBy($filters->orderBy ?? ['id' => SORT_ASC]);
        $query->limit($filters->limit)->offset($filters->offset);
        return array_map([$this, 'hydrate'], $query->all());
    }

    public function countWithFilters(UserFiltersDto $filters): int
    {
        $query = $this->buildFilterQuery($filters);
        return (int) $query->count();
    }

    /**
     * @throws Exception
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function save(User $user): void
    {
        $ar = $this->findOrCreateAR($user->getPassportId());

        $ar->passport_id    = $user->getPassportId();
        $ar->email          = $user->getEmail()->value();
        $ar->surname        = $user->getSurname();
        $ar->name           = $user->getName();
        $ar->patronymic     = $user->getPatronymic();
        $ar->post           = $user->getPost();
        $ar->is_owner       = $user->isOwner();
        $ar->synced_at      = $user->getSyncAt()?->format('Y-m-d H:i:s');

        if (!$ar->save()) {
            throw new RuntimeException(
                'Failed to save user: ' . json_encode($ar->getErrors(), JSON_UNESCAPED_UNICODE),
            );
        }

        if ($user->getId()->isNew()) {
            $user->setId(new UserId((int)$ar->id));
        }
    }

    public function delete(UserId $id): void
    {
        UserAR::deleteAll(['id' => $id->value()]);
    }

    private function buildFilterQuery(UserFiltersDto $filters): Query
    {
        $query = (new Query())->from(UserAR::tableName());
        if ($filters->ids !== null) {
            $ids = (new IdRange($filters->ids))->toArray();
            if (!empty($ids)) {
                $query->andWhere(['id' => $ids]);
            }
        }
        if ($filters->email !== null) {
            $query->andWhere(['email' => $filters->email]);
        }
        if ($filters->isOwner !== null) {
            $query->andWhere(['is_owner' => $filters->isOwner]);
        }
        if ($filters->passportId !== null) {
            $query->andWhere(['passport_id' => $filters->passportId]);
        }

        // LIKE searches
        if ($filters->surname !== null) {
            $query->andWhere(['like', 'surname', $filters->surname]);
        }
        if ($filters->name !== null) {
            $query->andWhere(['like', 'name', $filters->name]);
        }
        if ($filters->patronymic !== null) {
            $query->andWhere(['like', 'patronymic', $filters->patronymic]);
        }
        if ($filters->post !== null) {
            $query->andWhere(['like', 'post', $filters->post]);
        }

        // Date ranges
        if ($filters->syncFrom !== null) {
            $query->andWhere(['>=', 'synced_at', $filters->syncFrom]);
        }
        if ($filters->syncTo !== null) {
            $query->andWhere(['<=', 'synced_at', $filters->syncTo]);
        }
        if ($filters->createdFrom !== null) {
            $query->andWhere(['>=', 'created_at', $filters->createdFrom]);
        }
        if ($filters->createdTo !== null) {
            $query->andWhere(['<=', 'created_at', $filters->createdTo]);
        }
        if ($filters->updatedFrom !== null) {
            $query->andWhere(['>=', 'updated_at', $filters->updatedFrom]);
        }
        if ($filters->updatedTo !== null) {
            $query->andWhere(['<=', 'updated_at', $filters->updatedTo]);
        }

        return $query;
    }

    private function findOrCreateAR(int $id): UserAR
    {
        $ar = UserAR::findOne(['passport_id' => $id]);
        return $ar ?? new UserAR();
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    private function hydrate(array $row, bool $withRelations = false): User
    {
        $userId = new UserId((int)$row['id']);

        $roles = [];

        if ($withRelations) {

            $roles = array_map(
                static fn (array $role): UserRole => new UserRole(
                    new UserRoleId((int)$role['id']),
                    $userId,
                    new RoleId((int)$role['role_id']),
                    new DateTimeImmutable((string)$role['assigned_at']),
                    $role['assigned_by'] !== null ? new UserId((int)$role['assigned_by']) : null,
                ),
                (new Query())
                    ->from(UserRoleAR::tableName())
                    ->where(['user_id' => $userId->value()])
                    ->orderBy(['id' => SORT_ASC])
                    ->all(),
            );
        }

        return new User(
            $userId,
            (int)$row['passport_id'],
            new Email($row['email']),
            $row['surname'],
            $row['name'],
            $row['patronymic'],
            $row['post'],
            (bool)$row['is_owner'],
            $row['synced_at'] !== null ? new DateTimeImmutable($row['synced_at']) : null,
            $row['created_at'] !== null ? new DateTimeImmutable($row['created_at']) : null,
            $row['updated_at'] !== null ? new DateTimeImmutable($row['updated_at']) : null,
            $roles,
        );
    }
}
