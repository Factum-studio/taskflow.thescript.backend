<?php

declare(strict_types=1);

namespace core\domain\entity;

use core\domain\exception\EntityNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\Email;
use core\domain\valueObject\UserId;
use core\domain\valueObject\UserRoleId;
use DateTimeImmutable;

class User
{
    private UserId $id;
    private int $passportId;
    private Email $email;
    private ?string $surname;
    private ?string $name;
    private ?string $patronymic;
    private ?string $post;
    private ?bool $isOwner;
    private ?DateTimeImmutable $syncedAt;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /** @var UserRole[] */
    private array $roles = [];

    /**
     * @throws ValidationException
     */
    public function __construct(
        UserId $id,
        int $passportId,
        Email $email,
        ?string $surname,
        ?string $name,
        ?string $patronymic,
        ?string $post,
        bool $isOwner,
        ?DateTimeImmutable $syncedAt = null,
        DateTimeImmutable $createdAt = null,
        DateTimeImmutable $updatedAt = null,
        array $roles = [],
    ) {
        $this->id               = $id;
        $this->passportId       = $passportId;
        $this->email            = $email;
        $this->surname          = $surname;
        $this->name             = $name;
        $this->patronymic       = $patronymic;
        $this->post             = $post;
        $this->isOwner          = $isOwner;
        $this->syncedAt         = $syncedAt ?? new DateTimeImmutable();
        $this->createdAt        = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt        = $updatedAt ?? new DateTimeImmutable();

        foreach ($roles as $role) {
            if (!$role instanceof UserRole) {
                //TODO: Да мне просто лень делать новый exception если добавится InvalidArgument исправить
                throw new ValidationException('Invalid role');
            }
            if (!$role->getUserId()->equals($this->id)) {
                throw new ValidationException('Role does not belong to this user.');
            }
            $this->roles[] = $role;
        }
    }

    // ---------------------------- Геттеры ----------------------------
    public function getId(): UserId
    {
        return $this->id;
    }
    public function getPassportId(): ?int
    {
        return $this->passportId;
    }
    public function getPost(): ?string
    {
        return $this->post;
    }
    public function getEmail(): Email
    {
        return $this->email;
    }
    public function getSurname(): ?string
    {
        return $this->surname;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }
    public function isOwner(): bool
    {
        return $this->isOwner;
    }
    public function getSyncAt(): ?DateTimeImmutable
    {
        return $this->syncedAt;
    }
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
    /** @return UserRole[] */
    public function getRoles(): array
    {
        return $this->roles;
    }

    // ---------------------------- Мутаторы ----------------------------

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
        $this->touch();
    }

    public function changeSurname(?string $surname): void
    {
        $this->surname = $surname;
        $this->touch();
    }

    public function changeName(?string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function changePatronymic(?string $patronymic): void
    {
        $this->patronymic = $patronymic;
        $this->touch();
    }

    public function changePost(?string $post): void
    {
        $this->post = $post;
        $this->touch();
    }

    public function setIsOwner(): void
    {
        $this->isOwner = true;
        $this->touch();
    }

    public function recordSync(DateTimeImmutable $time): void
    {
        $this->syncedAt = $time;
        $this->touch();
    }

    // ---------------------------- Child entities ----------------------------

    /**
     * @throws ValidationException
     */
    public function assignRole(UserRole $role): void
    {
        if (!$role->getUserId()->equals($this->id)) {
            throw new ValidationException('Role assignment does not belong to this user.');
        }
        foreach ($this->roles as $existing) {
            if ($existing->getRoleId()->equals($role->getRoleId())) {
                throw new ValidationException('Role already assigned.');
            }
        }
        $this->roles[] = $role;
        $this->touch();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function removeRole(UserRoleId $userRoleId): void
    {
        foreach ($this->roles as $key => $role) {
            if ($role->getId()->equals($userRoleId)) {
                unset($this->roles[$key]);
                $this->roles = array_values($this->roles);
                $this->touch();
                return;
            }
        }
        throw new EntityNotFoundException('User role assignment not found.');
    }

    /**
     * @internal Используется только репозиторием для установки ID после сохранения
     */
    public function setId(UserId $id): void
    {
        $this->id = $id;
    }
}
