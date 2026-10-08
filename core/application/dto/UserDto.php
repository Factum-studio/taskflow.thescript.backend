<?php

declare(strict_types=1);

namespace core\application\dto;

use core\domain\entity\User;
use JsonSerializable;

class UserDto implements JsonSerializable
{
    private int $id;
    private int $passportId;
    private string $email;
    private ?string $surname;
    private ?string $name;
    private ?string $patronymic;
    private ?string $post;
    private bool $isOwner;
    private ?string $syncedAt;
    private string $createdAt;
    private string $updatedAt;

    /** @var UserRoleDto[] */
    private array $roles;

    public function __construct(
        int     $id,
        int     $passportId,
        string  $email,
        ?string $surname,
        ?string $name,
        ?string $patronymic,
        ?string $post,
        bool    $isOwner,
        ?string $syncedAt,
        string  $createdAt,
        string  $updatedAt,
        array   $roles = [],
    ) {
        $this->id           = $id;
        $this->passportId   = $passportId;
        $this->email        = $email;
        $this->surname      = $surname;
        $this->name         = $name;
        $this->patronymic   = $patronymic;
        $this->post         = $post;
        $this->isOwner      = $isOwner;
        $this->syncedAt     = $syncedAt;
        $this->createdAt    = $createdAt;
        $this->updatedAt    = $updatedAt;
        $this->roles        = $roles;
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            $user->getId()->value(),
            $user->getPassportId(),
            $user->getEmail()?->value(),
            $user->getSurname(),
            $user->getName(),
            $user->getPatronymic(),
            $user->getPost(),
            $user->isOwner(),
            $user->getSyncAt()?->format('Y-m-d'),
            $user->getCreatedAt()->format('Y-m-d H:i:s'),
            $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            array_map([UserRoleDto::class, 'fromEntity'], $user->getRoles()),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'passport_id'   => $this->passportId,
            'email'         => $this->email,
            'surname'       => $this->surname,
            'name'          => $this->name,
            'patronymic'    => $this->patronymic,
            'post'          => $this->post,
            'is_owner'      => $this->isOwner,
            'synced_at'     => $this->syncedAt,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
            'roles'         => $this->roles,
        ];
    }
}
