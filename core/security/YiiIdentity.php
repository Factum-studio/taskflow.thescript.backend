<?php

declare(strict_types=1);

namespace core\security;

use yii\web\IdentityInterface;

/**
 * Identity of the authenticated OAuth subject.
 *
 * The ID is the external Passport subject (sub). The local service may map it
 * to its own users table by passport_id.
 */
final class YiiIdentity implements IdentityInterface
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        private readonly string $id,
        private readonly string $clientId = '',
        private readonly string $tokenId = '',
        private readonly array  $scopes = [],
        private readonly string $subjectType = 'user',
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public static function findIdentity($id): ?self
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function isUser(): bool
    {
        return $this->subjectType === 'user';
    }
}
