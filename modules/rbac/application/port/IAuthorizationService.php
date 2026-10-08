<?php

declare(strict_types=1);

namespace modules\rbac\application\port;

use core\security\YiiIdentity;

interface IAuthorizationService
{
    /**
     * @param array<string, mixed> $context
     */
    public function can(
        int $userId,
        string $permission,
        array $context = [],
    ): bool;

    /**
     * @param array<string, mixed> $context
     */
    public function authorize(
        int $userId,
        string $permission,
        array $context = [],
    ): void;

    /**
     * Authorizes either a user subject or an M2M client subject.
     *
     * @param array<string, mixed> $context
     */
    public function canForIdentity(
        YiiIdentity $identity,
        string $permission,
        array $context = [],
    ): bool;

    /**
     * @param array<string, mixed> $context
     */
    public function authorizeForIdentity(
        YiiIdentity $identity,
        string $permission,
        array $context = [],
    ): void;
}
