<?php

namespace core\security;

use core\domain\entity\User;
use core\domain\valueObject\JwtToken;
use yii\web\IdentityInterface;

final class YiiIdentity implements IdentityInterface
{
    public function __construct(
        private readonly User $user,
        private readonly ?JwtToken $jwtToken = null
    ) {}

    public function getId(): string
    {
        return $this->user->getId()->value();
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public static function findIdentity($id)
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getJwtToken(): ?JwtToken
    {
        return $this->jwtToken;
    }

    public function getFullName(): string
    {
        return $this->user->getFullName();
    }

    public function getUiName(): string
    {
        return $this->user->getUiName();
    }

    public function getContacts(): array
    {
        return $this->user->getContacts();
    }

    public function getPostName(): ?string
    {
        $post = $this->user->getPost();
        return $post?->getName();
    }
}