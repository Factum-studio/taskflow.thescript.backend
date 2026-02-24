<?php

namespace core\security;

use core\domain\valueObject\Identify;
use core\domain\valueObject\JwtToken;
use yii\web\IdentityInterface;

final class YiiIdentity implements IdentityInterface
{
    private Identify $identity;
    private ?JwtToken $jwtToken = null;

    public function __construct(
        Identify  $identity,
        ?JwtToken $jwtToken = null
    ) {
        $this->identity = $identity;
        $this->jwtToken = $jwtToken;
    }

    public function getId(): string
    {
        return $this->identity->value();
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

    public function getDomainIdentity(): Identify
    {
        return $this->identity;
    }

    public function getJwtToken(): ?JwtToken
    {
        return $this->jwtToken;
    }

    public function setJwtToken(JwtToken $jwtToken): void
    {
        $this->jwtToken = $jwtToken;
    }
}